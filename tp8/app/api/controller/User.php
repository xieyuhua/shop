<?php
declare(strict_types=1);

namespace app\api\controller;

use app\model\admin\UserModel;

class User extends ApiController
{
    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        $keyword = $this->request->param('keyword', '');

        $query = UserModel::order('id', 'desc');
        
        if ($keyword) {
            $query->where('username|mobile|email|nickname', 'like', "%{$keyword}%");
        }

        $list = $query->page($page, $limit)->select();
        $total = $query->count();

        return $this->success([
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);
    }

    public function save()
    {
        $data = $this->request->post();
        
        if (empty($data['mobile']) || empty($data['password'])) {
            return $this->error('手机号和密码不能为空');
        }

        if (UserModel::where('mobile', $data['mobile'])->find()) {
            return $this->error('手机号已存在');
        }

        $user = new UserModel();
        $user->save($data);
        
        return $this->success(['id' => $user->id], '添加成功');
    }

    public function update()
    {
        $id = $this->request->post('id');
        $data = $this->request->post();

        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        unset($data['id']);

        $user = UserModel::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }

        $user->save($data);
        
        return $this->success(null, '更新成功');
    }

    public function delete()
    {
        $id = $this->request->param('id');
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $user = UserModel::find($id);
        if (!$user) {
            return $this->error('用户不存在');
        }

        $user->delete();
        
        return $this->success(null, '删除成功');
    }
}