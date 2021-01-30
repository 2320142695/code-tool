<?php

use Cxx\Codetool\Traits\Snapshot;
use think\Model;

class Test extends Model
{
    // 引入快照功能
    use Snapshot;

    /**
     * 注册事件
     *
     */
    protected static function init()
    {
        // 如果有自定义模型的初始方法，需要先调用Snapshot::init();
        Snapshot::init();
    }

    /**
     * 设置字段注释 (覆盖trait)
     *
     * @return array
     */
    public function setFieldAnnotation()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'age' => '年龄',
            'status' => '状态'
        ];
    }

    /**
     * 全部字段已经通过 setFieldAnnotation 设置，就不需要此方法进行读取数据库配置了
     */
    protected function autoGetAnnotation($model)
    {
        return [];
    }

    /**
     * 获取器
     */
    public function getStatusTextAttr($value, $data)
    {
        $status_list = [1 => '正常', 2 => '锁定', 3 => '黑名单'];
        return $status_list[$value] ?? '无';
    }
}

$model = Test::get(['id' => 1]);
$model->name = 'cxx';
$model->age = 18;
$model->status = 2;
$model->save();
// 获取变动字段的新旧数据
$snapshot = $model->getSnapshot();
var_dump($snapshot);