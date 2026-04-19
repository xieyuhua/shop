<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\entity\AdminProductEntity;

/**
 * 后台商品管理控制器 - 仅接收参数和返回结果
 */
class ProductController extends BaseController
{
    private AdminProductEntity $productEntity;

    public function __construct()
    {
        parent::__construct();
        $this->productEntity = new AdminProductEntity();
    }

    /**
     * 商品列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'keyword' => $this->request->get('keyword', ''),
            'category_id' => $this->request->get('category_id', 0),
            'shop_id' => $this->request->get('shop_id', 0),
            'status' => $this->request->get('status', ''),
        ];

        $result = $this->productEntity->getList($params);

        return $this->success($result);
    }

    /**
     * 商品详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $product = $this->productEntity->getDetail($id);

        if (!$product) {
            return $this->error('商品不存在');
        }

        return $this->success($product);
    }

    /**
     * 创建商品
     */
    public function create(): \think\Response
    {
        $this->adminAuth();

        $adminId = $this->adminId;
        $data = $this->request->post();

        $result = $this->productEntity->create($adminId, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 更新商品
     */
    public function update(): \think\Response
    {
        $this->adminAuth();

        $data = $this->request->post();

        $result = $this->productEntity->update($data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 删除商品
     */
    public function delete(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);

        $result = $this->productEntity->delete($id);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 审核商品
     */
    public function audit(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);
        $status = (int) $this->request->post('status', 1);

        $result = $this->productEntity->audit($id, $status);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 批量更新
     */
    public function batchUpdate(): \think\Response
    {
        $this->adminAuth();

        $data = $this->request->post();
        $ids = $data['ids'] ?? [];

        $result = $this->productEntity->batchUpdate($ids, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }
}
