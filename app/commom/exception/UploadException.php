<?php
namespace app\commom\exception;

class UploadException extends BaseException{
    public $code=400;
    public $msg='上传失败!';
    public $errorCode=20009;
}
