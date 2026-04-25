<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class NotifyService
{
    const TYPE_SYSTEM = 1;
    const TYPE_ORDER = 2;
    const TYPE_USER = 3;
    const TYPE_PRODUCT = 4;

    public static function send(int $userId, string $title, string $content, int $type = self::TYPE_SYSTEM): int
    {
        return Db::name('notify')->insertGetId([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'is_read' => 0,
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }

    public static function sendToAll(string $title, string $content, int $type = self::TYPE_SYSTEM): int
    {
        $users = Db::name('user')->where('status', 1)->column('id');
        
        $data = [];
        $time = date('Y-m-d H:i:s');
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
        
        return Db::name('notify')->insertAll($data);
    }

    public static function sendToAdmin(string $title, string $content, int $type = self::TYPE_SYSTEM): int
    {
        return Db::name('notify')->insertGetId([
            'user_id' => 0,
            'admin_id' => 1,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'is_read' => 0,
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }

    public static function getList(int $userId, int $page = 1, int $limit = 15): array
    {
        $query = Db::name('notify')
            ->where('user_id', $userId)
            ->order('id', 'desc');
        
        $list = $query->page($page, $limit)->select();
        $total = $query->count();
        
        return ['list' => $list, 'total' => $total];
    }

    public static function getUnreadCount(int $userId): int
    {
        return Db::name('notify')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->count();
    }

    public static function read(int $id): bool
    {
        return Db::name('notify')->where('id', $id)->update(['is_read' => 1, 'read_time' => date('Y-m-d H:i:s')]) !== false;
    }

    public static function readAll(int $userId): int
    {
        return Db::name('notify')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1, 'read_time' => date('Y-m-d H:i:s')]);
    }

    public static function delete(int $id): bool
    {
        return Db::name('notify')->delete($id) > 0;
    }

    public static function orderNotify(int $userId, string $orderNo, string $status): void
    {
        $title = '订单通知';
        $statusMap = [
            0 => '订单已创建，请尽快支付',
            1 => '订单已支付，等待发货',
            2 => '订单已发货，注意查收',
            3 => '订单已完成',
            4 => '订单已取消',
            5 => '订单已退款'
        ];
        
        $content = "订单号: {$orderNo}, {$statusMap[$status]}";
        
        self::send($userId, $title, $content, self::TYPE_ORDER);
    }
}