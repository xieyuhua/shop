<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\ShopControllerEntity;

/**
 * 店铺控制器 - 仅接收参数和返回结果
 */
class ShopController extends BaseController
{
    private ShopControllerEntity $entity;

    public function __construct()
    {
        parent::__construct();
        $this->entity = new ShopControllerEntity();
    }

    /**
     * 店铺列表
     */
    public function list(): \think\Response
    {
        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'category_id' => $this->request->get('category_id', 0),
            'keyword' => $this->request->get('keyword', ''),
            'sort' => $this->request->get('sort', 'default'),
        ];

        $result = $this->entity->getList($params);

        return $this->success($result);
    }

    /**
     * 店铺详情
     */
    public function detail(): \think\Response
    {
        $id = (int) $this->request->get('id', 0);

        $shop = $this->entity->getDetail($id);

        if (!$shop) {
            return $this->error('店铺不存在');
        }

        return $this->success($shop);
    }

    /**
     * 店铺商品列表
     */
    public function products(): \think\Response
    {
        $this->auth();

        $shopId = (int) $this->request->get('shop_id', 0);
        if ($shopId <= 0) {
            $shopId = $this->shopId;
        }

        $params = [
            'shop_id' => $shopId,
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'sort' => $this->request->get('sort', 'latest'),
        ];

        $result = $this->entity->getProducts($params);

        return $this->success($result);
    }

    /**
     * 申请店铺
     */
    public function apply(): \think\Response
    {
        $this->auth();

        $data = $this->request->post();

        $result = $this->entity->apply($this->userId, $data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 我的店铺信息
     */
    public function info(): \think\Response
    {
        $this->auth();
        $this->shopAuth();

        $shop = $this->entity->getMyShop($this->userId);

        if (!$shop) {
            return $this->error('店铺不存在');
        }

        return $this->success($shop);
    }

    /**
     * 店铺统计
     */
    public function statistics(): \think\Response
    {
        $this->auth();
        $this->shopAuth();

        $data = $this->entity->getStatistics($this->shopId);

        return $this->success($data);
    }
}
