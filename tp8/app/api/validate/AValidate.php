<?php
declare(strict_types=1);

namespace app\api\validate;

use think\Validate;

abstract class AValidate extends Validate
{
    public function checkScene(array $data, string $scene = ''): bool
    {
        if ($scene && isset($this->scene[$scene])) {
            $sceneRules = $this->scene[$scene];
            $rules = [];
            foreach ($sceneRules as $rule) {
                if (isset($this->rule[$rule])) {
                    $rules[$rule] = $this->rule[$rule];
                }
            }
            $this->rule = $rules;
        }
        
        return $this->check($data);
    }

    protected function getError(): string
    {
        $errors = $this->getErrorList();
        return $errors ? current($errors) : '';
    }
}