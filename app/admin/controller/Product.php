<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\model\Product;
use app\common\model\ProductSku;
use app\common\model\ProductSpec;

class ProductController extends BaseController
{
    public function list()
    {
        $this->adminAuth();
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 15);
        $keyword = $this->request->get('keyword', '');
        $categoryId = $this->request->get('category_id', 0);
        $shopId = $this->request->get('shop_id', 0);
        $status = $this->request->get('status', '');

        $query = Product::with(['shop', 'category']);

        if ($keyword) {
            $query = $query->where('name', 'like', '%' . $keyword . '%');
        }

        if ($categoryId > 0) {
            $query = $query->where('category_id', $categoryId);
        }

        if ($shopId > 0) {
            $query = $query->where('shop_id', $shopId);
        }

        if ($status !== '') {
            $query = $query->where('status', $status);
        }

        $total = $query->count();
        $list = $query->order('create_time', 'desc')->page($page, $limit)->select();

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    public function detail()
    {
        $this->adminAuth();
        $id = $this->request->get('id');

        $product = Product::with(['shop', 'category', 'specs', 'skus'])->find($id);
        if (!$product) {
            return $this->error('商品不存在');
        }

        return $this->success($product);
    }

    public function create()
    {
        $this->adminAuth();
        $data = $this->request->post();

        if (empty($data['shop_id'])) {
            return $this->error('请选择店铺');
        }
        if (empty($data['category_id'])) {
            return $this->error('请选择分类');
        }
        if (empty($data['name'])) {
            return $this->error('请输入商品名称');
        }
        if (!isset($data['price']) || $data['price'] <= 0) {
            return $this->error('请输入商品价格');
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

        return $this->success($product);
    }

    public function update()
    {
        $this->adminAuth();
        $data = $this->request->post();

        $product = Product::find($data['id']);
        if (!$product) {
            return $this->error('商品不存在');
        }

        $product->category_id = $data['category_id'] ?? $product->category_id;
        $product->name = $data['name'] ?? $product->name;
        $product->subtitle = $data['subtitle'] ?? $product->subtitle;
        $product->image = $data['image'] ?? $product->image;
        $product->images = $data['images'] ?? $product->images;
        $product->price = $data['price'] ?? $product->price;
        $product->original_price = $data['original_price'] ?? $product->original_price;
        $product->cost_price = $data['cost_price'] ?? $product->cost_price;
        $product->stock = $data['stock'] ?? $product->stock;
        $product->weight = $data['weight'] ?? $product->weight;
        $product->unit = $data['unit'] ?? $product->unit;
        $product->content = $data['content'] ?? $product->content;
        $product->is_on_sale = $data['is_on_sale'] ?? $product->is_on_sale;
        $product->is_recommend = $data['is_recommend'] ?? $product->is_recommend;
        $product->is_new = $data['is_new'] ?? $product->is_new;
        $product->freight_type = $data['freight_type'] ?? $product->freight_type;
        $product->freight_money = $data['freight_money'] ?? $product->freight_money;
        $product->save();

        return $this->success($product);
    }

    public function delete()
    {
        $this->adminAuth();
        $id = $this->request->post('id');

        $product = Product::find($id);
        if (!$product) {
            return $this->error('商品不存在');
        }

        if ($product->sales > 0) {
            return $this->error('该商品有销售记录，无法删除');
        }

        ProductSku::where('product_id', $id)->delete();
        ProductSpec::where('product_id', $id)->delete();
        $product->delete();

        return $this->success();
    }

    public function audit()
    {
        $this->adminAuth();
        $data = $this->request->post();

        $product = Product::find($data['id']);
        if (!$product) {
            return $this->error('商品不存在');
        }

        if ($product->status != Product::STATUS_PENDING) {
            return $this->error('商品状态不允许审核');
        }

        if ($data['status'] == Product::STATUS_PASS) {
            $product->status = Product::STATUS_PASS;
        } else {
            $product->status = Product::STATUS_REJECT;
        }

        $product->save();

        return $this->success();
    }

    public function batchUpdate()
    {
        $this->adminAuth();
        $data = $this->request->post();

        $ids = $data['ids'] ?? [];
        if (empty($ids)) {
            return $this->error('请选择商品');
        }

        $updateData = [];
        if (isset($data['is_on_sale'])) {
            $updateData['is_on_sale'] = $data['is_on_sale'];
        }
        if (isset($data['is_recommend'])) {
            $updateData['is_recommend'] = $data['is_recommend'];
        }
        if (isset($data['is_new'])) {
            $updateData['is_new'] = $data['is_new'];
        }

        if (!empty($updateData)) {
            Product::whereIn('id', $ids)->update($updateData);
        }

        return $this->success();
    }
}
