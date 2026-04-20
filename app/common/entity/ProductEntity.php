<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Product;
use app\common\model\ProductSku;
use app\common\model\Category;
use app\common\model\OrderEvaluate;
use app\common\model\Shop;

/**
 * 商品实体
 */
class ProductEntity extends BaseEntity
{
    protected $table = 'product';

    protected $type = [
        'price' => 'float',
        'cost_price' => 'float',
        'stock' => 'integer',
        'sales' => 'integer',
        'weight' => 'float',
    ];

    // ========== 业务逻辑 ==========

    /**
     * 获取商品列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $limit = min(50, max(1, (int) ($params['limit'] ?? 15)));
        $categoryId = (int) ($params['category_id'] ?? 0);
        $shopId = (int) ($params['shop_id'] ?? 0);
        $keyword = $params['keyword'] ?? '';
        $sort = $params['sort'] ?? 'latest';
        $isNew = (int) ($params['is_new'] ?? 0);
        $isRecommend = (int) ($params['is_recommend'] ?? 0);

        $query = self::with(['shop', 'category'])
            ->where('is_on_sale', 1)
            ->where('status', Product::STATUS_PASS);

        if ($categoryId > 0) {
            $categoryIds = Category::where('pid', $categoryId)->column('id') ?? [];
            $categoryIds[] = $categoryId;
            $query->whereIn('category_id', $categoryIds);
        }

        if ($shopId > 0) {
            $query->where('shop_id', $shopId);
        }

        if ($keyword) {
            $keyword = addcslashes($keyword, '%_');
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        if ($isNew) {
            $query->where('is_new', 1);
        }

        if ($isRecommend) {
            $query->where('is_recommend', 1);
        }

        $query = $this->applySort($query, $sort);

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }

    /**
     * 获取商品详情
     */
    public function getDetail(int $id): ?array
    {
        $product = self::with(['shop', 'category', 'specs'])
            ->where('id', $id)
            ->where('is_on_sale', 1)
            ->find();

        if (!$product) {
            return null;
        }

        $domain = request()->domain();
        $product->content = preg_replace(
            '/<img src="\/uploads/',
            '<img src="' . $domain . '/uploads/',
            $product->content ?? ''
        );

        if ($product->spec_type == Product::SPEC_TYPE_MULTI) {
            $product->skus = ProductSku::where('product_id', $id)->select();
        }

        $product->evaluate_count = OrderEvaluate::where('product_id', $id)->count();
        $product->avg_score = OrderEvaluate::where('product_id', $id)->avg('score') ?? 5;

        return $product->toArray();
    }

    /**
     * 获取商品评价
     */
    public function getComments(int $productId, int $page = 1, int $limit = 15): array
    {
        return OrderEvaluate::getProductEvaluates($productId, $page, $limit);
    }

    /**
     * 获取分类列表
     */
    public function getCategories(int $pid = 0): array
    {
        return Category::where('pid', $pid)
            ->where('is_show', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }

    /**
     * 获取分类树
     */
    public function getCategoryTree(): array
    {
        return Category::getTree();
    }

    /**
     * 获取推荐商品
     */
    public function getRecommend(int $limit = 10): array
    {
        return self::with(['shop'])
            ->where('is_on_sale', 1)
            ->where('is_recommend', 1)
            ->where('status', Product::STATUS_PASS)
            ->limit($limit)
            ->select()
            ->toArray();
    }

    /**
     * 获取新品
     */
    public function getNewArrivals(int $limit = 10): array
    {
        return self::with(['shop'])
            ->where('is_on_sale', 1)
            ->where('is_new', 1)
            ->where('status', Product::STATUS_PASS)
            ->limit($limit)
            ->select()
            ->toArray();
    }

    private function applySort($query, string $sort)
    {
        return match ($sort) {
            'sales' => $query->order('sales', 'desc'),
            'price_asc' => $query->order('price', 'asc'),
            'price_desc' => $query->order('price', 'desc'),
            default => $query->order('create_time', 'desc'),
        };
    }
}
