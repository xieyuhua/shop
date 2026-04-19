<?php

declare(strict_types=1);

namespace app\common\entity;

use app\common\model\UserAddress as UserAddressModel;

/**
 * 用户地址实体 - 处理地址相关业务逻辑
 */
class UserAddressEntity
{
    /**
     * 创建收货地址
     */
    public function create(int $userId, array $data): array
    {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // 如果设为默认，先取消其他默认
        if (!empty($data['is_default'])) {
            UserAddressModel::where('user_id', $userId)->update(['is_default' => 0]);
        }

        $address = new UserAddressModel();
        $address->user_id = $userId;
        $address->consignee = $data['consignee'];
        $address->mobile = $data['mobile'];
        $address->province = $data['province'];
        $address->city = $data['city'];
        $address->district = $data['district'];
        $address->address = $data['address'];
        $address->zipcode = $data['zipcode'] ?? '';
        $address->is_default = $data['is_default'] ?? 0;
        $address->longitude = $data['longitude'] ?? 0;
        $address->latitude = $data['latitude'] ?? 0;
        $address->save();

        return [
            'success' => true,
            'data' => $address,
        ];
    }

    /**
     * 更新收货地址
     */
    public function update(int $addressId, int $userId, array $data): array
    {
        $address = UserAddressModel::where('id', $addressId)
            ->where('user_id', $userId)
            ->find();

        if (!$address) {
            return ['success' => false, 'msg' => '地址不存在'];
        }

        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // 如果设为默认，先取消其他默认
        if (!empty($data['is_default'])) {
            UserAddressModel::where('user_id', $userId)->update(['is_default' => 0]);
        }

        $address->consignee = $data['consignee'];
        $address->mobile = $data['mobile'];
        $address->province = $data['province'];
        $address->city = $data['city'];
        $address->district = $data['district'];
        $address->address = $data['address'];
        $address->zipcode = $data['zipcode'] ?? '';
        $address->is_default = $data['is_default'] ?? 0;
        $address->longitude = $data['longitude'] ?? 0;
        $address->latitude = $data['latitude'] ?? 0;
        $address->save();

        return [
            'success' => true,
            'data' => $address,
        ];
    }

    /**
     * 删除收货地址
     */
    public function delete(int $addressId, int $userId): array
    {
        $address = UserAddressModel::where('id', $addressId)
            ->where('user_id', $userId)
            ->find();

        if (!$address) {
            return ['success' => false, 'msg' => '地址不存在'];
        }

        $address->delete();

        return ['success' => true];
    }

    /**
     * 获取用户地址列表
     */
    public function getList(int $userId): array
    {
        $list = UserAddressModel::where('user_id', $userId)
            ->order('is_default', 'desc')
            ->order('id', 'desc')
            ->select();

        return [
            'success' => true,
            'data' => $list,
        ];
    }

    /**
     * 获取地址详情
     */
    public function getDetail(int $addressId, int $userId): array
    {
        $address = UserAddressModel::where('id', $addressId)
            ->where('user_id', $userId)
            ->find();

        if (!$address) {
            return ['success' => false, 'msg' => '地址不存在'];
        }

        return [
            'success' => true,
            'data' => $address,
        ];
    }

    /**
     * 设置默认地址
     */
    public function setDefault(int $addressId, int $userId): array
    {
        $address = UserAddressModel::where('id', $addressId)
            ->where('user_id', $userId)
            ->find();

        if (!$address) {
            return ['success' => false, 'msg' => '地址不存在'];
        }

        UserAddressModel::where('user_id', $userId)->update(['is_default' => 0]);
        $address->is_default = 1;
        $address->save();

        return ['success' => true];
    }

    /**
     * 获取默认地址
     */
    public function getDefault(int $userId): ?UserAddressModel
    {
        return UserAddressModel::where('user_id', $userId)
            ->where('is_default', 1)
            ->find();
    }

    /**
     * 验证地址数据
     */
    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['consignee'])) {
            $errors['consignee'] = '请输入收货人姓名';
        }

        if (empty($data['mobile'])) {
            $errors['mobile'] = '请输入手机号';
        } elseif (!preg_match('/^1[3-9]\d{9}$/', $data['mobile'])) {
            $errors['mobile'] = '手机号格式不正确';
        }

        if (empty($data['province'])) {
            $errors['province'] = '请选择省份';
        }

        if (empty($data['city'])) {
            $errors['city'] = '请选择城市';
        }

        if (empty($data['district'])) {
            $errors['district'] = '请选择区县';
        }

        if (empty($data['address'])) {
            $errors['address'] = '请输入详细地址';
        }

        return $errors;
    }
}
