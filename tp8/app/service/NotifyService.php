<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class NotifyService extends Service
{
    protected string $model = 'notify';

    const TYPE_SYSTEM = 1;
    const TYPE_ORDER = 2;
    const TYPE_USER = 3;
    const TYPE_PRODUCT = 4;

    public function send(int $userId, string $title, string $content, int $type = self::TYPE_SYSTEM): int
    {
        return db($this->model)->insertGetId([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'is_read' => 0,
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function sendToAll(string $title, string $content, int $type = self::TYPE_SYSTEM): int
    {
        $users = db('user')->where('status', 1)->column('id');
        $time = date('Y-m-d H:i:s');
        
        $data = [];
        foreach ($users as $userId) {
            $data[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'content' => $content,
                'is_read' => 0,
                'create_time' => $time
            ];
        }
        
        return db($this->model)->insertAll($data);
    }

    public function sendToAdmin(string $title, string $content, int $type = self::TYPE_SYSTEM): int
    {
        return db($this->model)->insertGetId([
            'user_id' => 0,
            'admin_id' => 1,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'is_read' => 0,
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function getList(int $userId, int $page = 1, int $limit = 15): array
    {
        $query = db($this->model)->where('user_id', $userId)->order('id', 'desc');
        return $this->paginate($query, $page, $limit);
    }

    public function getUnreadCount(int $userId): int
    {
        return db($this->model)->where('user_id', $userId)->where('is_read', 0)->count();
    }

    public function read(int $id): bool
    {
        return db($this->model)->where('id', $id)->update([
            'is_read' => 1,
            'read_time' => date('Y-m-d H:i:s')
        ]) !== false;
    }

    public function readAll(int $userId): int
    {
        return db($this->model)->where('user_id', $userId)->where('is_read', 0)->update([
            'is_read' => 1,
            'read_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function orderNotify(int $userId, string $orderNo, int $status): void
    {
        $statusMap = [
            0 => '订单已创建，请尽快支付',
            1 => '订单已支付，等待发货',
            2 => '订单已发货，注意查收',
            3 => '订单已完成',
            4 => '订单已取消',
            5 => '订单已退款'
        ];
        
        $content = "订单号: {$orderNo}, {$statusMap[$status]}";
        
        $this->send($userId, '订单通知', $content, self::TYPE_ORDER);
    }
}