<?php
namespace app\commom\exception;

// 更新或新增成功
class SuccessMessage extends BaseException{
    public $code=201;//http状态码
    public $msg='OK';//错误具体信息
    public $errorCode=0;//自定义错误码
}
