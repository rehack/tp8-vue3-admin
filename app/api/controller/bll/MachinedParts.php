<?php

namespace app\api\controller\bll;

use think\facade\Db;
use app\api\service\BaseToken;

class MachinedParts extends BaseController
{
    /**
     * 获取加工件列表
     */
    public function getlist()
    {
        $page = max(1, (int) input('get.page/d', 1));
        $limit = max(1, min(200, (int) input('get.limit/d', 100)));
        $offset = ($page - 1) * $limit;

        $name = input('get.name/s', '');
        $manufacturerId = input('get.manufacturer_id/s', '');
        $status = input('get.status/s', '');

        $where = [
            ['m.del_flag', '=', 0]
        ];

        if ($name !== '') {
            $where[] = ['m.name', 'like', '%' . $name . '%'];
        }
        if ($manufacturerId !== '') {
            $where[] = ['m.manufacturer_id', '=', $manufacturerId];
        }
        if ($status !== '') {
            $where[] = ['m.status', '=', $status];
        }

        // 总数
        $cacheKey = 'machined_parts_count_' . md5(json_encode($where));
        $total = cache($cacheKey) ?: null;
        if ($total === null) {
            $total = Db::name('machined_parts')
                ->alias('m')
                ->where($where)
                ->count('m.id');
            cache($cacheKey, $total, 60);
        }

        // 列表
        $list = Db::name('machined_parts')
            ->alias('m')
            ->leftJoin('machined_manufacturer mf', 'm.manufacturer_id = mf.id')
            ->where($where)
            ->field('m.id, m.name, m.manufacturer_id, m.unit, m.single_price, m.month, m.remark, m.status, m.create_date, m.update_date, mf.name as manufacturer_name')
            ->order('m.create_date', 'desc')
            ->limit($offset, $limit)
            ->select();

        return json([
            'total' => $total,
            'data' => $list
        ]);
    }

    /**
     * 获取加工件详情
     */
    public function detail()
    {
        $id = input('get.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少加工件id', 'data' => null]);
        }

        $row = Db::name('machined_parts')
            ->where('id', $id)
            ->where('del_flag', 0)
            ->find();

        if (!$row) {
            return json(['code' => 404, 'msg' => '加工件未找到', 'data' => null]);
        }

        return json(['code' => 200, 'msg' => 'ok', 'data' => $row]);
    }

    /**
     * 保存加工件(新增/更新)
     */
    public function save()
    {
        $data = input('post.');

        $id = isset($data['id']) ? trim($data['id']) : '';
        $now = date('Y-m-d H:i:s');

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        $saveData = [
            'name' => isset($data['name']) ? trim($data['name']) : null,
            'manufacturer_id' => isset($data['manufacturer_id']) ? trim($data['manufacturer_id']) : null,
            'unit' => isset($data['unit']) ? trim($data['unit']) : null,
            'single_price' => isset($data['single_price']) ? $data['single_price'] : null,
            'month' => isset($data['month']) ? (int) $data['month'] : null,
            'remark' => isset($data['remark']) ? $data['remark'] : null,
            'status' => isset($data['status']) ? (int) $data['status'] : 1,
            'update_date' => $now,
            'update_by' => $userId
        ];

        if ($id) {
            // 更新
            $exists = Db::name('machined_parts')->where('id', $id)->where('del_flag', 0)->find();
            if (!$exists) {
                return json(['code' => 404, 'msg' => '更新失败，记录不存在', 'data' => null]);
            }
            Db::name('machined_parts')->where('id', $id)->update($saveData);
            $newRow = Db::name('machined_parts')->where('id', $id)->find();
            return json(['code' => 200, 'msg' => '更新成功', 'data' => $newRow]);
        }

        // 新增
        $saveData['id'] = uuid4(random_bytes(16));
        $saveData['create_date'] = $now;
        $saveData['create_by'] = $userId;
        $saveData['del_flag'] = 0;
        Db::name('machined_parts')->insert($saveData);

        return json(['code' => 200, 'msg' => '保存成功', 'data' => $saveData]);
    }

    /**
     * 删除加工件(逻辑删除)
     */
    public function delete()
    {
        $id = input('post.id/s', '');
        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少加工件id', 'data' => null]);
        }

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        $exists = Db::name('machined_parts')->where('id', $id)->where('del_flag', 0)->find();
        if (!$exists) {
            return json(['code' => 404, 'msg' => '删除失败，记录不存在', 'data' => null]);
        }

        Db::name('machined_parts')->where('id', $id)->update([
            'del_flag' => 1,
            'update_date' => date('Y-m-d H:i:s'),
            'update_by' => $userId
        ]);

        return json(['code' => 200, 'msg' => '删除成功', 'data' => null]);
    }

    /**
     * 启用/禁用加工件
     */
    public function setStatus()
    {
        $data = input('post.');
        $id = isset($data['id']) ? trim($data['id']) : '';
        $status = isset($data['status']) ? (int) $data['status'] : null;

        if (!$id) {
            return json(['code' => 400, 'msg' => '缺少加工件id', 'data' => null]);
        }

        if ($status === null || !in_array($status, [0, 1])) {
            return json(['code' => 400, 'msg' => '状态值无效', 'data' => null]);
        }

        // 获取当前用户
        try {
            $userId = BaseToken::getCurrentUid();
        } catch (\Exception $e) {
            $userId = 'system';
        }

        $exists = Db::name('machined_parts')->where('id', $id)->where('del_flag', 0)->find();
        if (!$exists) {
            return json(['code' => 404, 'msg' => '记录不存在', 'data' => null]);
        }

        Db::name('machined_parts')->where('id', $id)->update([
            'status' => $status,
            'update_date' => date('Y-m-d H:i:s'),
            'update_by' => $userId
        ]);

        return json(['code' => 200, 'msg' => '状态更新成功', 'data' => null]);
    }

    /**
     * 获取启用的加工件列表(下拉选择用)
     */
    public function getEnabledList()
    {
        $list = Db::name('machined_parts')
            ->alias('m')
            ->leftJoin('machined_manufacturer mf', 'm.manufacturer_id = mf.id')
            ->where('m.del_flag', 0)
            ->where('m.status', 1)
            ->field('m.id, m.name, m.manufacturer_id, m.unit, m.single_price, mf.name as manufacturer_name')
            ->order('m.name', 'asc')
            ->select();

        return json([
            'code' => 200,
            'msg' => 'ok',
            'data' => $list
        ]);
    }
    
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
