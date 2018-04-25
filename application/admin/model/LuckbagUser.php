<?php

namespace app\admin\model;

use think\Model;

class LuckbagUser extends Model
{
    // 表名
    protected $name = 'luckbag_user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'is_receive_text',
        'receive_time_text'
    ];
    

    
    public function getIsReceiveList()
    {
        return ['0' => __('Is_receive 0'),'1' => __('Is_receive 1')];
    }     


    public function getIsReceiveTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['is_receive'];
        $list = $this->getIsReceiveList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getReceiveTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['receive_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setReceiveTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
