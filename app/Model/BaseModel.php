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
        $builder->where($where);
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
        $builder->where($where);
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
        $affectedRows = $builder->where($where)->update($data);
        return $affectedRows;
    }

    /**
     * 获取查询器
     * @return \Illuminate\Database\Query\Builder
     */
    private function getBuilder(){
        return DB::table($this->table);
    }
}