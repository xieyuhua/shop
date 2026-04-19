<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class BalanceLog extends Model
{
    protected $table = 'balance_log';

    protected $type = [
        'user_id' => 'integer',
        'shop_id' => 'integer',
        'type' => 'integer',
        'money' => 'float',
        'balance' => 'float',
        'before_money' => 'float',
        'after_money' => 'float',
        'remark' => 'string',
        'source_type' => 'string',
        'source_id' => 'integer',
    ];

    const TYPE_INCOME = 1;
    const TYPE_EXPEND = 2;
    const TYPE_FROZEN = 3;
    const TYPE_UNFROZEN = 4;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public static function change($userId, $money, $type, $remark, $sourceType = '', $sourceId = 0, $shopId = 0)
    {
        $user = User::find($userId);
        $beforeMoney = $user->balance;

        $log = new self();
        $log->user_id = $userId;
        $log->shop_id = $shopId;
        $log->type = $type;
        $log->money = abs($money);
        $log->balance = $money > 0 ? $money : -$money;
        $log->before_money = $beforeMoney;
        $log->after_money = $money > 0 ? $user->balance + $money : $user->balance - $money;
        $log->remark = $remark;
        $log->source_type = $sourceType;
        $log->source_id = $sourceId;
        $log->save();

        if ($type == self::TYPE_INCOME) {
            $user->balance = $user->balance + $money;
        } else {
            $user->balance = $user->balance - $money;
        }
        $user->save();

        return $log;
    }

    public static function frozen($userId, $money, $remark, $sourceType = '', $sourceId = 0)
    {
        $user = User::find($userId);
        if ($user->balance < $money) {
            return false;
        }

        $log = new self();
        $log->user_id = $userId;
        $log->type = self::TYPE_FROZEN;
        $log->money = $money;
        $log->balance = $money;
        $log->before_money = $user->balance;
        $log->after_money = $user->balance - $money;
        $log->remark = $remark;
        $log->source_type = $sourceType;
        $log->source_id = $sourceId;
        $log->save();

        $user->balance = $user->balance - $money;
        $user->frozen_balance = $user->frozen_balance + $money;
        $user->save();

        return $log;
    }
}
