<?php
namespace app\api\controller\pub;
use app\api\service\BaseToken;

// pub目录下的控制器都不需要验证权限，所以不需要添加Auth中间件
class BaseController{
    // protected $uid;
    // // 前置方法  tp6没有了前置方法
    // protected $beforeActionList = [
    //     'getUid'
    // ];
    // protected $middleware = [\think\middleware\AllowCrossDomain::class];
    public function __construct(){
        BaseToken::getCurrentVarByToken('uid');
    }
    protected function getUid(){
        // $this->uid = BaseToken::getCurrentVarByToken('uid');
        return BaseToken::getCurrentVarByToken('uid');
    }

}