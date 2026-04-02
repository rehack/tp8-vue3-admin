<?php
namespace app\commom\exception;

class AuthException extends BaseException{
    public $code=401;
    public $msg='您暂无该操作权限，请与管理员联系!!';
    public $errorCode=20001;
}
