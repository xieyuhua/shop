<?php
declare(strict_types=1);

namespace app\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    public static function export(array $data, array $config = []): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $title = $config['title'] ?? 'Export';
        $headers = $config['headers'] ?? [];
        $fields = $config['fields'] ?? [];
        
        $sheet->setTitle($title);
        
        $col = 'A';
        $row = 1;
        
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $col++;
        }
        
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($fields as $field) {
                $value = is_callable($field) ? $field($item) : ($item[$field] ?? '');
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        foreach (range('A', $col) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        
        $filename = $title . '_' . date('YmdHis') . '.xlsx';
        $filepath = runtime_path('exports/' . $filename);
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return $filepath;
    }

    public static function exportCsv(array $data, array $config = []): string
    {
        $title = $config['title'] ?? 'Export';
        $headers = $config['headers'] ?? [];
        $fields = $config['fields'] ?? [];
        
        $content = '';
        
        if ($headers) {
            $content .= implode(',', $headers) . "\n";
        }
        
        foreach ($data as $item) {
            $row = [];
            foreach ($fields as $field) {
                $value = is_callable($field) ? $field($item) : ($item[$field] ?? '');
                $value = str_replace([",", "\n", "\r"], ' ', $value);
                $row[] = $value;
            }
            $content .= implode(',', $row) . "\n";
        }
        
        $filename = $title . '_' . date('YmdHis') . '.csv';
        $filepath = runtime_path('exports/' . $filename);
        
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($filepath, $content);
        
        return $filepath;
    }

    public static function exportOrder(array $orders): string
    {
        $headers = ['订单号', '收货人', '联系电话', '商品金额', '实付金额', '状态', '下单时间'];
        $fields = [
            'order_no',
            'receiver_name',
            'receiver_mobile',
            'total_amount',
            'pay_amount',
            'status' => function($item) {
                $map = [0 => '待付款', 1 => '待发货', 2 => '待收货', 3 => '已完成', 4 => '已取消', 5 => '已退款'];
                return $map[$item['status']] ?? '';
            },
            'create_time'
        ];
        
        return self::export($orders, ['title' => '订单数据', 'headers' => $headers, 'fields' => $fields]);
    }

    public static function exportProduct(array $products): string
    {
        $headers = ['商品名称', '分类', '价格', '原价', '库存', '销量', '状态'];
        $fields = [
            'name',
            'category_id',
            'price',
            'original_price',
            'stock',
            'sales',
            'status' => function($item) {
                return $item['status'] ? '上架' : '下架';
            }
        ];
        
        return self::export($products, ['title' => '商品数据', 'headers' => $headers, 'fields' => $fields]);
    }

    public static function exportUser(array $users): string
    {
        $headers = ['手机号', '昵称', '余额', '积分', '状态', '注册时间'];
        $fields = [
            'mobile',
            'nickname',
            'balance',
            'points',
            'status' => function($item) {
                return $item['status'] ? '正常' : '禁用';
            },
            'create_time'
        ];
        
        return self::export($users, ['title' => '会员数据', 'headers' => $headers, 'fields' => $fields]);
    }
}