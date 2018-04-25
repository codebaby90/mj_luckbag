<?php

namespace app\api\controller\v1;

use app\api\library\WXLoginHelper;
use app\api\library\Api;
use fast\Random;
use think\Db;
use think\Config;
use app\api\controller\v1\Sms;
use think\Cache;

/**
 * 示例接口
 */
class Luckbag extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = [''];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = [''];

    public $pid;
    public $luckbag_info;
    public $luckbag_baglist;
    public $luckbag_bagprizelist;
    const NO_REGISTER = '40001';
    const HAS_COMPOSE = '40002';

    public function _initialize()
    {
        parent::_initialize();
        $this->pid = input("pid", '1', 'intval');
        $this->luckbag_info = $this->getLuckbagInfo();
        $this->luckbag_baglist = Config::get('luckbag.baglist');
        $this->luckbag_bagprizelist = Config::get('luckbag.bagprizelist');
    }
    /**
     * [registerUser description]
     * @Author    mjrw
     * @DateTime  2018-03-26
     * @copyright [copyright]
     * @remark    [用户注册接口]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function registerUser()
    {
        //$usename = input("username", '', 'htmlspecialchars_decode');
        $idcard = input("idcard", '');
        $mobile = input("mobile", '');
        $code = input("code", '', 'intval');
        $share_key = input("share_key", '');
        // 检测是否录入过用户信息
        $userinfo = $this->findLuckbagUser();
        if(!$userinfo){
            // 判断验证码是否正确
            $codename = Sms::setMobileCodeName($mobile);
            $cachecode = Cache::get($codename);
            if($code != $cachecode['code']){
                $this->error('验证码错误');
            }
            // 判断手机号是否正常
            // 判断身份证是否正常
            // 调用接口判断身份证号
            $table_luckbag_user = Db::name('luckbag_user');
            $ip = request()->ip();
            // 插入luckbag_user表
            $time = time();
            $data = [
                'user_name' => $this->global_userinfo['nick_name'],
                'nick_name' => $this->global_userinfo['nick_name'],
                'openid' => $this->openid,
                'tel' => $mobile,
                'idcard' => $idcard,
            ];
            $params = array_merge($data, [
                'token'  => $this->token,
                'pid'  => $this->pid,
                'share_key'      => Random::alpha(32),
                'from_sharekey' => $share_key,
                'createtime'  => $time,
                'updatetime' => $time,
            ]);
            // 查询分享人具体信息
            if(!empty($share_key)){
                $from_user = $table_luckbag_user->where(['share_key'=>$share_key])->find();
                if($from_user){
                    $params['source_openid'] = $from_user['openid'];
                }  
            }
            //账号注册时需要开启事务,避免出现垃圾数据
            Db::startTrans();
            try
            {
                $user = $table_luckbag_user->insert($params);
                if($share_key != ''){
                    $table_luckbag_sharelist = Db::name('luckbag_sharelist');
                    $insert_sharedata = [
                        'share_key' => $share_key,
                        'openid' => $this->openid,
                        'token'  => $this->token,
                        'pid'  => $this->pid,
                        'createtime'  => $time,
                        'updatetime' => $time,
                    ];
                    $table_luckbag_sharelist->insert($insert_sharedata);
                }
                //$user = User::create($params);
                Db::commit();
                return $this->success('验证信息成功');
            }
            catch (Exception $e)
            {
                $this->setError($e->getMessage());
                Db::rollback();
                return $this->error('增加信息失败');
            }
        }else{
            return $this->error('你已经认证过了');
        }
        //return json($data);
    }
    /**
     * [getBagList description]
     * @Author    mjrw
     * @DateTime  2018-03-26
     * @copyright [copyright]
     * @remark    [获取用户福袋记录]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function getBagList()
    {
        // 处理默认返回给用户的数据
        $default_param = [
            'is_receive' => 0,
            'userbaglist' => [],
            'bagcount' => 0,
            'luckbaginfo' => $this->luckbag_info,
            'baglist' => $this->luckbag_baglist,
            'userbaglist' => []

        ];
        // 查询是否存在用户数据，即用户是否注册
        $userinfo = $this->findLuckbagUser();
        if(!$userinfo){
            return $this->error('用户还未注册',$default_param, NO_REGISTER);
        }
        // 判断用户是否合成成功
        $prizeinfo = $this->findUserCompose();
        if($prizeinfo){
            return $this->error('已经合成过了',$default_param, HAS_COMPOSE);
        }
        // 查找当前用户办理的业务，比对用户业务
        $userbaglist = $this->handleUserBusiness();
        // 匹配业务信息，输出
        return $this->success('success',$default_param);
    }
    /**
     * [composeBag description]
     * @Author    mjrw
     * @DateTime  2018-03-26
     * @copyright [copyright]
     * @remark    [合成福袋]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function composeBag()
    {
        // 判断用户是否已经领取过
        $prizeinfo = $this->findUserCompose();
        if($prizeinfo){
            return $this->error('已经合成过了','',HAS_COMPOSE);
        }
        // 查询用户当前所拥有的所有业务
        // 查询是否符合合成要求
        // 根据手机号随机合成的金额
        // 
    }
    /**
     * [receiveMoney description]
     * @Author    mjrw
     * @DateTime  2018-03-26
     * @copyright [copyright]
     * @remark    [根据手机号领取合成金额]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function receiveMoney()
    {
        // 判断用户是否已经领取过
        // 判断手机号类型
        // 调取接口赠送金额
        // 成功以后修改状态
    }
    /**
     * [handleUserBusiness description]
     * @Author    mjrw
     * @DateTime  2018-03-29
     * @copyright [处理用户当前已有的业务]
     * @remark    [remark]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function handleUserBusiness(){
        // 查询当前所有业务
        $buslist = $this->getBankBusiness();
        // 查询所有配置的福袋
        $allbag = $this->luckbag_baglist;
        // 查询当前用户的所有福袋key数组
        $userbag = $this->selectUserBag(1);
        // 将用户的福袋与系统福袋做比较，禁用或启用用户福袋
        $this->disableUserbus($buslist, $allbag, $userbag);
        // 先根据个人类型匹配用户的个人记录，禁用或者启用
        // 根据分享关系禁用或者启用数据
        // 根据分组搜索当前用户一共启用了多少业务
        
    }
    protected function disableUserbus($buslist, $allbag, $userbag){
        
        // 以用户列表为依据，查询业务列表中差集，禁用掉
        $nodisableuserbag = [];
        // 获取未禁用个人福袋列表
        foreach ($userbag as $key => $value) {
            if($value['receive_type'] == 0){
                array_push($nodisableuserbag,$key);
            }
        }
        dump($buslist);
        // 处理未添加系统的福袋列表
        $nohavebaglist = [];
        // 以业务列表为依据，查询用户列表中差集，新增记录
        $nohavebaglist = $this->diffBag($buslist, $userbag);
        foreach ($nodisableuserbag as $key => $value) {
            # code...匹配目前没有插入记录表的未禁用数据
            if(!empty($allbag[$key])){
                dump($key);
                dump($buslist);
                if(!in_array($key,$buslist)){
                    array_push($nohavebaglist,$key);
                }
            }
        }
        $userinfo = $this->findLuckbagUser();
        $table_luckbag_receivelist = Db::name('luckbag_receivelist');
        $time = time();
        // 判断未添加到系统的数据
        if(!empty($nohavebaglist)){
            $insert_receivedata = [
                'token' => $this->token,
                'openid' => $this->openid,
                'pid' => $this->pid,
                'source_openid' => $userinfo['from_sharekey'],
            ];
            foreach ($nohavebaglist as $key => $value) {
                $merge_data = [
                    'bag_key' => $value,
                    'bag_name' => $allbag[$value]['bag_name'],
                    'receive_time' => $time,
                    'createtime' => $time,
                    'updatetime' => $time,
                ];
                $insert_receivedata = array_merge($insert_receivedata,$merge_data);
                $table_luckbag_receivelist
                    ->insert($insert_receivedata);
            }
        }
        //获取禁用赠送福袋列表
        foreach ($nodisableuserbag as $key => $value) {
            # code...
            if(!empty($allbag[$key])){
                if(!in_array($key,$buslist)){
                    array_push($nosharebaglist,$key);
                }
            }
        }
        // 将注销的福袋禁用掉
        if(!empty($nosharebaglist)){
            $where_receivedata = [
                'token' => $this->token,
                'openid' => $this->openid,
                'pid' => $this->pid,
                'receive_type' => 0,
            ];
            foreach ($nosharebaglist as $key => $value) {
                $where_receivedata['bag_key'] = $value;
                $save_data = [
                    'status' => 0,
                    'updatetime' => $time,
                ];
                $table_luckbag_receivelist
                    ->where($where_receivedata)
                    ->update($save_data);
            }
        }
        // 处理分享关系，去除上家的分享
        if(!empty($userinfo['source_openid'])){
            // 查询上家所得的所有赠送的福袋
            $where_share = [
                'openid' => $userinfo['source_openid'],
                'receive_type' => 1,
            ];
            $shareuser_baglist = $table_luckbag_receivelist
                ->where($where)
                ->select();
            foreach ($shareuser_baglist as $key => $value) {
                array_push($shareuser_baglistkey,$value['bag_key']);
            }
            foreach ($buslist as $key => $value) {
                # code...匹配目前没有插入记录表的未禁用数据
                if(!empty($allbag[$key])){
                    if(!in_array($key,$shareuser_baglistkey)){
                        //插入
                    }
                }
            }

        }
    }
    /**
     * [diffBag description]
     * @Author    mjrw
     * @DateTime  2018-04-22
     * @copyright [copyright]
     * @remark    [取出用户和业务的福袋的差集]
     * @version   [version]
     * @param     [type]      $first  [description]
     * @param     [type]      $second [description]
     * @return    [type]              [description]
     */
    protected function diffBag($first, $second){
        $allbag = $this->luckbag_baglist;
        // 取出全部交集
        $first = array_intersect($allbag, $first);
        return array_diff($first,$second);
    }
    /**
     * [intersectBag description]
     * @Author    mjrw
     * @DateTime  2018-04-22
     * @copyright [copyright]
     * @remark    [取出用户和业务的福袋的交集]
     * @version   [version]
     * @return    [type]               [description]
     */
    protected function intersectBag($first, $second){
        $allbag = $this->luckbag_baglist;
        // 取出全部交集
        $first = array_intersect($allbag, $first);
        return array_intersect($first,$second);
    }
    /**
     * [selectUserBag description]
     * @Author    mjrw
     * @DateTime  2018-03-29
     * @copyright [copyright]
     * @remark    [获取用户当前所有福袋]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function selectUserBag($type = 0){
        $table_luckbag_receivelist = Db::name('luckbag_receivelist');
        $where = ['openid'=>$this->openid,'token'=>$this->token,'pid'=>$this->pid];
        $where['status'] = 1;
        $info = $table_luckbag_receivelist
            ->where($where)
            ->select();
        switch ($type) {
            case '0':
                return $info;
                break;
            case '1':
                $userbaglist = [];
                foreach ($info as $key => $value) {
                    $userbaglist[$value['bag_key']] = $value;
                }
                //$userbaglist = array_unique($userbaglist);
                return $userbaglist;
                break;
            case '1':
                $userbaglist = [];
                foreach ($info as $key => $value) {
                    array_push($userbaglist,$value['bag_key']);
                }
                return $userbaglist;
                break;
            default:
                return $info;
                break;
        } 
    }
    /**
     * [selectAllBag description]
     * @Author    mjrw
     * @DateTime  2018-03-29
     * @copyright [copyright]
     * @remark    [查询系统配置中所有福袋]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function selectAllBag(){
        $table_luckbag_setbag = Db::name('luckbag_setbag');
        $where = ['token'=>$this->token,'pid'=>$this->pid];
        $info = $table_luckbag_setbag
            ->where($where)
            ->select();
        return $info;
    }
    /**
     * [findUserCompose description]
     * @Author    mjrw
     * @DateTime  2018-03-29
     * @copyright [copyright]
     * @remark    [查找用户是否合成过]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function findUserCompose(){
        $table_luckbag_prizelist = Db::name('luckbag_prizelist');
        $where = ['openid'=>$this->openid,'token'=>$this->token,'pid'=>$this->pid];
        $info = $table_luckbag_prizelist
            ->where($where)
            ->find();
        return $info;
    }
    /**
     * [findLuckbagUser description]
     * @Author    mjrw
     * @DateTime  2018-03-29
     * @copyright [copyright]
     * @remark    [查找用户注册信息]
     * @version   [version]
     * @return    [type]      [description]
     */
    public function findLuckbagUser(){
        $where = ['openid'=>$this->openid,'token'=>$this->token,'pid'=>$this->pid];
        $table_luckbag_user = Db::name('luckbag_user');
        $userinfo = $table_luckbag_user
            ->where($where)
            ->find();
        return $userinfo;
    }
    /**
     * [getLuckbagInfo description]
     * @Author    mjrw
     * @DateTime  2018-03-29
     * @copyright [copyright]
     * @remark    [获取活动信息]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function getLuckbagInfo(){
        $table_luckbag_info = Db::name('luckbag_info');
        $where = ['token'=>$this->token,'id'=>$this->pid];
        $info = $table_luckbag_info
            ->where($where)
            ->find();
        $this->checkAction($info);
        return $info;
    }
    /**
     * [checkAction description]
     * @Author    mjrw
     * @DateTime  2018-03-29
     * @copyright [copyright]
     * @remark    [验证活动是否进行中]
     * @version   [version]
     * @param     [type]      $info [description]
     * @return    [type]            [description]
     */
    protected function checkAction($info){

    }
    /**
     * [sendSms description]
     * @Author    mjrw
     * @DateTime  2018-03-26
     * @copyright [copyright]
     * @remark    [短信发送接口]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function sendSms()
    {

    }
    /**
     * [validateIdcard description]
     * @Author    mjrw
     * @DateTime  2018-03-26
     * @copyright [copyright]
     * @remark    [验证身份证号接口]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function validateIdcard()
    {

    }
    /**
     * [getBankBusiness description]
     * @Author    mjrw
     * @DateTime  2018-03-26
     * @copyright [copyright]
     * @remark    [获取用户办理银行业务]
     * @version   [version]
     * @return    [type]      [description]
     */
    protected function getBankBusiness()
    {
        //return $this->luckbag_baglist;
        $arr = [
            'bag1' => [
                'bag_name' => '存本取息',
                'bag_image' => '/images/sjpa.png',// 福袋图片
                'bag_npimage' => '/images/person/cbqx_pno.png', // 未获得人物图片
                'bag_pimage' => '/images/person/cbqx_pget.png',// 获得人物图片
                'bag_ndesc' => '手机银行开户路径：关联账户转账——卡内活期转定期;微信银行开户路径：借记卡——转账汇款——卡内活期转定期',// 未获得文案
                'bag_desc' => '手机银行开户路径：关联账户转账——卡内活期转定期;微信银行开户路径：借记卡——转账汇款——卡内活期转定期',// 获得文案
                'is_get' => 0
            ],
            'bag3' => [
                'bag_name' => '零存宝',
                'bag_image' => '/images/sjpa.png',// 福袋图片
                'bag_npimage' => '/images/person/lcb_pno.png', // 未获得人物图片
                'bag_pimage' => '/images/person/lcb_pget.png',// 获得人物图片
                'bag_ndesc' => '手机银行开户路径：关联账户转账——卡内活期转定期;微信银行开户路径：借记卡——转账汇款——卡内活期转定期',// 未获得文案
                'bag_desc' => '手机银行开户路径：关联账户转账——卡内活期转定期;微信银行开户路径：借记卡——转账汇款——卡内活期转定期',// 获得文案
                'is_get' => 0
            ]
        ];
        return $arr;
        $userinfo = $this->findLuckbagUser();

    }
}
