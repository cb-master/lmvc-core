<?php
/**
 * Project: Laika MVC Framework
 * Author Name: Showket Ahmed
 * Author Email: riyadhtayf@gmail.com
 */

// Namespace
namespace CBM\Core\App;

use CBM\Model\Model as BaseModel;
use CBM\Core\Config;

// Forbidden Access
defined('BASE_PATH') || http_response_code(403).die('403 Forbidden Access!');

class Model Extends BaseModel
{
    // Status Table Name
    protected string $status_table;

    // List
    private array $list = [];

    // Get Limit
    public function limit(int|string $page = 1, array $where = []): array
    {
        if($where){
            return $this->all($where);
        }
        return $this->db
                    ->table($this->table)
                    ->limit((int) Config::get('app', 'limit'))
                    ->offset($page)
                    ->get();
    }


    // Get Statuses
    /**
     * @param string $column Optional Parameter. Default is null.
     */
    public function statuses(?string $column = null):array
    {
        $statuses = [];
        $column = $column ?: 'status';
        $data = $this->db->table($this->status_table)->select($column)->get();
        foreach($data as $val){
            $statuses[strtolower($val[$column])] = ucwords($val[$column]);
        }
        return $statuses;
    }

    // Get Selected Column
    /**
     * @param string $column Required Argument. Example 'id,title'
     * @param array $where Optiona Argument. Example ['id'=>1]. 
     */
    public function getColumns(string $columns, array $where = []): array
    {
        return $where ? $this->db->table($this->table)->select($columns)->where($where)->get():
                        $this->db->table($this->table)->select($columns)->get();
    }

    // Get List
    /**
     * @param string $column1 Optional Parameter
     * @param string $column2 Required Parameter
     * @param array $where Optiona Argument. Example ['id'=>1].
     */
    public function list(string $column1, string $column2, array $where = []): array
    {
        $data = call_user_func([$this, 'getColumns'], "{$column1}, {$column2}", $where);
        foreach($data as $val){
            $this->list[$val[$column1]] = $val[$column2];
        }
        return $this->list;
    }
}