<?php
declare(strict_types=1);

namespace app\api;

use think\exception\Handle;
use think\Response;
use Throwable;

class ApiExceptionHandle extends Handle
{
    protected $ignoreReport = [
        \think\exception\HttpException::class,
        \think\exception\ValidateException::class,
    ];

    public function render($request, Throwable $e): Response
    {
        $msg = $e->getMessage();
        $code = $e->getCode() ?: 500;

        if ($e instanceof \think\exception\ValidateException) {
            $msg = $msg ?: '数据验证失败';
            $code = 422;
        }

        if ($e instanceof \think\exception\HttpException) {
            $msg = $e->getMessage();
            $code = $e->getStatusCode();
        }

        $data = [
            'code' => $code,
            'msg' => $msg,
            'data' => null,
        ];

        $response = json($data, $code);
        $response->contentType($request->accept()['html'] ?? 'json');

        return $response;
    }

    public function report(Throwable $e): void
    {
        $data = [
            'msg' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        log('error', json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}