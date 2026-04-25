<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class QueueService
{
    const STATUS_PENDING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILED = 3;

    public static function push(string $job, array $data, int $delay = 0): int
    {
        return Db::name('queue')->insertGetId([
            'job' => $job,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'status' => self::STATUS_PENDING,
            'attempts' => 0,
            'delay' => $delay,
            'available_at' => date('Y-m-d H:i:s', time() + $delay),
            'create_time' => date('Y-m-d H:i:s')
        ]);
    }

    public static function pop(int $limit = 10): array
    {
        return Db::name('queue')
            ->where('status', self::STATUS_PENDING)
            ->where('available_at', '<=', date('Y-m-d H:i:s'))
            ->limit($limit)
            ->select()
            ->toArray();
    }

    public static function reserve(int $id): bool
    {
        return Db::name('queue')->where('id', $id)->update([
            'status' => self::STATUS_RUNNING,
            'start_time' => date('Y-m-d H:i:s'),
            'attempts' => Db::raw('attempts+1')
        ]) !== false;
    }

    public static function success(int $id, string $result = ''): bool
    {
        return Db::name('queue')->where('id', $id)->update([
            'status' => self::STATUS_SUCCESS,
            'result' => $result,
            'finish_time' => date('Y-m-d H:i:s')
        ]) !== false;
    }

    public static function failed(int $id, string $error = ''): bool
    {
        return Db::name('queue')->where('id', $id)->update([
            'status' => self::STATUS_FAILED,
            'result' => $error,
            'finish_time' => date('Y-m-d H:i:s')
        ]) !== false;
    }

    public static function release(int $id): bool
    {
        $queue = Db::name('queue')->find($id);
        
        if (!$queue || $queue['attempts'] >= 3) {
            return false;
        }
        
        return Db::name('queue')->where('id', $id)->update([
            'status' => self::STATUS_PENDING,
            'available_at' => date('Y-m-d H:i:s', time() + 60)
        ]) !== false;
    }

    public static function clean(int $days = 7): int
    {
        return Db::name('queue')
            ->where('status', 'in', [self::STATUS_SUCCESS, self::STATUS_FAILED])
            ->where('create_time', '<', date('Y-m-d H:i:s', time() - $days * 86400))
            ->delete();
    }

    public static function run(): void
    {
        $jobs = self::pop(10);
        
        foreach ($jobs as $job) {
            self::execute($job);
        }
    }

    private static function execute(array $job): void
    {
        self::reserve($job['id']);
        
        try {
            $data = json_decode($job['data'], true);
            $result = self::dispatch($job['job'], $data);
            
            if ($result === false) {
                self::release($job['id']);
            } else {
                self::success($job['id'], is_array($result) ? json_encode($result) : (string)$result);
            }
        } catch (\Exception $e) {
            self::failed($job['id'], $e->getMessage());
        }
    }

    private static function dispatch(string $job, array $data)
    {
        $map = [
            'sendSms' => [SmsService::class, 'send'],
            'sendEmail' => [EmailService::class, 'send'],
            'syncProduct' => [ProductService::class, 'sync'],
            'exportData' => [ExportService::class, 'exportOrder'],
        ];
        
        if (isset($map[$job])) {
            [$class, $method] = $map[$job];
            return $class::$method(...$data);
        }
        
        return false;
    }
}