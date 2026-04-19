<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class PointsLog extends Model
{
    protected $table = 'points_log';

    protected $type = [
        'user_id' => 'integer',
        'type' => 'integer',
        'points' => 'integer',
        'balance' => 'integer',
        'remark' => 'string',
        'source_id' => 'integer',
    ];

    const TYPE_INCOME = 1;
    const TYPE_EXPEND = 2;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function change($userId, $points, $type, $remark, $sourceId = 0)
    {
        $log = new self();
        $log->user_id = $userId;
        $log->type = $type;
        $log->points = abs($points);
        $log->balance = $points > 0 ? $points : -$points;
        $log->remark = $remark;
        $log->source_id = $sourceId;
        $log->save();

        $user = User::find($userId);
        if ($type == self::TYPE_INCOME) {
            $user->points = $user->points + $points;
        } else {
            $user->points = $user->points - $points;
        }
        $user->save();

        return $log;
    }
}
