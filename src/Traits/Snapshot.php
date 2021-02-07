<?php

namespace Cxx\Codetool\Traits;

use think\Db;
use think\Config;
use think\Loader;

/**
 * 基于tp5的模型扩展，可以记录模型修改前后的数据
 * 使模型具有记录快照的功能
 */
trait Snapshot
{
    /**
     * 变更前数据
     *
     * @var array
     */
    private $old_data = [];

    /**
     * 变更后数据
     *
     * @var array
     */
    private $new_data = [];

    /**
     * 改变的数据
     * 
     * @var array
     */
    private $change_data = [];

    /**
     * 字段注释
     * 
     * @var array
     */
    protected $field_annotation = [];

    /**
     * 注册事件
     *
     */
    protected static function init()
    {
        static::event('after_write', function ($model) {
            $model->old_data = $model->origin;
            $model->new_data = $model->data;
            $model->change_data = $model->getChangedData();
            $model->field_annotation = array_merge($model->autoGetAnnotation($model), $model->setFieldAnnotation() ?? []);
        });
    }

    /**
     * 自动获取字段注释(如果已经实现setFieldAnnotation方法可以在模型中复写此方法，返回空数组，不让其进行sql查询）
     */
    protected function autoGetAnnotation($model)
    {
        $db_config = Config::get('database');
        //从数据库中获取表字段信息
        $sql = "SELECT * FROM `information_schema`.`columns` "
            . "WHERE TABLE_SCHEMA = ? AND table_name = ? "
            . "ORDER BY ORDINAL_POSITION";
        //加载主表的列
        $columnList = Db::query($sql, [$db_config['database'], $model->getTable()]);
        $annotation = [];
        foreach ($columnList as $row) {
            $annotation[$row['COLUMN_NAME']] = $this->getLangItem($row['COLUMN_COMMENT']);
        }
        return $annotation;
    }

    /**
     * 解析字段注释
     *
     * @param string $content
     * @return string
     */
    private function getLangItem($content)
    {
        if (!$content) {
            return '';
        }
        $content = str_replace('，', ',', $content);
        if (stripos($content, ':') !== false && stripos($content, ',') && stripos($content, '=') !== false) {
            [$fieldLang] = explode(':', $content);
            return $fieldLang;
        }
        return $content;
    }

    /**
     * 设置字段注释
     *
     * @return array
     */
    public function setFieldAnnotation()
    {
        return [];
    }

    /**
     * 获取快照 (如果字段需要进行转换，则需要实现 get[field]TextAttr($value,$data) 方法,[field]为表字段采用小驼峰命名的方式)
     *
     * @return array
     */
    public function getSnapshot()
    {
        $snapshots = [];
        foreach ($this->change_data as $field => $value) {
            if (!isset($this->old_data[$field])) {
                continue;
            };
            $new_value = $value;
            $old_value = $this->old_data[$field] ?? null;
            // 检测属性获取器
            $method = 'get' . Loader::parseName($field . '_text', 1) . 'Attr';
            if (method_exists($this, $method)) {
                $new_value = $this->$method($new_value, $this->new_data);
                $old_value = $this->$method($old_value, $this->old_data);
            }
            $snapshots[] = [
                'field' => $field,
                'new_value' => $new_value,
                'old_value' => $old_value,
                'name' => $this->field_annotation[$field] ?? $field
            ];
        }
        return $snapshots;
    }

    public function getOldData()
    {
        return $this->old_data;
    }

    public function getNewData()
    {
        return $this->new_data;
    }

    public function getFieldAnnotation()
    {
        return $this->field_annotation;
    }
}
