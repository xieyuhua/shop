<?php
declare(strict_types=1);

namespace app\controller\admin;

class Index extends AdminBase
{
    public function index()
    {
        return view('', ['title' => '管理后台']);
    }

    public function dashboard()
    {
        $stats = [
            'today_order' => 0,
            'today_sales' => 0,
            'today_user' => 0,
            'today_product' => 0,
        ];
        return view('', ['title' => '控制台', 'stats' => $stats]);
    }
}