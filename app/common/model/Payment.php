<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class Payment extends Model
{
    protected $table = 'payment';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'user_id' => 'integer',
        'order_id' => 'integer',
        'trade_no' => 'string',
        'out_trade_no' => 'string',
        'type' => 'integer',
        'amount' => 'float',
        'status' => 'integer',
        'pay_time' => 'integer',
        'notify_data' => 'string',
    ];

    const TYPE_WECHAT = 1;
    const TYPE_ALIPAY = 2;
    const TYPE_BALANCE = 3;

    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public static function createPayment($userId, $orderId, $type, $amount)
    {
        $payment = new self();
        $payment->user_id = $userId;
        $payment->order_id = $orderId;
        $payment->trade_no = self::createTradeNo();
        $payment->type = $type;
        $payment->amount = $amount;
        $payment->status = self::STATUS_PENDING;
        $payment->save();

        return $payment;
    }

    public static function createTradeNo()
    {
        return 'PAY' . date('YmdHis') . rand(100000, 999999);
    }

    public function success($tradeNo = '', $notifyData = [])
    {
        $this->out_trade_no = $tradeNo;
        $this->status = self::STATUS_SUCCESS;
        $this->pay_time = time();
        $this->notify_data = json_encode($notifyData);
        return $this->save();
    }

    public function failed()
    {
        $this->status = self::STATUS_FAILED;
        return $this->save();
    }

    public function getTypeTextAttr()
    {
        $types = [
            self::TYPE_WECHAT => '微信支付',
            self::TYPE_ALIPAY => '支付宝',
            self::TYPE_BALANCE => '余额支付',
        ];
        return $types[$this->type] ?? '';
    }

    public function getStatusTextAttr()
    {
        $status = [
            self::STATUS_PENDING => '待支付',
            self::STATUS_SUCCESS => '已支付',
            self::STATUS_FAILED => '支付失败',
        ];
        return $status[$this->status] ?? '';
    }
}
