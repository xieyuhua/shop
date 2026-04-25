<?php
declare(strict_types=1);

namespace app\api\validate;

use think\Validate;

class UserValidate extends Validate
{
    protected $rule = [
        'mobile' => 'require|mobile',
        'password' => 'require|min:6|max:32',
        'nickname' => 'max:50',
        'email' => 'email',
        'status' => 'number|in:0,1',
    ];

    protected $message = [
        'mobile.require' => '手机号不能为空',
        'mobile.mobile' => '手机号格式不正确',
        'password.require' => '密码不能为空',
        'password.min' => '密码至少6个字符',
        'password.max' => '密码最多32个字符',
        'nickname.max' => '昵称最多50个字符',
        'email.email' => '邮箱格式不正确',
        'status.number' => '状态必须是数字',
        'status.in' => '状态值不正确',
    ];

    protected $scene = [
        'save' => ['mobile', 'password'],
        'update' => ['mobile'],
    ];
}