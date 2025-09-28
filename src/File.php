<?php
/**
 * Project: Laika MVC Framework
 * Author Name: Showket Ahmed
 * Author Email: riyadhtayf@gmail.com
 */

// Namespace
namespace CBM\Core;

class File
{
    // Path
    /**
     * @var string $path
     */
    protected string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    ###############################################################
    ## ----------------------- FILE INFO ----------------------- ##
    ###############################################################
    // Check Path Exist
    public function exists(): bool
    {
        return file_exists($this->file);
    }

    // Check Path is Readable
    public function readable(): bool
    {
        return is_readable($this->file);
    }

    // Check Path is Writable
    public function writable(): bool
    {
        return is_writable($this->file);
    }

    // Get File Size
    /**
     * @return int|false Output will be in byte
     */
    public function size(): int|false
    {
        return $this->exists() ? filesize($this->file) : false;
    }

    // Get File Info
    /**
     * @return string Get File Info
     */
    public function info(): array
    {
        return pathinfo($this->file);
    }

    // Get Mime Type
    /**
     * @return string|false Mime Type of File on Success and false on Fail
     */
    public function mimeType(): string|false
    {
        return mime_content_type($this->file);
    }

    // Get Mime Extension
    /**
     * @return string Get Extension Name
     */
    public function extension(): string
    {
        return pathinfo($this->file, PATHINFO_EXTENSION);
    }

    // Get File Name
    /**
     * @return string Get File Name
     */
    public function name(): string
    {
        return pathinfo($this->file, PATHINFO_FILENAME);
    }

    // Get File Base Name
    /**
     * @return string Get File Name
     */
    public function base(): string
    {
        return pathinfo($this->file, PATHINFO_BASENAME);
    }

    // Get Path
    /**
     * @return string Path
     */
    public function path(): string
    {
        return pathinfo($this->file, PATHINFO_DIRNAME);
    }

    ######################################################################
    ## --------------------- BASIC FILE OPERATION --------------------- ##
    ######################################################################
    /**
     * Read File Content
     * @return string|false
     */
    public function read(): string|false
    {
        return file_get_contents($this->file);
    }

    /**
     * Write Content in File
     * @param string $str Required Argument
     * @return bool
     */
    public function write(string $str): bool
    {
        // Make Directory if Not Exists
        Directory::make($this->path());
        // Write Contents
        return file_put_contents($this->file, $str) !== false;
    }

    /**
     * Add New Content in File
     * @param string $str Content to append
     * @return bool
     */
    public function append(string $str): bool
    {
        return $this->writable() ? (file_put_contents($this->file, $str, FILE_APPEND) !== false) : false;
    }

    /**
     * Delete File
     * @return bool
     */
    public function pop(): bool
    {
        return unlink($this->file);
    }

    // Move Path
    /**
     * Move File
     * @param string $to New file name to move
     * @return bool
     */
    public function move(string $to): bool
    {
        $result = rename($this->file, $to);
        if ($result) $this->file = $to;
        return $result;
    }

    /**
     * Copy File
     * @param string $to Destination File Name
     * @return bool
     */
    public function copy(string $to): bool
    {
        return copy($this->file, $to);
    }

    /**
     * Sets Access & Modification Time of File
     * @param ?int $mtime Modefied Time. Default is null
     * @param ?int $atime Access Time. Default is null
     * @return bool
     */
    public function touch(?int $mtime = null, ?int $atime = null): bool
    {
        return touch($this->file, $mtime, $atime);
    }

    #########################################################################
    ## -------------------------- File Download -------------------------- ##
    #########################################################################
    /**
     * @param ?string $as Download As for Content Disposition
     * @return void
     */
    public function download(?string $as = null): void
    {
        $filename = $as ?? $this->name();
        $mime = $this->mimeType() ?: 'application/octet-stream';

        header("Content-Type: {$mime}");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Content-Length: {$this->size()}");
        readfile($this->file);
        return;
    }
}