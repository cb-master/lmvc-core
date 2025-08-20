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
namespace CBM\Core\Console\Service;

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use CBM\Model\ConnectionManager;
use CBM\Core\Console\Message;
use CBM\Model\Schema;
use CBM\Model\Model;
use CBM\Core\Config;

class Database Extends Model
{
    /**
     * @var array $args
     */
    private array $args;

    /**
     * @var array $message
     */
    private array $message = [];

    /**
     * @param array $args
     */
    public function __construct(array $args)
    {
        $this->args = $args;
        
        try {
            // Get Database Name
            $name = $this->args[0] ?? 'default';
            // Add Database Connection
            $config = Config::get('database', $name);
            ConnectionManager::add($config ?: []);

            parent::__construct($name);
        } catch (\Throwable $th) {
            $this->message = [
                'status'    => false,
                'message'   => Message::show('Error', $th->getMessage(), 'red'),
            ];
        }
    }

    public function up(): array
    {
        if(!empty($this->message)) return $this->message;

        /**
         * Set the PDO connection & Create the database
         */
        try{
            Schema::setConnection($this->args['name'] ?? 'default');
            Schema::create('options', function($e){

                $e->id('opt_id', 'BIGINT', )
                    ->string('opt_key', 255)
                    ->text('opt_value')
                    ->enum('opt_default', ['yes', 'no'], 'yes')
                    ->primary('opt_id')
                    ->index('opt_key', 255);

            });
            return [
                'status'    => true,
                'message'   => Message::show('Success', 'Database Created Successfully'),
            ];
        } catch (\Throwable $th) {
            return [
                'status'    => false,
                'message'   => Message::show('Error', $th->getMessage(), 'red'),
            ];
        }
        

        return [
            'status'    => true,
            'message'   => Message::show('Error', 'Something Went Wrong', 'red'),
        ];
    }

    public function down(): array
    {
        if(!empty($this->message)) return $this->message;

        /**
         * Drop the database
         */
        try{
            Schema::setConnection($this->args['name'] ?? 'default');
            Schema::drop('options');
            
            return [
                'status'    => true,
                'message'   => Message::show('Success', 'Database Dropped Successfully'),
            ];
        } catch (\Throwable $th) {
            return [
                'status'    => false,
                'message'   => Message::show('Error', $th->getMessage(), 'red'),
            ];
        }
    }
}