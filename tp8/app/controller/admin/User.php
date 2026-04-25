<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\admin\UserService;

class User extends AdminBase
{
    protected UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        $keyword = $this->request->param('keyword', '');

        $list = $this->service->getList($page, $limit, $keyword);
        
        return view('', ['title' => '会员管理', 'list' => $list]);
    }

    public function add()
    {
        return view('', ['title' => '添加会员']);
    }

    public function save()
    {
        $data = $this->request->post();
        $result = $this->service->create($data);
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }
        
        return $this->success('添加成功');
    }

    public function edit()
    {
        $id = $this->request->param('id');
        $info = $this->service->getInfo($id);
        
        return view('', ['title' => '编辑会员', 'info' => $info]);
    }

    public function update()
    {
        $id = $this->request->post('id');
        $data = $this->request->post();
        
        $result = $this->service->update($id, $data);
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }
        
        return $this->success('更新成功');
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $result = $this->service->delete($id);
        
        return $this->success('删除成功');
    }

    public function status()
    {
        $id = $this->request->param('id');
        $status = $this->request->param('status', 1);
        $result = $this->service->setStatus($id, (int)$status);
        
        return $this->success('操作成功');
    }
}