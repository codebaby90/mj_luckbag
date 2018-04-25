<?php

namespace app\api\library;

use fast\Http;
use think\Cache;

/**
 * 自定义API模块的错误显示
 */
class Jzbank
{
    //默认配置
    const POSTURL = "http://61.148.29.58:80/pwxwebpbd/";
    const CHECKUSER = self::POSTURL."CheckUser.do";
    const PROLIST = self::POSTURL."PrdOpenedQry.do";

    /**
     * 构造函数
     */
    public function __construct() {
        
    }

    public static function checkUser($mobile, $idcard)
    {
        $param = [
            'IDNo' => $idcard,
            'MobilePhone' => $mobile,
        ];
        $info = Http::get(self::CHECKUSER, $param);
        return $info;
    }

    public static function proList($mobile, $idcard)
    {
        $param = [
            'IDNo' => $idcard,
            'MobilePhone' => $mobile,
        ];
        $info = Http::get(self::PROLIST, $param);
        return $info;
    }

}
