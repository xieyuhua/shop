<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\entity\ProductEntity;
use app\common\entity\CategoryEntity;

/**
 * 商品控制器 - 仅接收参数和返回结果
 */
class Product extends BaseController
{
    private ProductEntity $productEntity;
    private CategoryEntity $categoryEntity;

    public function __construct()
    {
        parent::__construct();
        $this->productEntity = new ProductEntity();
        $this->categoryEntity = new CategoryEntity();
    }

    /**
     * 商品列表
     */
    public function list(): \think\Response
    {
        $params = [
            'page' => $this->request->get('page', 1),
            'limit' => $this->request->get('limit', 15),
            'category_id' => $this->request->get('category_id', 0),
            'shop_id' => $this->request->get('shop_id', 0),
            'keyword' => $this->request->get('keyword', ''),
            'sort' => $this->request->get('sort', 'latest'),
            'is_new' => $this->request->get('is_new', 0),
            'is_recommend' => $this->request->get('is_recommend', 0),
        ];

        $result = $this->productEntity->getList($params);

        return $this->success($result);
    }

    /**
     * 商品详情
     */
    public function detail(): \think\Response
    {
        $id = (int) $this->request->get('id');

        $product = $this->productEntity->getDetail($id);

        if (!$product) {
            return $this->error('商品不存在');
        }

        return $this->success($product);
    }

    /**
     * 商品评价
     */
    public function comment(): \think\Response
    {
        $id = (int) $this->request->get('id');
        $page = (int) $this->request->get('page', 1);
        $limit = (int) $this->request->get('limit', 15);

        $result = $this->productEntity->getComments($id, $page, $limit);

        return $this->success($result);
    }

    /**
     * 分类列表
     */
    public function category(): \think\Response
    {
        $pid = (int) $this->request->get('pid', 0);

        $list = $this->categoryEntity->getList($pid);

        return $this->success($list);
    }

    /**
     * 分类树
     */
    public function categoryTree(): \think\Response
    {
        $list = $this->categoryEntity->getTree();

        return $this->success($list);
    }

    /**
     * 推荐商品
     */
    public function recommend(): \think\Response
    {
        $limit = (int) $this->request->get('limit', 10);

        $list = $this->productEntity->getRecommend($limit);

        return $this->success($list);
    }

    /**
     * 新品上架
     */
    public function newArrival(): \think\Response
    {
        $limit = (int) $this->request->get('limit', 10);

        $list = $this->productEntity->getNewArrivals($limit);

        return $this->success($list);
    }
}
