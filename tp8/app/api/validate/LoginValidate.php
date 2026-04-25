<?php
declare(strict_types=1);

namespace app\api\validate;

use think\Validate;

class LoginValidate extends Validate
{
    protected $rule = [
        'username' => 'require|alphaNum|min:3|max:20',
        'password' => 'require|min:6|max:32',
    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'username.alphaNum' => '用户名必须是字母数字',
        'username.min' => '用户名至少3个字符',
        'username.max' => '用户名最多20个字符',
        'password.require' => '密码不能为空',
        'password.min' => '密码至少6个字符',
        'password.max' => '密码最多32个字符',
    ];
}