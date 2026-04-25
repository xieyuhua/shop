<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $image
 * @property array|string|null $images
 * @property string|null $description
 * @property float $price
 * @property float $original_price
 * @property float $cost_price
 * @property int $stock
 * @property int $sales
 * @property int $virtual_sales
 * @property array|string|null $specs
 * @property int $is_spec
 * @property int $is_hot
 * @property int $is_new
 * @property int $is_recommend
 * @property int $status
 * @property string $create_time
 * @property string|null $update_time
 */
class ProductModel extends Model
{
    protected $name = 'product';
    protected $pk = 'id';
    protected $autoWriteTimestamp = 'datetime';
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';

    protected function setImagesAttr($value): string
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
    }

    protected function getImagesAttr($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    protected function setSpecsAttr($value): string
    {
        return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
    }

    protected function getSpecsAttr($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function isOnSale(): bool
    {
        return $this->status === 1 && $this->stock > 0;
    }

    public function isHot(): bool
    {
        return (bool)$this->is_hot;
    }

    public function isNew(): bool
    {
        return (bool)$this->is_new;
    }

    public function isRecommend(): bool
    {
        return (bool)$this->is_recommend;
    }

    public function getPriceFormatAttribute(): string
    {
        return '￥' . number_format($this->price, 2);
    }

    public function getOriginalPriceFormatAttribute(): string
    {
        return '￥' . number_format($this->original_price, 2);
    }

    public function getTotalSalesAttribute(): int
    {
        return $this->sales + $this->virtual_sales;
    }
}