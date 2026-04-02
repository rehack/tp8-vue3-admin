<?php
namespace app\api\controller\pub;
use think\facade\Db;
class Medical extends BaseController{
    /**
     * 获取厂商列表(下拉选择用)
     */
    public function getManufacturerList()
    {
        $list = Db::name('machined_manufacturer')
            ->where('del_flag', 0)
            ->where('status', 1)
            ->field('id, name, contact, phone')
            ->order('name', 'asc')
            ->select();

        return json([
            'code' => 200,
            'msg' => 'ok',
            'data' => $list
        ]);
    }
}