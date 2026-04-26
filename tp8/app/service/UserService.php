<?php
declare(strict_types=1);

namespace app\service;

use app\model\admin\UserModel;
use app\model\admin\UserModel as Model;

class UserService extends Service
{
    protected string $model = 'user';

    public function list(array $params = []): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        $keyword = $params['keyword'] ?? '';

        $query = db('user')->order('id', 'desc');

        if ($keyword) {
            $query->where('username|mobile|email|nickname', 'like', "%{$keyword}%");
        }

        return $this->paginate($query, $page, $limit);
    }

    public function create(array $data): array
    {
        if (empty($data['mobile']) || empty($data['password'])) {
            return $this->error('手机号和密码不能为空');
        }

        if ($this->exists(['mobile' => $data['mobile']])) {
            return $this->error('手机号已存在');
        }

        $user = new UserModel();
        $user->save($data);

        return $this->success(['id' => $user->id], '添加成功');
    }

    public function update(int $id, array $data): array
    {
        try {
            $user = $this->findOrFail($id);
        } catch (\RuntimeException) {
            return $this->error('用户不存在');
        }

        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        unset($data['id']);

        $user->save($data);

        return $this->success(null, '更新成功');
    }

    public function delete(int $id): array
    {
        if (!$this->find($id)) {
            return $this->error('用户不存在');
        }

        $this->delete($id);

        return $this->success(null, '删除成功');
    }

    public function getOptions(): array
    {
        return db('user')->field('id, username, nickname, mobile')->select()->toArray();
    }
}