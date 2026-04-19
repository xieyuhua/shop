<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\CartEntity;

/**
 * 购物车控制器 - 仅接收参数和返回结果
 */
class Cart extends BaseController
{
    private CartEntity $entity;

    public function __construct()
    {
        parent::__construct();
        $this->entity = new CartEntity();
    }

    /**
     * 购物车列表
     */
    public function list(): \think\Response
    {
        $this->auth();

        $result = $this->entity->getList($this->userId);

        return $this->success($result['data']);
    }

    /**
     * 添加商品到购物车
     */
    public function add(): \think\Response
    {
        $this->auth();

        $productId = (int) ($this->request->post('product_id') ?? 0);
        $num = (int) ($this->request->post('num') ?? 1);
        $skuId = (int) ($this->request->post('sku_id') ?? 0);

        $result = $this->entity->add($this->userId, $productId, $num, $skuId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 更新商品数量
     */
    public function updateNum(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id') ?? 0);
        $num = (int) ($this->request->post('num') ?? 1);

        $result = $this->entity->updateNum($id, $this->userId, $num);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 删除购物车商品
     */
    public function delete(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id') ?? 0);

        $result = $this->entity->delete($id, $this->userId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }

    /**
     * 清空购物车
     */
    public function clear(): \think\Response
    {
        $this->auth();

        $result = $this->entity->clear($this->userId);

        return $this->success();
    }

    /**
     * 切换选中状态
     */
    public function toggleSelected(): \think\Response
    {
        $this->auth();

        $id = (int) ($this->request->post('id') ?? 0);

        $result = $this->entity->toggleSelected($id, $this->userId);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 全选/取消全选
     */
    public function selectAll(): \think\Response
    {
        $this->auth();

        $selected = (int) ($this->request->post('selected', 1));

        $result = $this->entity->selectAll($this->userId, $selected);

        return $this->success();
    }

    /**
     * 删除已选商品
     */
    public function deleteSelected(): \think\Response
    {
        $this->auth();

        $result = $this->entity->deleteSelected($this->userId);

        return $this->success();
    }

    /**
     * 获取选中商品统计
     */
    public function getSelectedTotal(): \think\Response
    {
        $this->auth();

        $result = $this->entity->getSelectedTotal($this->userId);

        return $this->success($result['data']);
    }
}
