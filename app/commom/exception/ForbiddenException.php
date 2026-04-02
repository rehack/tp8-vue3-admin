<?php
namespace app\commom\exception;
// 用户权限异常
class ForbiddenException extends BaseException{
    public $code=403;
    public $msg='权限不足';
    public $errorCode=20007;
}
