<?php

namespace app\api\library;

use fast\Http;
use think\Cache;

/**
 * 自定义API模块的错误显示
 */
class Juhe
{
    //默认配置
    protected $config = [
        'tel_appkey' => "2939e1914146291237c626255cb231d1", 
        'idcard_appkey' => 'd68393d82bc27d39c8761c6a803c5fd1',
    ];

    /**
     * 构造函数
     */
    public function __construct() {
        //可设置配置项 juhe, 此配置项为数组。
        if ($juhe = Config::get('juhe')) {
            $this->config = array_merge($this->config, $juhe);
        }
    }

    public static function sendSms($callback)
    {
        
    }

}
