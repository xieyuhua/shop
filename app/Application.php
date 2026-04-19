<?php

namespace app;

use think\App as BaseApp;
use think\facade\Hook;

class App extends BaseApp
{
    protected $middlewarePriority = [];

    public function run()
    {
        $this->console->setDispatchCallback(function () {
            return $this->http->run();
        });

        if ($this->runningInConsole()) {
            $this->http->run();
        } else {
            Hook::listen('app_begin');
            $response = $this->http->run();
            $response->send();
            Hook::listen('app_end', $response);
        }

        return $response;
    }
}
