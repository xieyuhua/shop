<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\model\Shop as ShopModel;

/**
 * 后台店铺管理控制器 - 仅接收参数和返回结果
 */
class Shop extends BaseController
{
    /**
     * 店铺列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $page = (int) $this->request->get('page', 1);
        $limit = (int) $this->request->get('limit', 15);
        $keyword = $this->request->get('keyword', '');
        $status = $this->request->get('status', '');

        $query = ShopModel::with(['user', 'category'])->order('id', 'desc');

        if (!empty($keyword)) {
            $query->where('shop_name', 'like', '%' . $keyword . '%');
        }

        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * 店铺详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $shop = ShopModel::with(['user', 'category'])->find($id);

        if (!$shop) {
            return $this->error('店铺不存在');
        }

        return $this->success($shop);
    }

    /**
     * 审核店铺
     */
    public function audit(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $status = (int) $this->request->post('status', 1);
        $reason = $this->request->post('reason', '');

        $shop = ShopModel::find($id);

        if (!$shop) {
            return $this->error('店铺不存在');
        }

        if ($shop->status != ShopModel::STATUS_PENDING) {
            return $this->error('店铺状态不允许审核');
        }

        $shop->status = $status;
        if ($status == ShopModel::STATUS_REJECTED) {
            $shop->reject_reason = $reason;
        }
        $shop->audit_time = time();
        $shop->save();

        return $this->success();
    }

    /**
     * 设置店铺状态
     */
    public function setStatus(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $status = (int) $this->request->post('status', 1);

        $shop = ShopModel::find($id);

        if (!$shop) {
            return $this->error('店铺不存在');
        }

        $shop->status = $status;
        $shop->save();

        return $this->success();
    }
}
