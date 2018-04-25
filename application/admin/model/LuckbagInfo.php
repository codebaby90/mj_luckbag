<?php

namespace app\admin\model;

use think\Model;

class LuckbagInfo extends Model
{
    // 表名
    protected $name = 'luckbag_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'start_time_text',
        'end_time_text',
        'min_bagcount_text',
        'status_text'
    ];
    

    
    public function getMinBagcountList()
    {
        return ['2' => __('Min_bagcount 2')];
    }     

    public function getStatusList()
    {
        return ['0' => __('Status 0'),'1' => __('Status 1'),'2' => __('Status 2')];
    }     


    public function getStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['start_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['end_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getMinBagcountTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['min_bagcount'];
        $list = $this->getMinBagcountList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStartTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setEndTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
