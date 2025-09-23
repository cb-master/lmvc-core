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
namespace CBM\Core;

// Deny Direct Access
defined('APP_PATH') || http_response_code(403).die('403 Direct Access Denied!');

use InvalidArgumentException;

// Meta Hndler
class Meta
{
   // Get Version Info from PHP File
   /**
    * @param ?string $path - Required A Path of PHP File
    * @return array - Returns an associative array of meta information extracted from the PHP file's doc comments.
    * @throws InvalidArgumentException - Throws an exception if the provided path is not a valid directory.
    */
   public static function version(string $path): array
   {
      if(!is_dir($path)) throw new InvalidArgumentException("Invalid Path: $path");
      $meta = [];
      $tokens = token_get_all(file_get_contents($path));
      $found = false;
      // Get Doc Comments if Exist
      foreach($tokens as $token){
         if(isset($token[0]) && isset($token[1]) && ($token[0] == T_DOC_COMMENT)){
            $comments = explode('*', $token[1]);
            foreach($comments as $value){
               // Set Values
               if(str_contains($value, ':')){
                  $array = explode(':', $value);
                  $array[0] = strtolower(str_replace(' ', '-', trim($array[0])));
                  $array[1] = trim(isset($array[2]) ? $array[1] . ":" . $array[2] : $array[1]);
                  $meta[$array[0]] = $array[1];
               }
            }
            $found = true;            
         }
         if($found){
            break;
         }
      }
      return $meta;
   }
}