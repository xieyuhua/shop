<?php
declare(strict_types=1);

namespace app\model\admin;

use think\Model;

/**
 * @property int $id
 * @property string $group
 * @property string $filename
 * @property string $filepath
 * @property int $filesize
 * @property string|null $filetype
 * @property string|null $ext
 * @property string|null $mime
 * @property int $use_num
 * @property int $status
 * @property string $create_time
 */
class FileModel extends Model
{
    protected $name = 'file';
    protected $pk = 'id';
    protected $createTime = 'create_time';

    public static function upload(string $group = 'default'): array
    {
        $file = request()->file('file');
        
        if (!$file) {
            return ['code' => 400, 'msg' => '请选择文件'];
        }

        $validate = ['size' => 1024 * 1024 * 10, 'ext' => 'jpg,jpeg,png,gif,bmp,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z'];
        
        $info = \think\facade\Filesystem::putFile($group, $file, function ($file) use ($validate) {
            $rule = [
                'size' => $validate['size'],
                'type' => explode(',', $validate['ext'])
            ];
            
            return [
                'uuid' => uuid(),
                'ext' => $file->getOriginalExtension()
            ];
        });

        $filepath = '/storage/' . $info;
        
        return [
            'code' => 200,
            'msg' => '上传成功',
            'data' => [
                'filepath' => $filepath,
                'filename' => $file->getOriginalName(),
                'filesize' => $file->getSize(),
                'ext' => $file->getOriginalExtension()
            ]
        ];
    }

    public function getSizeFormatAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->filesize;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}