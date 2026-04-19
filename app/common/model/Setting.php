<?php

declare(strict_types=1);

namespace app\common\model;

use think\Model;

class Setting extends Model
{
    protected $table = 'setting';

    protected $type = [
        'key' => 'string',
        'group' => 'string',
        'value' => 'string',
        'type' => 'string',
    ];
}
