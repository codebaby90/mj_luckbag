<?php

namespace app\api\controller;

use fast\Random;
use think\Config;
use app\api\library\WXLoginHelper;

/**
 * 公共接口
 */
class Commoninterface
{

    /**
     * [getAccessToken description]
     * @Author    mjrw
     * @DateTime  2018-03-28
     * @copyright [copyright]
     * @remark    [获取accesstoken]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function getAccessToken(){
        $code = input("code", '', 'htmlspecialchars_decode');
        $rawData = input("rawData", '', 'htmlspecialchars_decode');
        $signature = input("signature", '', 'htmlspecialchars_decode');
        $encryptedData = input("encryptedData", '', 'htmlspecialchars_decode');
        $iv = input("iv", '', 'htmlspecialchars_decode');
        $wxHelper = new WXLoginHelper();
        $data = $wxHelper->checkLogin($code, $rawData, $signature, $encryptedData, $iv);
        return json($data);
    }

}
