<?php

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

abstract class BaseController
{
    protected $app;
    protected $request;
    protected $middleware = [];

    protected $userId = 0;
    protected $userInfo = null;
    protected $shopId = 0;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
        $this->initialize();
    }

    protected function initialize()
    {
    }

    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                list($validate, $scene) = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass($validate, __DIR__ . DS . 'validate');
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        return $v->message($message)->batch($batch)->check($data);
    }

    protected function auth()
    {
        $token = $this->request->header('Authorization', '');
        if (empty($token)) {
            $token = $this->request->param('token', '');
        }
        
        if (empty($token)) {
            throw new \think\exception\HttpResponseException(json(['code' => 401, 'msg' => '请先登录'])->respond());
        }

        $auth = \app\common\library\Token::get($token);
        if (!$auth) {
            throw new \think\exception\HttpResponseException(json(['code' => 401, 'msg' => '登录已过期'])->respond());
        }

        $this->userId = $auth['user_id'];
        $this->shopId = $auth['shop_id'] ?? 0;
        $this->userInfo = \app\common\model\User::find($this->userId);

        if (!$this->userInfo) {
            throw new \think\exception\HttpResponseException(json(['code' => 401, 'msg' => '用户不存在'])->respond());
        }

        return $auth;
    }

    protected function shopAuth()
    {
        $this->auth();
        if ($this->shopId <= 0) {
            throw new \think\exception\HttpResponseException(json(['code' => 403, 'msg' => '请先入驻商家'])->respond());
        }
        return $this->shopId;
    }

    protected function adminAuth()
    {
        $token = $this->request->header('Admin-Token', '');
        if (empty($token)) {
            throw new \think\exception\HttpResponseException(json(['code' => 401, 'msg' => '请先登录'])->respond());
        }

        $auth = \app\common\library\AdminToken::get($token);
        if (!$auth) {
            throw new \think\exception\HttpResponseException(json(['code' => 401, 'msg' => '登录已过期'])->respond());
        }

        return $auth;
    }

    protected function json($code = 0, $msg = '', $data = null)
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => time(),
        ];
        if ($data !== null) {
            $result['data'] = $data;
        }
        return json($result);
    }

    protected function success($data = null, $msg = '操作成功')
    {
        return $this->json(0, $msg, $data);
    }

    protected function error($msg = '操作失败', $code = 1)
    {
        return $this->json($code, $msg);
    }

    protected function paginate($query, $page = 1, $limit = 15)
    {
        $page = max(1, intval($page));
        $limit = min(100, max(1, intval($limit)));
        
        $total = $query->count();
        $list = $query->page($page, $limit)->select();
        
        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }
}
