<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\StatisticsService;

class Statistics extends ApiController
{
    protected StatisticsService $service;

    public function __construct()
    {
        $this->service = new StatisticsService();
    }

    public function index() { return $this->success($this->service->index($this->param('type', 'today'))); }
    public function chart() { return $this->success($this->service->chart((int)$this->param('days', 7))); }
}