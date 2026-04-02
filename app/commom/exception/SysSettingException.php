<?php
namespace app\commom\exception;

class SysSettingException extends BaseException{
    public $code=404;
    public $msg='系统设置错误';
    public $errorCode=60002;
}
