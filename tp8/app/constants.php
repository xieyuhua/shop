<?php

return [
    // 订单状态
    'ORDER_STATUS' => [
        'PENDING' => 0,
        'PAID' => 1,
        'SHIPPED' => 2,
        'RECEIVED' => 3,
        'CANCELLED' => 4,
        'REFUNDED' => 5,
    ],

    // 支付方式
    'PAY_TYPE' => [
        'WECHAT' => 1,
        'ALIPAY' => 2,
        'BALANCE' => 3,
    ],

    // 用户状态
    'USER_STATUS' => [
        'DISABLED' => 0,
        'ACTIVE' => 1,
    ],

    // 性别
    'GENDER' => [
        'UNKNOWN' => 0,
        'MALE' => 1,
        'FEMALE' => 2,
    ],

    // 商品状态
    'PRODUCT_STATUS' => [
        'OFFLINE' => 0,
        'ONLINE' => 1,
    ],

    // 通知类型
    'NOTIFY_TYPE' => [
        'SYSTEM' => 1,
        'ORDER' => 2,
        'USER' => 3,
        'PRODUCT' => 4,
    ],

    // 消息类型
    'NOTIFY_TYPE_TEXT' => [
        1 => '系统通知',
        2 => '订单通知',
        3 => '用户通知',
        4 => '商品通知',
    ],

    // 订单状态文本
    'ORDER_STATUS_TEXT' => [
        0 => '待付款',
        1 => '待发货',
        2 => '待收货',
        3 => '已完成',
        4 => '已取消',
        5 => '已退款',
    ],

    // 支付方式文本
    'PAY_TYPE_TEXT' => [
        1 => '微信支付',
        2 => '支付宝',
        3 => '余额支付',
    ],

    // 性别文本
    'GENDER_TEXT' => [
        0 => '未知',
        1 => '男',
        2 => '女',
    ],
];