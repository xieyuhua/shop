<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\entity\ShopEntity;

/**
 * 后台店铺管理控制器 - 仅接收参数和返回结果
 */
class Shop extends BaseController
{
    private ShopEntity $shopEntity;

    public function __construct()
    {
        parent::__construct();
        $this->shopEntity = new ShopEntity();
    }

    /**
     * 店铺列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'keyword' => $this->request->get('keyword', ''),
            'status' => $this->request->get('status', ''),
        ];

        $result = $this->shopEntity->getList($params);

        return $this->success($result);
    }

    /**
     * 店铺详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $shop = $this->shopEntity->getDetail($id);

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

        $result = $this->shopEntity->audit($id, $status, $reason);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

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

        $result = $this->shopEntity->setStatus($id, $status);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }
}
