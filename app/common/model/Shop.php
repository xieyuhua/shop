<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class Shop extends Model
{
    protected $table = 'shop';

    protected $type = [
        'user_id' => 'integer',
        'shop_name' => 'string',
        'shop_logo' => 'string',
        'shop_banner' => 'string',
        'shop_desc' => 'string',
        'contact_name' => 'string',
        'contact_mobile' => 'string',
        'contact_email' => 'string',
        'province' => 'string',
        'city' => 'string',
        'district' => 'string',
        'address' => 'string',
        'category_id' => 'integer',
        'business_license' => 'string',
        'status' => 'integer',
        'is_recommend' => 'integer',
        'sort' => 'integer',
        'total_sales' => 'integer',
        'total_amount' => 'float',
        'frozen_amount' => 'float',
        'commission_rate' => 'float',
    ];

    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_REJECTED = 2;
    const STATUS_CLOSED = 3;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'shop_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'shop_id');
    }

    public function getStatusTextAttr()
    {
        $status = [
            self::STATUS_PENDING => '待审核',
            self::STATUS_ACTIVE => '已开通',
            self::STATUS_REJECTED => '已拒绝',
            self::STATUS_CLOSED => '已关闭',
        ];
        return $status[$this->status] ?? '';
    }

    public static function apply($userId, $data)
    {
        $shop = new self();
        $shop->user_id = $userId;
        $shop->shop_name = $data['shop_name'];
        $shop->shop_logo = $data['shop_logo'] ?? '';
        $shop->shop_banner = $data['shop_banner'] ?? '';
        $shop->shop_desc = $data['shop_desc'] ?? '';
        $shop->contact_name = $data['contact_name'];
        $shop->contact_mobile = $data['contact_mobile'];
        $shop->contact_email = $data['contact_email'] ?? '';
        $shop->province = $data['province'];
        $shop->city = $data['city'];
        $shop->district = $data['district'];
        $shop->address = $data['address'];
        $shop->category_id = $data['category_id'];
        $shop->business_license = $data['business_license'] ?? '';
        $shop->status = self::STATUS_PENDING;
        $shop->commission_rate = 0.05;
        $shop->save();

        return $shop;
    }

    public function getFullAddressAttr()
    {
        return $this->province . $this->city . $this->district . $this->address;
    }

    public static function getActiveShops()
    {
        return self::where('status', self::STATUS_ACTIVE)->select();
    }
}
