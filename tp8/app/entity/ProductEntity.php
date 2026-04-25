<?php
declare(strict_types=1);

namespace app\entity;

class ProductEntity extends Entity
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
        return $this->status == 1 && $this->stock > 0;
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

    public function haveSpecs(): bool
    {
        return (bool)$this->is_spec;
    }

    public function getStatusText(): string
    {
        return $this->status ? '上架' : '下架';
    }

    public function getPriceFormat(): string
    {
        return '￥' . number_format($this->price, 2);
    }

    public function getOriginalPriceFormat(): string
    {
        return '￥' . number_format($this->original_price, 2);
    }

    public function getDiscount(): float
    {
        if ($this->original_price > 0) {
            return round($this->price / $this->original_price * 10, 1);
        }
        return 0;
    }

    public function getTotalSales(): int
    {
        return $this->sales + $this->virtual_sales;
    }
}