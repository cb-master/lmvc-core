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

// Namespace
namespace CBM\Core\Console\Commands;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403) . die('403 Direct Access Denied!');

use CBM\Core\{Console\Command, Config};
use CBM\Model\ConnectionManager;
use CBM\Model\{Schema,DB};
use Exception;

class Migrate Extends Command
{
    // Default Options Keys
    private function defaulKeys(): array
    {
        return [
            'app.name'      =>  'CBM Framework',
            'time.zone'     =>  'Europe/London',
            'time.format'   =>  'Y-M-d H:i:s',
            'dbsession'     =>  'yes',
            'developermode' =>  'yes',
            'app.path'      =>  realpath(APP_PATH ?? __DIR__.'/../../../../../../'),
            'admin.icon'    =>  'favicon.ico',
            'admin.logo'    =>  'logo.png',
            'csrf.lifetime' =>  'logo.png',
        ];
    }

    /**
     * Run the command to create a new controller.
     *
     * @param array $params
     */
    public function run(array $params): void
    {
        $connection_name = (isset($params[0]) && $params[0]) ? trim($params[0]) : 'default';
        try {
            // Make PDO Connection
            ConnectionManager::add(Config::get('database', $connection_name), $connection_name);

            // Connectin=on Set for Schema
            Schema::setConnection($connection_name);
            
            // Make Table
            Schema::create('options', function($e){
                $e->id('id')
                  ->string('opt_key', 255)
                  ->text('opt_value', true)
                  ->enum('opt_default', ['yes','no'], 'no')
                  ->index('opt_key', 255);
            });

            // Insert Default Data
            $db = DB::getInstance();
            
            // Get Old Data if Exist
            $old_data = $db->table('options')->select('opt_key')->get();
            if(!empty($old_data)){
                throw new Exception("Database Table 'options' Already Exists. Please Remove Old Table First");
            }

            $rows = [];
            foreach($this->defaulKeys() as $key => $val){
                $rows[] = ['opt_key' => $key, 'opt_value'=>$val, 'opt_default'=>'yes'];
            }
            // Insert Options
            $db->table('options')->insertMany($rows);

            // Create Secret Config File if Not Exist
            if(!Config::has('secret')) Config::create('secret', ['key'=>bin2hex(random_bytes(64))]);
            // Create Secret Key Value Not Exist or Empty
            if(!Config::has('secret', 'key')) Config::set('secret', 'key', bin2hex(random_bytes(64)));
            // Success Message
            $this->info("App Migrated Successfully");
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
            return;
        }
    }
}