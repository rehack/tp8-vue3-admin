<?php
/**
 * 生成树结构数据
 * 这个方法需要注意的是 要把你的主键作为数组下标
 */
 function generate_tree($items)
 {
    $tree = [];
    foreach ($items as $item)
        if (isset($items[$item['pid']])) {
            $items[$item['pid']]['children'][] = &$items[$item['id']];
        } else {
            $tree[] = &$items[$item['id']];
        }

    return $tree;
}
// 生成UUID v4
function uuid4($data){
    if (function_exists('uuid_create') === true){
        return uuid_create(4);
    }else{
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}