<?php
declare(strict_types=1);

namespace app\api\controller;

use think\facade\Db;

class User extends ApiController
{
    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        $keyword = $this->request->param('keyword', '');

        $where = [];
        if ($keyword) {
            $where[] = ['username|mobile|email', 'like', "%{$keyword}%"];
        }

        $list = Db::name('user')
            ->where($where)
            ->order('id', 'desc')
            ->page($page, $limit)
            ->select();

        $total = Db::name('user')->where($where)->count();

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

        if (Db::name('user')->where('mobile', $data['mobile'])->find()) {
            return $this->error('手机号已存在');
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['create_time'] = date('Y-m-d H:i:s');

        $id = Db::name('user')->insertGetId($data);
        
        return $this->success(['id' => $id], '添加成功');
    }

    public function update()
    {
        $id = $this->request->post('id');
        $data = $this->request->post();

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        if (isset($data['id'])) {
            unset($data['id']);
        }

        $result = Db::name('user')->where('id', $id)->update($data);
        
        return $result !== false ? $this->success(null, '更新成功') : $this->error('更新失败');
    }

    public function delete()
    {
        $id = $this->request->param('id');
        
        if (!$id) {
            return $this->error('参数错误');
        }
        
        $result = Db::name('user')->delete($id);
        
        return $result ? $this->success(null, '删除成功') : $this->error('删除失败');
    }
}