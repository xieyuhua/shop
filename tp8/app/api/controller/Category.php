<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\CategoryService;

class Category extends ApiController
{
    protected CategoryService $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    public function index() { return $this->parseList($this->service->list($this->param())); }
    public function tree() { return $this->success($this->service->tree()); }
    public function save() { return $this->parse($this->service->create($this->post())); }
    public function update() { return $this->parse($this->service->update($this->id(), $this->post())); }
    public function delete() { return $this->parse($this->service->delete($this->id())); }
    public function options() { return $this->success($this->service->getOptions()); }
}