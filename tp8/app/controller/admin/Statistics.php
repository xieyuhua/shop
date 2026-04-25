<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\service\admin\StatisticsService;

class Statistics extends AdminBase
{
    protected StatisticsService $service;

    public function __construct()
    {
        $this->service = new StatisticsService();
    }

    public function index()
    {
        $type = $this->request->param('type', 'today');
        $stats = $this->service->getStatistics($type);
        
        return view('', ['title' => '数据统计', 'stats' => $stats, 'type' => $type]);
    }

    public function chart()
    {
        $days = $this->request->param('days', 7);
        $data = $this->service->getChartData((int)$days);
        
        return json(['code' => 200, 'data' => $data]);
    }

    public function export()
    {
        $data = $this->service->export();
        
        return json(['code' => 200, 'data' => $data]);
    }
}