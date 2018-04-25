<?php

namespace app\admin\model;

use think\Model;

class LuckbagReceivelist extends Model
{
    // 表名
    protected $name = 'luckbag_receivelist';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'receive_time_text',
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['0' => __('Status 0'),'1' => __('Status 1'),'2' => __('Status 2')];
    }     


    public function getReceiveTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['receive_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setReceiveTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
