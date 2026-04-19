<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\Shop as ShopModel;

/**
 * 店铺实体 - 处理店铺相关业务逻辑
 */
class ShopEntity
{
    /**
     * 申请入驻
     */
    public function apply(int $userId, array $data): array
    {
        // 检查是否已有店铺
        $existing = ShopModel::where('user_id', $userId)->find();
        if ($existing) {
            return ['success' => false, 'msg' => '您已申请过店铺'];
        }

        $errors = $this->validateApply($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $shop = new ShopModel();
        $shop->user_id = $userId;
        $shop->shop_name = $data['shop_name'];
        $shop->shop_desc = $data['shop_desc'] ?? '';
        $shop->shop_logo = $data['shop_logo'] ?? '';
        $shop->contact_name = $data['contact_name'];
        $shop->contact_mobile = $data['contact_mobile'];
        $shop->category_id = $data['category_id'] ?? 0;
        $shop->status = ShopModel::STATUS_PENDING;
        $shop->save();

        return [
            'success' => true,
            'data' => [
                'shop_id' => $shop->id,
                'status' => $shop->status,
            ],
        ];
    }

    /**
     * 获取店铺信息
     */
    public function getInfo(int $shopId): ?array
    {
        $shop = ShopModel::find($shopId);
        if (!$shop) {
            return null;
        }
        return $this->formatShopInfo($shop);
    }

    /**
     * 获取用户的店铺
     */
    public function getUserShop(int $userId): ?array
    {
        $shop = ShopModel::where('user_id', $userId)->find();
        if (!$shop) {
            return null;
        }
        return $this->formatShopInfo($shop);
    }

    /**
     * 更新店铺信息
     */
    public function update(int $shopId, int $userId, array $data): array
    {
        $shop = ShopModel::where('id', $shopId)
            ->where('user_id', $userId)
            ->find();

        if (!$shop) {
            return ['success' => false, 'msg' => '店铺不存在'];
        }

        if (isset($data['shop_name'])) {
            $shop->shop_name = $data['shop_name'];
        }
        if (isset($data['shop_desc'])) {
            $shop->shop_desc = $data['shop_desc'];
        }
        if (isset($data['shop_logo'])) {
            $shop->shop_logo = $data['shop_logo'];
        }
        if (isset($data['contact_name'])) {
            $shop->contact_name = $data['contact_name'];
        }
        if (isset($data['contact_mobile'])) {
            $shop->contact_mobile = $data['contact_mobile'];
        }

        $shop->save();

        return [
            'success' => true,
            'data' => $this->formatShopInfo($shop),
        ];
    }

    /**
     * 审核店铺（后台）
     */
    public function audit(int $shopId, int $status, string $reason = ''): array
    {
        $shop = ShopModel::find($shopId);
        if (!$shop) {
            return ['success' => false, 'msg' => '店铺不存在'];
        }

        if ($shop->status != ShopModel::STATUS_PENDING) {
            return ['success' => false, 'msg' => '店铺状态不允许审核'];
        }

        $shop->status = $status;
        if ($status == ShopModel::STATUS_REJECTED) {
            $shop->reject_reason = $reason;
        }
        $shop->audit_time = time();
        $shop->save();

        return ['success' => true];
    }

    /**
     * 获取店铺列表
     */
    public function getList(array $filters = [], int $page = 1, int $limit = 15): array
    {
        $query = ShopModel::with(['user', 'category'])
            ->order('id', 'desc');

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['shop_name'])) {
            $query->where('shop_name', 'like', '%' . $filters['shop_name'] . '%');
        }

        $total = $query->count();
        $list = $query->page($page, $limit)->select();

        $data = [];
        foreach ($list as $shop) {
            $data[] = $this->formatShopInfo($shop);
        }

        return [
            'success' => true,
            'data' => [
                'list' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit),
            ],
        ];
    }

    /**
     * 验证入驻申请
     */
    private function validateApply(array $data): array
    {
        $errors = [];

        if (empty($data['shop_name'])) {
            $errors['shop_name'] = '请输入店铺名称';
        }

        if (empty($data['contact_name'])) {
            $errors['contact_name'] = '请输入联系人姓名';
        }

        if (empty($data['contact_mobile'])) {
            $errors['contact_mobile'] = '请输入联系电话';
        } elseif (!preg_match('/^1[3-9]\d{9}$/', $data['contact_mobile'])) {
            $errors['contact_mobile'] = '手机号格式不正确';
        }

        return $errors;
    }

    /**
     * 格式化店铺信息
     */
    private function formatShopInfo(ShopModel $shop): array
    {
        return [
            'id' => $shop->id,
            'user_id' => $shop->user_id,
            'shop_name' => $shop->shop_name,
            'shop_desc' => $shop->shop_desc,
            'shop_logo' => $shop->shop_logo,
            'contact_name' => $shop->contact_name,
            'contact_mobile' => $shop->contact_mobile,
            'category_id' => $shop->category_id,
            'status' => $shop->status,
            'status_text' => $shop->status_text,
            'create_time' => $shop->create_time,
        ];
    }
}
