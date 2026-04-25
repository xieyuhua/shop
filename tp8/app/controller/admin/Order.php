<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\admin\OrderService;

class Order extends AdminBase
{
    protected OrderService $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        
        $filter = [
            'keyword' => $this->request->param('keyword', ''),
            'status' => $this->request->param('status', ''),
            'date_range' => $this->request->param('date_range', ''),
        ];
        
        $list = $this->service->getList($page, $limit, $filter);
        
        return view('', ['title' => '订单管理', 'list' => $list]);
    }

    public function detail()
    {
        $id = $this->request->param('id');
        $result = $this->service->getInfo($id);
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }
        
        return view('', ['title' => '订单详情', 'order' => $result['data']['order'], 'items' => $result['data']['items']]);
    }

    public function ship()
    {
        $id = $this->request->post('id');
        $expressCompany = $this->request->post('express_company');
        $expressNo = $this->request->post('express_no');
        
        $result = $this->service->ship($id, $expressCompany, $expressNo);
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }
        
        return $this->success('发货成功');
    }

    public function cancel()
    {
        $id = $this->request->param('id');
        $result = $this->service->cancel($id);
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }
        
        return $this->success('取消成功');
    }

    public function refund()
    {
        $id = $this->request->param('id');
        $result = $this->service->refund($id);
        
        if ($result['code'] !== 200) {
            return $this->error($result['msg']);
        }
        
        return $this->success('退款成功');
    }

    public function complete()
    {
        $id = $this->request->param('id');
        $result = $this->service->complete($id);
        
        return $this->success('操作成功');
    }
}