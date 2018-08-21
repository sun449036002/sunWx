<?php
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/23
 * Time: 10:29
 */
class BaseModel extends Model
{
    /**
     * 获取一条数据
     * @param $columns
     * @param $where
     * @param array $order
     * @param array $group
     * @return object
     */
    public function getOne($columns, $where, $order = [], $group = []) {
        $builder = $this->getBuilder();
        $builder->select($columns);
        $this->bindWhere($where, $builder);
        return $builder->first();
    }

    /**
     * 获取列表
     * @param $columns
     * @param $where
     * @param array $order
     * @param array $group
     * @return array
     */
    public function getList($columns, $where, $order = [], $group = []) {
        $builder = $this->getBuilder();
        $builder->select($columns);
        if (!empty($where)) {
            $this->bindWhere($where, $builder);
        }
        if (!empty($order)) {
            $this->orderBy($order[0], $order[1]);
        }
        $rows = $builder->get();
        if (!empty($rows)) {
            return $rows->toArray();
        }
        return [];
    }

    /**
     * @param $data
     * @return bool|int
     */
    public function insert($data) {
        if (empty($data)) {
            return false;
        }
        $builder = $this->getBuilder();
        $newId = $builder->insertGetId($data);
        return $newId;
    }

    /**
     * @param array $data
     * @param array $where
     * @return bool|int
     */
    public function updateData($data, $where) {
        if (empty($data) || empty($where)) {
            return false;
        }

        $builder = $this->getBuilder();
        $this->bindWhere($where, $builder);
        $affectedRows = $builder->update($data);
        return $affectedRows;
    }

    /**
     * 获取查询器
     * @return \Illuminate\Database\Query\Builder
     */
    private function getBuilder(){
        return DB::table($this->table);
    }

    /**
     * 处理 WHERE 条件
     * @param $where
     * @param $builder
     */
    private function bindWhere($where, &$builder) {
        foreach ($where as $key => $item) {
            if (is_array($item)) {
                if (count($item) == 3) {
                    list($field, $option, $val) = $item;
                    if (strtoupper($item[1]) == 'IN') {
                        if (!is_array($val)) {
                            $val[] = $val;
                        }
                        $builder->whereIn($field, $val);
                    } else if (strtoupper($item[1]) == 'NOT IN'){
                        if (!is_array($val)) {
                            $val[] = $val;
                        }
                        $builder->whereNotIn($field, $val);
                    } else {
                        $builder->where($field, $option, $val);
                    }
                }
            } else {
                $builder->where($key, $item);
            }
        }
    }
}