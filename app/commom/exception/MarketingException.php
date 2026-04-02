<?php
namespace app\commom\exception;

class MarketingException extends BaseException{
    public $code=404;
    public $msg='营销中心数据错误';
    public $errorCode=60002;
}
