<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\UserService;

class User extends ApiController
{
    protected UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function index() { return $this->parseList($this->service->list($this->param())); }
    public function save() { return $this->parse($this->service->create($this->post())); }
    public function update() { return $this->parse($this->service->update($this->id(), $this->post())); }
    public function delete() { return $this->parse($this->service->delete($this->id())); }
    public function options() { return $this->success($this->service->getOptions()); }
}