<?php
declare(strict_types=1);

namespace app\api\controller;

use app\service\DictService;

class Dict extends ApiController
{
    public function index()
    {
        $group = $this->request->param('group', '');
        
        if ($group) {
            $options = DictService::getOptions($group);
        } else {
            $options = [];
        }
        
        return $this->success($options);
    }
}