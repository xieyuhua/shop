<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\entity\AdminCategoryEntity;

/**
 * 后台分类管理控制器 - 仅接收参数和返回结果
 */
class CategoryController extends BaseController
{
    private AdminCategoryEntity $categoryEntity;

    public function __construct()
    {
        parent::__construct();
        $this->categoryEntity = new AdminCategoryEntity();
    }

    /**
     * 分类列表
     */
    public function list(): \think\Response
    {
        $this->adminAuth();

        $list = $this->categoryEntity->getList();

        return $this->success($list);
    }

    /**
     * 分类树
     */
    public function tree(): \think\Response
    {
        $this->adminAuth();

        $list = $this->categoryEntity->getTree();

        return $this->success($list);
    }

    /**
     * 分类选项（用于下拉选择）
     */
    public function options(): \think\Response
    {
        $this->adminAuth();

        $list = $this->categoryEntity->getOptions();

        return $this->success($list);
    }

    /**
     * 分类详情
     */
    public function detail(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->get('id', 0);

        $category = $this->categoryEntity->getDetail($id);

        if (!$category) {
            return $this->error('分类不存在');
        }

        return $this->success($category);
    }

    /**
     * 创建分类
     */
    public function create(): \think\Response
    {
        $this->adminAuth();

        $data = $this->request->post();

        $result = $this->categoryEntity->create($data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 更新分类
     */
    public function update(): \think\Response
    {
        $this->adminAuth();

        $data = $this->request->post();

        $result = $this->categoryEntity->update($data);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success($result['data']);
    }

    /**
     * 删除分类
     */
    public function delete(): \think\Response
    {
        $this->adminAuth();

        $id = (int) $this->request->post('id', 0);

        $result = $this->categoryEntity->delete($id);

        if (!$result['success']) {
            return $this->error($result['msg']);
        }

        return $this->success();
    }
}
