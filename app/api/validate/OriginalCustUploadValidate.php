<?php
namespace app\api\validate;
// 原始客户导入验证
class OriginalCustUploadValidate extends BaseValidate{
    protected $rule=[
        'excel'=>'file|fileExt:xls,xlsx,csv|fileSize:12582912'
        
    ];

    protected $message=[
        'excel.file'=>'非法文件',
        'excel.fileExt'=>'文件格式不正确',
        'excel.fileSize'=>'文件大小不能超过12M'
    ];
    

}
