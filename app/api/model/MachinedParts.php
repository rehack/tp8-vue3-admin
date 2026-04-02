<?php

namespace app\api\model;

use think\Model;

class MachinedParts extends Model
{
    // 设置表名
    protected $name = 'machined_parts';
    
    // 设置主键
    protected $pk = 'id';
    
    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_date';
    protected $updateTime = 'update_date';
    
    // 状态常量
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    // 类型常量
    const TYPE_IMPLANT = 0;      // 种植件
    const TYPE_MOVABLE = 1;      // 活动件
    const TYPE_OTHER = 2;        // 其它件
    
    // 状态获取器
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用'
        ];
        return $status[$data['status']] ?? '未知';
    }
    
    // 类型获取器
    public function getTypeTextAttr($value, $data)
    {
        $types = [
            self::TYPE_IMPLANT => '种植件',
            self::TYPE_MOVABLE => '活动件',
            self::TYPE_OTHER => '其它件'
        ];
        return $types[$data['machined_parts_type']] ?? '未知';
    }
    
    // 关联厂商
    public function manufacturer()
    {
        return $this->belongsTo('app\api\model\Manufacturer', 'manufacturer_id', 'id');
    }
}
