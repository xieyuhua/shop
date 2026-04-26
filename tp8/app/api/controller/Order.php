<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\OrderService;

class Order extends ApiController
{
    protected OrderService $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    public function index() { return $this->parseList($this->service->list($this->param())); }
    public function detail() { return $this->parse($this->service->detail($this->id())); }
    public function ship() { return $this->parse($this->service->ship($this->id(), $this->post('express_company'), $this->post('express_no'))); }
    public function cancel() { return $this->parse($this->service->cancel($this->id())); }
    public function refund() { return $this->parse($this->service->refund($this->id())); }
}