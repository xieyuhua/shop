<?php
declare(strict_types=1);

namespace app\api\controller;

use think\exception\ValidateException;
use app\model\admin\FileModel;

class File extends ApiController
{
    public function upload()
    {
        $group = $this->request->param('group', 'default');
        
        try {
            $file = $this->request->file('file');
            
            if (!$file) {
                return $this->error('请选择文件');
            }

            $rule = ['size' => 10 * 1024 * 1024, 'ext' => 'jpg,jpeg,png,gif,bmp,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z'];
            
            $validate = new \think\Validate(['file' => 'fileSize:' . $rule['size'] . '|fileExt:' . $rule['ext']]);
            $result = $validate->check(['file' => $file]);
            
            if (!$result) {
                return $this->error($validate->getError());
            }

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

    public function index()
    {
        $page = $this->request->param('page', 1);
        $limit = $this->request->param('limit', 15);
        $group = $this->request->param('group', '');

        $query = FileModel::order('id', 'desc');
        
        if ($group) {
            $query->where('group', $group);
        }

        $list = $query->page($page, $limit)->select();
        $total = $query->count();

        return $this->success([
            'list' => $list,
            'total' => $total,
        ]);
    }

    public function delete()
    {
        $id = $this->request->param('id');
        
        if (!$id) {
            return $this->error('参数错误');
        }

        $file = FileModel::find($id);
        if (!$file) {
            return $this->error('文件不存在');
        }

        $filepath = app()->getRootPath() . 'public' . $file->filepath;
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $file->delete();
        
        return $this->success(null, '删除成功');
    }
}