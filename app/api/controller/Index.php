<?php

declare(strict_types=1);

namespace app\api\controller;

use app\common\controller\BaseController;
use app\common\model\Product;
use app\common\model\Category;
use app\common\model\Shop;

class Index extends BaseController
{
    public function index()
    {
        $banners = [];
        $categories = [];
        $hotProducts = [];
        $newProducts = [];
        $recommendShops = [];

        $categories = Category::where('is_show', 1)
            ->where('is_nav', 1)
            ->order('sort', 'asc')
            ->limit(10)
            ->select();

        $hotProducts = Product::with(['shop'])
            ->where('is_on_sale', 1)
            ->where('is_recommend', 1)
            ->where('status', Product::STATUS_PASS)
            ->order('sales', 'desc')
            ->limit(10)
            ->select();

        $newProducts = Product::with(['shop'])
            ->where('is_on_sale', 1)
            ->where('is_new', 1)
            ->where('status', Product::STATUS_PASS)
            ->order('create_time', 'desc')
            ->limit(10)
            ->select();

        $recommendShops = Shop::where('status', Shop::STATUS_ACTIVE)
            ->where('is_recommend', 1)
            ->order('sort', 'asc')
            ->limit(6)
            ->select();

        return $this->success([
            'banners' => $banners,
            'categories' => $categories,
            'hot_products' => $hotProducts,
            'new_products' => $newProducts,
            'recommend_shops' => $recommendShops,
        ]);
    }

    public function config()
    {
        $configs = [
            'mall_name' => '多用户B2B2C商城',
            'mall_logo' => '/static/images/logo.png',
            'mall_phone' => '400-000-0000',
            'mall_qq' => '12345678',
            'copyright' => '© 2024 All Rights Reserved',
            'icp' => 'ICP备XXXXXXXX号',
            'about_us' => '关于我们的描述...',
            'agreement' => '用户协议内容...',
            'express_money' => 10,
            'free_express_money' => 99,
        ];

        return $this->success($configs);
    }
}
