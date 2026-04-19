<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\model\Shop;
use app\common\model\Product;

class ShopController extends BaseController
{
    public function list()
    {
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 15);
        $categoryId = $this->request->get('category_id', 0);
        $keyword = $this->request->get('keyword', '');
        $sort = $this->request->get('sort', 'default');

        $query = Shop::where('status', Shop::STATUS_ACTIVE);

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if ($keyword) {
            $query->where('shop_name', 'like', '%' . $keyword . '%');
        }

        switch ($sort) {
            case 'sales':
                $query->order('total_sales', 'desc');
                break;
            case 'new':
                $query->order('create_time', 'desc');
                break;
            case 'recommend':
                $query->where('is_recommend', 1)->order('sort', 'asc');
                break;
            default:
                $query->order('sort', 'asc');
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

        $shop = Shop::with(['category'])
            ->where('id', $id)
            ->where('status', Shop::STATUS_ACTIVE)
            ->find();

        if (!$shop) {
            return $this->error('店铺不存在');
        }

        return $this->success($shop);
    }

    public function products()
    {
        $this->auth();
        $shopId = $this->request->get('shop_id', 0);
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 15);
        $sort = $this->request->get('sort', 'latest');

        if ($shopId <= 0) {
            $shopId = $this->shopId;
        }

        $query = Product::with(['category'])
            ->where('shop_id', $shopId)
            ->where('is_on_sale', 1)
            ->where('status', Product::STATUS_PASS);

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

    public function apply()
    {
        $this->auth();
        $data = $this->request->post();

        if (empty($data['shop_name'])) {
            return $this->error('请输入店铺名称');
        }
        if (empty($data['contact_name'])) {
            return $this->error('请输入联系人');
        }
        if (empty($data['contact_mobile'])) {
            return $this->error('请输入联系电话');
        }
        if (empty($data['category_id'])) {
            return $this->error('请选择店铺分类');
        }

        $exists = Shop::where('user_id', $this->userId)->find();
        if ($exists) {
            return $this->error('您已申请过店铺');
        }

        $shop = Shop::apply($this->userId, $data);

        return $this->success($shop);
    }

    public function info()
    {
        $this->auth();
        $this->shopAuth();

        $shop = Shop::with(['category'])
            ->where('user_id', $this->userId)
            ->find();

        if (!$shop) {
            return $this->error('店铺不存在');
        }

        return $this->success($shop);
    }

    public function statistics()
    {
        $this->auth();
        $this->shopAuth();

        $shop = Shop::where('id', $this->shopId)->find();

        $data = [
            'total_products' => Product::where('shop_id', $this->shopId)->count(),
            'total_sales' => $shop->total_sales ?? 0,
            'total_amount' => $shop->total_amount ?? 0,
            'frozen_amount' => $shop->frozen_amount ?? 0,
        ];

        return $this->success($data);
    }
}
