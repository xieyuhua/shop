<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\entity\DashboardEntity;

/**
 * 后台仪表盘控制器 - 仅接收参数和返回结果
 */
class Dashboard extends BaseController
{
    private DashboardEntity $dashboardEntity;

    public function __construct()
    {
        parent::__construct();
        $this->dashboardEntity = new DashboardEntity();
    }

    /**
     * 获取统计数据
     */
    public function statistics(): \think\Response
    {
        $this->adminAuth();

        $result = $this->dashboardEntity->getStatistics();

        return $this->success($result);
    }

    /**
     * 快捷操作统计
     */
    public function quickStats(): \think\Response
    {
        $this->adminAuth();

        $result = $this->dashboardEntity->getQuickStats();

        return $this->success($result);
    }
}
