<?php
namespace app\commom\exception;

class LawCaseException extends BaseException{
    public $code=404;
    public $msg='案件管理错误';
    public $errorCode=201000;
}
