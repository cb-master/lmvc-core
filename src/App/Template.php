<?php
/**
 * Laika PHP MVC Framework
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com
 * License: MIT
 * This file is part of the Laika PHP MVC Framework.
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace CBM\Core\App;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Core\{Directory, File, Config, ClientInfo};
use RuntimeException;

class Template
{
    // Template Directory
    protected string $templateDir;
    // Cache Directory
    protected string $cacheDir;

    /** Variables available to templates */
    protected array $vars = [];

    /** Registered filters: name => callable($value, ...$args) */
    protected array $filters = [];

    /** Child block buffers & modes during render */
    protected array $childBlocks = [];
    protected array $childModes  = [];
    protected array $blockStack  = [];

    /** Unique placeholder used for parent content inside a child block */
    private const PARENT_PLACEHOLDER = "\x00__PARENT_BLOCK__\x00";
    
    /* ------------------------- Public API ------------------------- */
    public function __construct()
    {
        // Template/Cache Directory
        $this->templateDir = Config::get('app', 'template.dir', APP_PATH . '/lf-templates/');
        $this->cacheDir = Config::get('app', 'template.cache', APP_PATH . '/lf-cache/');
        
        // Add Default Config Data
        $this->vars['app_info'] = Config::get('app');
        // Add Client Info
        $this->vars['client_info'] = new ClientInfo();

        // Default filters (value, ...args)
        $this->filters = [
            'date'     => fn($v)        => date((string)$v),
            'upper'    => fn($v)        => strtoupper((string)$v),
            'lower'    => fn($v)        => strtolower((string)$v),
            'ucfirst'  => fn($v)        => ucfirst((string)$v),
            'trim'     => fn($v)        => trim((string)$v),
            'raw'      => fn($v)        => $v,                 // disables escaping
            // With args examples
            'truncate' => function ($v, int $len = 50, string $suffix = '…') {
                $s = (string)$v;
                return (mb_strlen($s) > $len) ? (mb_substr($s, 0, $len) . $suffix) : $s;
            },
            'replace'  => function ($v, string $search, string $replace) {
                return str_replace($search, $replace, (string)$v);
            },
            'named'    => fn(string $name, array $params = [])  => named($name, $params, true),
            'apply_filter'  =>  fn(string $filter, mixed $value = null, mixed ...$args) => apply_filter($filter, $value, ...$args),
        ];
    }

    ####################################################################
    /* ------------------------ INTERNAL API ------------------------ */
    ####################################################################
    // Set Template Sub Directory
    /**
     * @param string $directory Sub Directory inside lf-templates Directory
     * @return void
     */
    protected function addTemplateDir(string $directory): void
    {
        if(realpath($directory)){
            $this->templateDir = realpath($directory);
        }else{
            $this->templateDir .= trim(strtolower($directory), '/');
        }
        // Make Directory If Not Exists
        if(!Directory::make($this->templateDir)) throw new RuntimeException("Failed to Create Template Directory: {$this->templateDir}");
        return;
    }

    // Set Cache Sub Directory
    /**
     * @param string $directory Sub Directory inside tf-cache Directory Directory
     * @return void
     */
    protected function addCacheDir(string $directory): void
    {
        if(realpath($directory)){
            $this->cacheDir = realpath($directory);
        }else{
            $this->cacheDir .= trim(strtolower($directory), '/');
        }
        // Make Directory If Not Exists
        if(!Directory::make($this->cacheDir)) throw new RuntimeException("Failed to Create Template Directory: {$this->cacheDir}");
        return;
    }

    protected function assign(string|array $key, mixed $value = null): void
    {
        if(is_array($key)){
            $this->vars = array_merge($key, $this->vars);
        }else{
            $this->vars[$key] = $value;
        }
    }

    // Get Assigned Vars
    /**
     * Get All Assigned Vars in Controller
     * @return array
     */
    protected function getAssignedVars(): array
    {
        return array_keys($this->vars);
    }

    /** Register a custom filter. Callback signature: fn($value, ...$args): mixed */
    protected function addFilter(string $name, string|callable $callback): void
    {
        $this->filters[$name] = $callback;
    }

    /** Render a template file */
    protected function view(string $view): void
    {
        // Require All Template Hooks
        $hooks_path = $this->templateDir . '/hooks';
        // Create Hooks Path if Does Not Exists
        Directory::make($hooks_path);
        // Load Hooks
        $files = Directory::scanRecursive($hooks_path, true, ['php']);
        foreach($files as $file){
            require_once $file;
        }

        // Create Template Directory htaccess if Not Available
        $ht = new File($this->templateDir.'/.htaccess');
        if(!$ht->exists()) $ht->write("Deny from all");

        // Create Cache htaccess if Not Available
        $ch = new File($this->cacheDir.'/.htaccess');
        if(!$ch->exists()) $ch->write("Deny from all");

        // Template & Cache File Path
        $sourceFile = "{$this->templateDir}/{$view}.tpl.php";

        // Throw RuntimeException if View File Does not Exists
        if(!is_file($sourceFile)) throw new RuntimeException("Template File Not Found: '{$view}'");

        $cacheFile = $this->cacheDir . '/' . md5($sourceFile) . '-' . filemtime($sourceFile) . '.cache.php';

        // Make Template File Object
        $tpl = new File($sourceFile);
        // Make Cache File Object
        $cptl = new File($cacheFile);

        // Check File is Exists
        if(!$tpl->exists()) throw new RuntimeException("Template file not found: {$sourceFile}");

        // Read source to determine extends + dependencies
        $source = $tpl->read(); ////////////////////////////

        [$parent, $deps] = $this->collectDependencies($source);

        // Recompile Cache Template if Source File Modified
        if($this->needsRecompile($sourceFile, $cacheFile, $deps)){
            $compiled = $this->compile($source, isChild: (bool) $parent);
            $cptl->write($compiled);
        }

        // Reset child block state each render
        $this->childBlocks = [];
        $this->childModes  = [];
        $this->blockStack  = [];

        // Make filters available inside compiled templates
        $filters = $this->filters;

        // 1) Render the (possibly child) template
        ob_start();
        extract($this->vars, EXTR_SKIP);
        include $cacheFile; // this will call $this->startBlock()/endBlock() for child blocks
        $childTopLevelOutput = ob_get_clean();

        /**
         * If no parent, print child's own output (blocks are ignored unless parent uses them).
         * Or Render the parent, resolving blocks with default content
         */
        echo (!$parent) ? $childTopLevelOutput : $this->renderParent($parent);
    }

    /* -------------------- Block Helpers (child) ------------------- */

    /** Begin capturing a child block */
    protected function startBlock(string $name, string $mode = 'replace'): void
    {
        $this->blockStack[] = [$name, $mode];
        ob_start();
        return;
    }

    /** End capturing a child block */
    protected function endBlock(): void
    {
        if (empty($this->blockStack)) {
            throw new \LogicException('endBlock() called without matching startBlock().');
        }
        [$name, $mode] = array_pop($this->blockStack);
        $this->childBlocks[$name] = ob_get_clean();
        $this->childModes[$name]  = $mode;
        return;
    }

    /** Emits a unique marker that will be replaced by parent default content */
    protected function parentPlaceholder(): string
    {
        return self::PARENT_PLACEHOLDER;
    }

    /* -------------------- Block Helpers (parent) ------------------ */

    /**
     * Resolve a block by merging parent default and child override using mode.
     * Used from compiled parent templates.
     */
    protected function resolveBlock(string $name, string $default): string
    {
        $child = $this->childBlocks[$name] ?? '';
        $mode  = $this->childModes[$name]  ?? 'replace';

        // If child explicitly references parent placeholder, honor it regardless of mode
        if ($child !== '' && str_contains($child, self::PARENT_PLACEHOLDER)) {
            return str_replace(self::PARENT_PLACEHOLDER, $default, $child);
        }

        return match ($mode) {
            'append'  => $default . $child,
            'prepend' => $child . $default,
            default   => ($child !== '' ? $child : $default),
        };
    }

    protected function renderParent(string $parentTemplate): string
    {
        $parentFile = "{$this->templateDir}/{$parentTemplate}.tpl.php";
        if (!is_file($parentFile)) {
            throw new \RuntimeException("Parent template not found: {$parentFile}");
        }

        $parentSource = file_get_contents($parentFile);
        [$grandParent, $deps] = $this->collectDependencies($parentSource);

        $parentCache = $this->cacheDir . '/' . md5($parentFile) . '.cache.php';
        if ($this->needsRecompile($parentFile, $parentCache, $deps)) {
            $compiled = $this->compile($parentSource, isChild: false); // parent layout => not a child
            file_put_contents($parentCache, $compiled);
        }

        // Render the parent with access to filters and resolveBlock()
        $filters = $this->filters;
        ob_start();
        extract($this->vars, EXTR_SKIP);
        include $parentCache;
        $out = ob_get_clean();

        // (Optional) One level of nesting supported. If grandparent exists, you could
        // propagate $this->childBlocks upward by reusing the same arrays and calling
        // renderParent($grandParent) again. For now we stop at one level for simplicity.
        return $out;
    }

    /**
     * Compile Template
     * @param string $template Template String Format
     * @return string
     */
    protected function compile(string $content, bool $isChild): string
    {
        // Child blocks: capture content for later merging in parent
        if ($isChild) {
            $content = preg_replace('/\{\%\s*extends\s+[\'\"](.+?)[\'\"]\s*\%\}/', '', $content);
            $content = preg_replace_callback('/\{\%\s*block\s+(\w+)(?:\s+(append|prepend))?\s*\%\}/',
                function ($m) {
                    $name = $m[1];
                    $mode = $m[2] ?? 'replace';
                    return "<?php \$this->startBlock('{$name}', '{$mode}'); ?>";
                },
                $content
            );
            $content = preg_replace('/\{\%\s*endblock\s*\%\}/', '<?php $this->endBlock(); ?>', $content);

            // `{% parent %}` inside child blocks to reference parent's default content
            $content = preg_replace('/\{\%\s*parent\s*\%\}/', '<?= $this->parentPlaceholder(); ?>', $content);
        }
        else {
            // Parent blocks: capture default and resolve via resolveBlock()
            $content = preg_replace_callback(
                '/\{\%\s*block\s+(\w+)\s*\%\}(.*?)\{\%\s*endblock\s*\%\}/s',
                function ($m) {
                    $name = $m[1];
                    $inner = $m[2];
                    // Build code: capture default then echo resolved content
                    return "<?php ob_start(); ?>{$inner}<?php \$__default = ob_get_clean(); echo \$this->resolveBlock('{$name}', \$__default); ?>";
                },
                $content
            );
        }

        $content = preg_replace_callback('/\{\%\s*include\s+[\'"](.+?)[\'"]\s*\%\}/', function ($matches) {
            $file = $matches[1];
            // Add .tpl.php automatically if missing
            if (!str_ends_with($file, '.tpl.php')) {
                $file .= '.tpl.php';
            }
            $includeFile = $this->templateDir . '/' . ltrim($file, '/');
            return is_file($includeFile) ? file_get_contents($includeFile) : '';
        }, $content);

        // Global apply_filter function
        $content = preg_replace_callback(
            '/\{\{\s*apply_filter\((.+?)\)\s*\}\}/',
            fn($m) => "<?= apply_filter({$m[1]}) ?>",
            $content
        );

        // Variables with filters and optional arguments: {{ var | filter(arg1, 'str') | upper }}
        $content = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function ($matches) {
            $expr = trim($matches[1]);
            // Split by pipe not inside parentheses
            $parts = preg_split('/\|(?![^\(]*\))/', $expr);
            $base = trim(array_shift($parts));

            // Detect string literals, numbers, or expressions
            if (preg_match('/^([\'"]).*\1$/', $base) || is_numeric($base) || is_bool($base)) {
                $php = $base; // Leave strings ("..."), numbers and bollean as-is
            } else {
                $php = '$' . $base; // Treat as variable
            }
            $raw   = false;

            foreach ($parts as $seg) {
                $seg = trim($seg);
                if ($seg === 'raw') { $raw = true; continue; }
                if (preg_match('/^(\w+)\s*\((.*)\)$/', $seg, $m)) {
                    $fname   = $m[1];
                    $args    = trim($m[2]);
                    $argsPhp = $args === '' ? '' : ', ' . $args; // pass-through PHP args
                    $php     = '$this->applyFilter(\'' . $fname . '\', ' . $php . $argsPhp . ')';
                } else {
                    $php = '$this->applyFilter(\'' . $seg . '\', ' . $php . ')';
                }
            }

            if (!$raw) {
                $php = "htmlspecialchars({$php}, ENT_QUOTES, 'UTF-8')";
            }
            return "<?= {$php} ?>";
        }, $content);

        // If/elseif/else/endif
        $content = preg_replace('/\{\%\s*if\s+(.+?)\s*\%\}/', '<?php if ($1): ?>', $content);
        $content = preg_replace('/\{\%\s*elseif\s+(.+?)\s*\%\}/', '<?php elseif ($1): ?>', $content);
        $content = preg_replace('/\{\%\s*else\s*\%\}/', '<?php else: ?>', $content);
        $content = preg_replace('/\{\%\s*endif\s*\%\}/', '<?php endif; ?>', $content);

        // Foreach/endforeach
        $content = preg_replace('/\{\%\s*foreach\s+(.+?)\s*\%\}/', '<?php foreach ($1): ?>', $content);
        $content = preg_replace('/\{\%\s*endforeach\s*\%\}/', '<?php endforeach; ?>', $content);

        return $content;
    }

    /** Apply a filter by name to a value with optional args. Used by compiled code */
    public function applyFilter(string $name, mixed $value, ...$args): mixed
    {
        if (!isset($this->filters[$name])) {
            throw new \RuntimeException("Filter '{$name}' is not registered.");
        }
        return ($this->filters[$name])($value, ...$args);
    }

    /* -------------------- Internal: Dependencies ------------------ */

    /**
     * Parse source to find: parent (extends) and includes list.
     * @return array{0: string|null, 1: array}
     */
    protected function collectDependencies(string $source): array
    {
        $parent = null;
        if (preg_match('/\{\%\s*extends\s+[\'\"](.+?)[\'\"]\s*\%\}/', $source, $m)) {
            $parent = $m[1];
        }
        $includes = [];
        if (preg_match_all('/\{\%\s*include\s*[\'"](.+?)[\'"]\s*\%\}/', $source, $mm)) {
            $includes = array_map(function ($file) {
                if (!str_ends_with($file, '.tpl.php')) {
                    $file .= '.tpl.php';
                }
                return '/' . ltrim($file, '/');
            }, $mm[1]);
        }
        return [$parent, $includes];
    }

    /** Determine if recompilation is needed based on mtimes of source and dependencies */
    protected function needsRecompile(string $sourceFile, string $cacheFile, array $deps): bool
    {
        if (!is_file($cacheFile)) return true;
        $cacheMTime = filemtime($cacheFile) ?: 0;
        if ((filemtime($sourceFile) ?: 0) > $cacheMTime) return true;
        foreach ($deps as $rel) {
            $inc = $this->templateDir . $rel;
            if (is_file($inc) && (filemtime($inc) ?: 0) > $cacheMTime) return true;
        }
        return false;
    }
}