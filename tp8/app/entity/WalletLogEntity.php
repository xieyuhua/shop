<?php
declare(strict_types=1);

namespace app\entity;

class WalletLogEntity extends Entity
{
    protected $name = 'wallet_log';
    protected $pk = 'id';
    protected $createTime = 'create_time';

    public const TYPE_RECHARGE = 1;
    public const TYPE_CONSUME = 2;
    public const TYPE_REFUND = 3;

    public function getTypeText(): string
    {
        $map = [1 => '充值', 2 => '消费', 3 => '退款'];
        return $map[$this->type] ?? '未知';
    }

    public function getAmountFormat(): string
    {
        return '￥' . number_format($this->amount, 2);
    }

    public function getBeforeBalanceFormat(): string
    {
        return '￥' . number_format($this->before_balance, 2);
    }

    public function getAfterBalanceFormat(): string
    {
        return '￥' . number_format($this->after_balance, 2);
    }
}