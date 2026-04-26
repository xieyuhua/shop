<?php
declare(strict_types=1);

namespace app\service;

use app\model\admin\FileModel;

class FileService extends Service
{
    protected string $model = 'file';

    public function upload(string $group = 'default'): array
    {
        $file = app()->request()->file('file');
        
        if (!$file) {
            return $this->error('请选择文件');
        }

        $rule = ['size' => 10 * 1024 * 1024, 'ext' => 'jpg,jpeg,png,gif,bmp,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z'];
        $validate = new \think\Validate(['file' => "fileSize:{$rule['size']}|fileExt:{$rule['ext']}"]);
        
        if (!$validate->check(['file' => $file])) {
            return $this->error($validate->getError());
        }

        try {
            $savename = \think\facade\Filesystem::putFile($group, $file);
            $filepath = '/storage/' . $savename;

            $fileModel = new FileModel();
            $fileModel->group = $group;
            $fileModel->filename = $file->getOriginalName();
            $fileModel->filepath = $filepath;
            $fileModel->filesize = $file->getSize();
            $fileModel->ext = $file->getOriginalExtension();
            $fileModel->mime = $file->getMime();
            $fileModel->save();

            return $this->success([
                'id' => $fileModel->id,
                'filepath' => $filepath,
                'filename' => $fileModel->filename,
                'filesize' => $fileModel->filesize,
                'ext' => $fileModel->ext,
            ], '上传成功');
        } catch (\Throwable $e) {
            return $this->error('上传失败: ' . $e->getMessage());
        }
    }

    public function list(array $params = []): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;
        $group = $params['group'] ?? '';

        $query = db('file')->order('id', 'desc');
        
        if ($group) {
            $query->where('group', $group);
        }

        return $this->paginate($query, $page, $limit);
    }

    public function delete(int $id): array
    {
        $file = $this->find($id);
        if (!$file) {
            return $this->error('文件不存在');
        }

        $filepath = app()->getRootPath() . 'public' . $file->filepath;
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        parent::delete($id);

        return $this->success(null, '删除成功');
    }
}