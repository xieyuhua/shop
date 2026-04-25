<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class ValidateService
{
    public static function checkUser(int $userId): array
    {
        if ($userId <= 0) {
            return ['code' => 400, 'msg' => '用户ID无效'];
        }

        $user = Db::name('user')->find($userId);
        if (!$user) {
            return ['code' => 404, 'msg' => '用户不存在'];
        }

        if ($user['status'] == 0) {
            return ['code' => 403, 'msg' => '用户已被禁用'];
        }

        return ['code' => 200, 'msg' => 'ok'];
    }

    public static function checkProduct(int $productId): array
    {
        if ($productId <= 0) {
            return ['code' => 400, 'msg' => '商品ID无效'];
        }

        $product = Db::name('product')->find($productId);
        if (!$product) {
            return ['code' => 404, 'msg' => '商品不存在'];
        }

        if ($product['status'] == 0) {
            return ['code' => 400, 'msg' => '商品已下架'];
        }

        if ($product['stock'] <= 0) {
            return ['code' => 400, 'msg' => '商品库存不足'];
        }

        return ['code' => 200, 'msg' => 'ok'];
    }

    public static function checkCategory(int $categoryId): array
    {
        if ($categoryId <= 0) {
            return ['code' => 400, 'msg' => '分类ID无效'];
        }

        $category = Db::name('category')->find($categoryId);
        if (!$category) {
            return ['code' => 404, 'msg' => '分类不存在'];
        }

        if ($category['status'] == 0) {
            return ['code' => 400, 'msg' => '分类已隐藏'];
        }

        return ['code' => 200, 'msg' => 'ok'];
    }

    public static function checkOrder(int $orderId): array
    {
        if ($orderId <= 0) {
            return ['code' => 400, 'msg' => '订单ID无效'];
        }

        $order = Db::name('order')->find($orderId);
        if (!$order) {
            return ['code' => 404, 'msg' => '订单不存在'];
        }

        return ['code' => 200, 'msg' => 'ok', 'data' => $order];
    }

    public static function checkOrderStatus(int $orderId, array $allowStatus): array
    {
        $result = self::checkOrder($orderId);
        if ($result['code'] !== 200) {
            return $result;
        }

        $order = $result['data'];
        if (!in_array($order['status'], $allowStatus)) {
            return ['code' => 400, 'msg' => '订单状态不允许此操作'];
        }

        return ['code' => 200, 'msg' => 'ok'];
    }

    public static function checkMobile(string $mobile): array
    {
        if (empty($mobile)) {
            return ['code' => 400, 'msg' => '手机号不能为空'];
        }

        if (!preg_match('/^1[3-9]\d{9}$/', $mobile)) {
            return ['code' => 400, 'msg' => '手机号格式不正确'];
        }

        return ['code' => 200, 'msg' => 'ok'];
    }

    public static function checkEmail(string $email): array
    {
        if (empty($email)) {
            return ['code' => 200, 'msg' => 'ok'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['code' => 400, 'msg' => '邮箱格式不正确'];
        }

        return ['code' => 200, 'msg' => 'ok'];
    }

    public static function checkIdCard(string $idCard): array
    {
        if (empty($idCard)) {
            return ['code' => 200, 'msg' => 'ok'];
        }

        if (!preg_match('/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/', $idCard)) {
            return ['code' => 400, 'msg' => '身份证号格式不正确'];
        }

        return ['code' => 200, 'msg' => 'ok'];
    }

    public static function checkUnique(string $table, string $field, $value, int $excludeId = 0): array
    {
        $query = Db::name($table)->where($field, $value);
        
        if ($excludeId > 0) {
            $query->where('id', '<>', $excludeId);
        }

        $exists = $query->find();
        if ($exists) {
            return ['code' => 400, 'msg' => '该值已存在'];
        }

        return ['code' => 200, 'msg' => 'ok'];
    }
}