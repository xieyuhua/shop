<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Product;
use app\common\model\ProductSku;
use app\common\model\ProductSpec;
use app\common\model\Shop;
use app\common\model\Category;

/**
 * 后台商品实体
 */
class AdminProductEntity extends BaseEntity
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
        $keyword = $params['keyword'] ?? '';
        $categoryId = (int) ($params['category_id'] ?? 0);
        $shopId = (int) ($params['shop_id'] ?? 0);
        $status = $params['status'] ?? '';

        $query = self::with(['shop', 'category']);

        if ($keyword) {
            $keyword = addcslashes($keyword, '%_');
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if ($shopId > 0) {
            $query->where('shop_id', $shopId);
        }

        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->order('create_time', 'desc')->page($page, $limit)->select();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取商品详情
     */
    public function getDetail(int $id): ?array
    {
        $product = self::with(['shop', 'category', 'specs', 'skus'])->find($id);
        return $product ? $product->toArray() : null;
    }

    /**
     * 创建商品
     */
    public function create(int $adminId, array $data): array
    {
        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            return ['success' => false, 'msg' => implode(', ', $errors)];
        }

        $product = Product::createProduct($data['shop_id'], $data);

        if (!empty($data['specs'])) {
            foreach ($data['specs'] as $spec) {
                $productSpec = new ProductSpec();
                $productSpec->product_id = $product->id;
                $productSpec->name = $spec['name'];
                $productSpec->values = $spec['values'];
                $productSpec->sort = $spec['sort'] ?? 100;
                $productSpec->save();
            }
        }

        if (!empty($data['skus'])) {
            foreach ($data['skus'] as $sku) {
                $productSku = new ProductSku();
                $productSku->product_id = $product->id;
                $productSku->sku_name = $sku['sku_name'];
                $productSku->sku_code = $sku['sku_code'] ?? '';
                $productSku->price = $sku['price'];
                $productSku->original_price = $sku['original_price'] ?? 0;
                $productSku->cost_price = $sku['cost_price'] ?? 0;
                $productSku->stock = $sku['stock'] ?? 0;
                $productSku->weight = $sku['weight'] ?? 0;
                $productSku->image = $sku['image'] ?? '';
                $productSku->specs = $sku['specs'] ?? [];
                $productSku->save();
            }
        }

        return ['success' => true, 'data' => $product->toArray()];
    }

    /**
     * 更新商品
     */
    public function update(array $data): array
    {
        if (empty($data['id'])) {
            return ['success' => false, 'msg' => '商品ID不能为空'];
        }

        $product = self::find($data['id']);
        if (!$product) {
            return ['success' => false, 'msg' => '商品不存在'];
        }

        $fields = [
            'category_id', 'name', 'subtitle', 'image', 'images',
            'price', 'original_price', 'cost_price', 'stock', 'weight',
            'unit', 'content', 'is_on_sale', 'is_recommend', 'is_new',
            'freight_type', 'freight_money'
        ];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $product->$field = $data[$field];
            }
        }

        $product->save();

        return ['success' => true, 'data' => $product->toArray()];
    }

    /**
     * 删除商品
     */
    public function delete(int $id): array
    {
        $product = self::find($id);
        if (!$product) {
            return ['success' => false, 'msg' => '商品不存在'];
        }

        if ($product->sales > 0) {
            return ['success' => false, 'msg' => '该商品有销售记录，无法删除'];
        }

        ProductSku::where('product_id', $id)->delete();
        ProductSpec::where('product_id', $id)->delete();
        $product->delete();

        return ['success' => true];
    }

    /**
     * 审核商品
     */
    public function audit(int $id, int $status): array
    {
        $product = self::find($id);
        if (!$product) {
            return ['success' => false, 'msg' => '商品不存在'];
        }

        if ($product->status != Product::STATUS_PENDING) {
            return ['success' => false, 'msg' => '商品状态不允许审核'];
        }

        $product->status = $status;
        $product->save();

        return ['success' => true];
    }

    /**
     * 批量更新
     */
    public function batchUpdate(array $ids, array $data): array
    {
        if (empty($ids)) {
            return ['success' => false, 'msg' => '请选择商品'];
        }

        $updateData = [];
        $allowedFields = ['is_on_sale', 'is_recommend', 'is_new'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }

        if (!empty($updateData)) {
            self::whereIn('id', $ids)->update($updateData);
        }

        return ['success' => true];
    }

    private function validateProduct(array $data): array
    {
        $errors = [];

        if (empty($data['shop_id'])) {
            $errors[] = '请选择店铺';
        }
        if (empty($data['category_id'])) {
            $errors[] = '请选择分类';
        }
        if (empty($data['name'])) {
            $errors[] = '请输入商品名称';
        }
        if (!isset($data['price']) || $data['price'] <= 0) {
            $errors[] = '请输入商品价格';
        }

        return $errors;
    }
}
