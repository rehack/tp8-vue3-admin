<?php
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    // 'think\exception\Handle' => ExceptionHandle::class,
    'think\exception\Handle' => '\\app\commom\\exception\\ExceptionHandler', // 绑定自定义异常处理handle类
];
