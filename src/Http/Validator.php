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
namespace CBM\Core\Http;

// Deny Direct Access
defined('BASE_PATH') || http_response_code(403).die('403 Direct Access Denied!');

class Validator
{
    // Make Validator Result
    /**
     * @param array $data Required Argument. Example $_REQUEST or Any Associative Array. Example: ['email'=>'test@example.com','age'=>32]
     * @param array $rules Required Argument. Example: ['email'=>'required','age'=>'required|min:18|max:65']
     * @param array $customMessages Optional Argument. Example: ['email.required'=>'Email is Required!']
     * @return array
     */
    public static function make(array $data, array $rules, array $customMessages = []): array
    {
        $errors = [];

        foreach($rules as $field => $ruleString){
            $value = $data[$field] ?? null;
            $ruleList = explode('|', $ruleString);

            foreach($ruleList as $rule){
                $params = [];

                if (str_contains($rule, ':')) {
                    [$rule, $paramString] = explode(':', $rule, 2);

                    // Don't explode regex pattern!
                    $params = strtolower($rule) === 'regex'
                        ? [$paramString]
                        : explode(',', $paramString);
                }

                $messageKey = "{$field}.{$rule}";
                $customMessage = $customMessages[$messageKey] ?? null;

                switch(strtolower($rule)){
                    case 'required':
                        if($value === null || $value === ''){
                            $errors[$field][] = $customMessage ?? "The {$field} field is required.";
                        }
                        break;

                    case 'email':
                        if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
                            $errors[$field][] = $customMessage ?? "The {$field} must be a valid email address.";
                        }
                        break;

                    case 'numeric':
                        if($value !== null && !is_numeric($value)){
                            $errors[$field][] = $customMessage ?? "The {$field} must be numeric.";
                        }
                        break;

                    case 'min':
                        $min = (int)($params[0] ?? 0);
                        if(is_numeric($value) && $value < $min){
                            $errors[$field][] = $customMessage ?? "The {$field} must be at least {$min}.";
                        }elseif(is_string($value) && mb_strlen($value) < $min){
                            $errors[$field][] = $customMessage ?? "The {$field} must be at least {$min} characters.";
                        }
                        break;

                    case 'max':
                        $max = (int)($params[0] ?? 0);
                        if(is_numeric($value) && $value > $max){
                            $errors[$field][] = $customMessage ?? "The {$field} may not be greater than {$max}.";
                        }elseif(is_string($value) && mb_strlen($value) > $max){
                            $errors[$field][] = $customMessage ?? "The {$field} may not be greater than {$max} characters.";
                        }
                        break;

                    case 'match':
                        $other = $params[0] ?? '';
                        if(!isset($data[$other]) || $value !== $data[$other]){
                            $errors[$field][] = $customMessage ?? "The {$field} must match {$other}.";
                        }
                        break;

                    case 'in':
                        if(!in_array($value, array_map('strtolower', $params))){
                            $errors[$field][] = $customMessage ?? "The {$field} must be one of: " . implode(', ', $params);
                        }
                        break;

                    case 'regex':
                        $pattern = $params[0] ?? '';
                        if($pattern && !preg_match($pattern, $value)){
                            $errors[$field][] = $customMessage ?? "The {$field} format is invalid.";
                        }
                        break;

                    default:
                        $errors[$field][] = "Unknown validation rule '{$rule}' for field '{$field}'.";
                        break;
                }
            }
        }

        // return new ValidatorResult($errors);
        return $errors;
    }
}