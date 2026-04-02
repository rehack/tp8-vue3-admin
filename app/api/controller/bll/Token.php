<?php
namespace app\api\controller\bll;

use app\api\validate\UserLogin;
use app\api\service\PasswordToken;
use think\facade\Request;
// use think\Controller;
// 登录后发放token（包括账号密码登录和微信登录方式）
class Token{
    public function getToken(){
        (new UserLogin())->goCheck();
        $post_data = input('post.');
        $login_other_data = [];
        $login_other_data['user_agent'] = Request::header('user-agent');
        $login_other_data['login_ip'] = Request::ip();
        $login_other_data['url'] = Request::url(true);
        $login_other_data['login_env'] = $post_data['login_env'];

        $pt = new PasswordToken($post_data, $login_other_data);
        $token = $pt->login();
        return json([
            'token'=>$token
        ]);
    }
}