<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'shop_id' => 'integer',
        'category_id' => 'integer',
        'name' => 'string',
        'subtitle' => 'string',
        'image' => 'string',
        'images' => 'string',
        'price' => 'float',
        'original_price' => 'float',
        'cost_price' => 'float',
        'stock' => 'integer',
        'sales' => 'integer',
        'weight' => 'float',
        'unit' => 'string',
        'spec_type' => 'integer',
        'content' => 'string',
        'is_on_sale' => 'integer',
        'is_recommend' => 'integer',
        'is_new' => 'integer',
        'freight_type' => 'integer',
        'freight_id' => 'integer',
        'freight_money' => 'float',
        'status' => 'integer',
    ];

    const SPEC_TYPE_SINGLE = 0;
    const SPEC_TYPE_MULTI = 1;

    const STATUS_PENDING = 0;
    const STATUS_PASS = 1;
    const STATUS_REJECT = 2;

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function specs()
    {
        return $this->hasMany(ProductSpec::class, 'product_id');
    }

    public function skus()
    {
        return $this->hasMany(ProductSku::class, 'product_id');
    }

    public function evaluate()
    {
        return $this->hasMany(OrderEvaluate::class, 'product_id');
    }

    public function getImagesAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setImagesAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    public function getStatusTextAttr()
    {
        $status = [
            self::STATUS_PENDING => '待审核',
            self::STATUS_PASS => '已通过',
            self::STATUS_REJECT => '已拒绝',
        ];
        return $status[$this->status] ?? '';
    }

    public static function createProduct($shopId, $data)
    {
        $product = new self();
        $product->shop_id = $shopId;
        $product->category_id = $data['category_id'];
        $product->name = $data['name'];
        $product->subtitle = $data['subtitle'] ?? '';
        $product->image = $data['image'];
        $product->images = $data['images'] ?? [];
        $product->price = $data['price'];
        $product->original_price = $data['original_price'] ?? 0;
        $product->cost_price = $data['cost_price'] ?? 0;
        $product->stock = $data['stock'] ?? 0;
        $product->sales = 0;
        $product->weight = $data['weight'] ?? 0;
        $product->unit = $data['unit'] ?? '件';
        $product->spec_type = $data['spec_type'] ?? self::SPEC_TYPE_SINGLE;
        $product->content = $data['content'] ?? '';
        $product->is_on_sale = $data['is_on_sale'] ?? 1;
        $product->is_recommend = $data['is_recommend'] ?? 0;
        $product->is_new = $data['is_new'] ?? 0;
        $product->freight_type = $data['freight_type'] ?? 0;
        $product->freight_money = $data['freight_money'] ?? 0;
        $product->status = self::STATUS_PASS;
        $product->save();

        return $product;
    }

    public function decreaseStock($num)
    {
        if ($this->stock < $num) {
            return false;
        }
        $this->stock = $this->stock - $num;
        $this->sales = $this->sales + $num;
        return $this->save();
    }

    public function increaseStock($num)
    {
        $this->stock = $this->stock + $num;
        $this->sales = max(0, $this->sales - $num);
        return $this->save();
    }

    public function getFinalPrice($skuId = 0)
    {
        if ($this->spec_type == self::SPEC_TYPE_SINGLE) {
            return $this->price;
        }

        $sku = ProductSku::find($skuId);
        return $sku ? $sku->price : $this->price;
    }
}
