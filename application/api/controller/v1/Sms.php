<?php

namespace app\api\controller\v1;
use think\Cache;
use app\api\library\Api;
use fast\Random;
use app\api\controller\v1\Luckbag;

/**
 * 手机短信接口
 */
class Sms extends Api
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 发送验证码
     *
     * @param string    $mobile     手机号
     * @param string    $event      事件名称
     */
    public function send()
    {
        $mobile = $this->request->request("mobile");
        $last = $this->getMobileCode($mobile);
        if ($last && time() - $last['createtime'] < 60)
        {
            $this->error(__('发送频繁'));
        }
        if ($mobile)
        {
            $luckbag = new Luckbag();
            $userinfo = $luckbag->findLuckbagUser();
            if ($userinfo)
            {
                //已被注册
                $this->error(__('已被注册'));
            }
            $ret = $this->setMobileCode($mobile);
            if ($ret)
            {
                $this->success(__('发送成功'),$ret);
            }
            else
            {
                $this->error(__('发送失败'));
            }
        }else{
            $this->error(__('数据有误'));
        }
    }
    /**
     * [setMobileCodeName description]
     * @Author    mjrw
     * @DateTime  2018-04-21
     * @copyright [copyright]
     * @remark    [配置缓存名称]
     * @version   [version]
     */
    public static function setMobileCodeName($mobile, $alias = ''){
        $alias = empty($alias)?'jz':$alias;
        $name = [$alias,$mobile];
        return implode('_',$name);
    }
    /**
     * [getMobileCode description]
     * @Author    mjrw
     * @DateTime  2018-04-21
     * @copyright [copyright]
     * @remark    [获取验证码信息]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function getMobileCode($mobile, $alias = ''){
        $codename = self::setMobileCodeName($mobile, $alias);
        return Cache::get($codename);
    }
    /**
     * [setMobileCode description]
     * @Author    mjrw
     * @DateTime  2018-04-21
     * @copyright [copyright]
     * @remark    [设置手机号验证码]
     * @version   [version]
     * @param     [type]      $mobile [description]
     */
    protected function setMobileCode($mobile, $alias = ''){
        $codename = self::setMobileCodeName($mobile, $alias);
        $rand = Random::numeric(6);
        $data = [
            'code' => $rand,
            'createtime' => time()
        ];
        // 发送验证码
        $send = $this->sendSms($mobile, $rand);
        if($send){
            $expires_in = 3600;
            Cache::set($codename, $data ,$expires_in);
            return $rand;
        }else{
            return false;
        }
    }
    /**
     * [sendSms description]
     * @Author    mjrw
     * @DateTime  2018-04-21
     * @copyright [copyright]
     * @remark    [发送手机号验证码]
     * @version   [version]
     * @param     [type]      $mobile [description]
     * @param     [type]      $rand   [description]
     * @return    [type]              [description]
     */
    protected function sendSms($mobile, $rand){
        return true;
    }

}
