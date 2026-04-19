<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\model\Product;
use app\common\model\Category;
use app\common\model\Shop;
use app\common\model\ProductSku;
use app\common\model\OrderEvaluate;

class Product extends BaseController
{
    public function list()
    {
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 15);
        $categoryId = $this->request->get('category_id', 0);
        $shopId = $this->request->get('shop_id', 0);
        $keyword = $this->request->get('keyword', '');
        $sort = $this->request->get('sort', 'latest');
        $isNew = $this->request->get('is_new', 0);
        $isRecommend = $this->request->get('is_recommend', 0);

        $query = Product::with(['shop', 'category'])
            ->where('is_on_sale', 1)
            ->where('status', Product::STATUS_PASS);

        if ($categoryId > 0) {
            $categoryIds = Category::where('pid', $categoryId)->column('id');
            $categoryIds[] = $categoryId;
            $query->whereIn('category_id', $categoryIds);
        }

        if ($shopId > 0) {
            $query->where('shop_id', $shopId);
        }

        if ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        if ($isNew) {
            $query->where('is_new', 1);
        }

        if ($isRecommend) {
            $query->where('is_recommend', 1);
        }

        switch ($sort) {
            case 'sales':
                $query->order('sales', 'desc');
                break;
            case 'price_asc':
                $query->order('price', 'asc');
                break;
            case 'price_desc':
                $query->order('price', 'desc');
                break;
            case 'latest':
            default:
                $query->order('create_time', 'desc');
                break;
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ]);
    }

    public function detail()
    {
        $id = $this->request->get('id');

        $product = Product::with(['shop', 'category', 'specs'])
            ->where('id', $id)
            ->where('is_on_sale', 1)
            ->find();

        if (!$product) {
            return $this->error('商品不存在');
        }

        $product->content = preg_replace('/<img src="\/uploads/', '<img src="' . request()->domain() . '/uploads/', $product->content);

        if ($product->spec_type == Product::SPEC_TYPE_MULTI) {
            $skus = ProductSku::where('product_id', $id)->select();
            $product->skus = $skus;
        }

        $product->evaluate_count = OrderEvaluate::where('product_id', $id)->count();
        $product->avg_score = OrderEvaluate::where('product_id', $id)->avg('score') ?? 5;

        return $this->success($product);
    }

    public function comment()
    {
        $id = $this->request->get('id');
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 15);

        $result = OrderEvaluate::getProductEvaluates($id, $page, $limit);

        return $this->success($result);
    }

    public function category()
    {
        $pid = $this->request->get('pid', 0);

        $list = Category::where('pid', $pid)
            ->where('is_show', 1)
            ->order('sort', 'asc')
            ->select();

        return $this->success($list);
    }

    public function categoryTree()
    {
        $list = Category::getTree();

        return $this->success($list);
    }

    public function recommend()
    {
        $limit = $this->request->get('limit', 10);

        $list = Product::with(['shop'])
            ->where('is_on_sale', 1)
            ->where('is_recommend', 1)
            ->where('status', Product::STATUS_PASS)
            ->limit($limit)
            ->select();

        return $this->success($list);
    }

    public function newArrival()
    {
        $limit = $this->request->get('limit', 10);

        $list = Product::with(['shop'])
            ->where('is_on_sale', 1)
            ->where('is_new', 1)
            ->where('status', Product::STATUS_PASS)
            ->limit($limit)
            ->select();

        return $this->success($list);
    }
}
