<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\admin\ProductService;

class Product extends AdminBase
{
    protected ProductService $service;

    public function __construct()
    {
        $this->service = new ProductService();
    }

    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        
        $filter = [
            'category_id' => $this->request->param('category_id', 0),
            'keyword' => $this->request->param('keyword', ''),
            'status' => $this->request->param('status', ''),
        ];
        
        $list = $this->service->getList($page, $limit, $filter);
        
        return view('', ['title' => '商品管理', 'list' => $list]);
    }

    public function add()
    {
        return view('', ['title' => '添加商品']);
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
        
        return view('', ['title' => '编辑商品', 'info' => $info]);
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

    public function recommend()
    {
        $id = $this->request->param('id');
        $isRecommend = $this->request->param('is_recommend', 1);
        $result = $this->service->setRecommend($id, (int)$isRecommend);
        
        return $this->success('操作成功');
    }
}