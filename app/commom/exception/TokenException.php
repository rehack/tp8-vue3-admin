<?php
namespace app\commom\exception;

// Token异常
class TokenException extends BaseException{
    public $code=401;//http状态码
    public $msg='token已过期或无效token';//错误具体信息
    public $errorCode=20002;//自定义错误码
}
