<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\admin\CategoryService;

class Category extends AdminBase
{
    protected CategoryService $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    public function index()
    {
        $list = $this->service->getTreeList();
        
        return view('', ['title' => '商品分类', 'list' => $list]);
    }

    public function add()
    {
        return view('', ['title' => '添加分类']);
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
        
        return view('', ['title' => '编辑分类']);
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
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }
        
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