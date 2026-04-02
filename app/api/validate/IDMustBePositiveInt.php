<?php
namespace app\api\validate;

class IDMustBePositiveInt extends BaseValidate
{
    protected $rule = [
        // 'id' => 'require|isPositiveInteger',
        'id' => 'require|number' // numbre不含负数和小数点
    ];
}
