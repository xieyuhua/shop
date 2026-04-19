<?php

declare(strict_types=1);

namespace app\api\controller;

use think\Request;

class Validate
{
    protected $rules = [];
    protected $messages = [];
    protected $scene = '';

    public function __construct()
    {
        $this->messages = [
            'require' => ':attribute不能为空',
            'number' => ':attribute必须是数字',
            'integer' => ':attribute必须是整数',
            'float' => ':attribute必须是浮点数',
            'boolean' => ':attribute必须是布尔值',
            'email' => ':attribute格式不正确',
            'mobile' => ':attribute格式不正确',
            'url' => ':attribute格式不正确',
            'ip' => ':attribute格式不正确',
            'date' => ':attribute不是有效的时间格式',
            'alpha' => ':attribute只能是字母',
            'alphaNum' => ':attribute只能是字母和数字',
            'alphaDash' => ':attribute只能是字母、数字和下划线',
            'in' => ':attribute必须在范围内',
            'notIn' => ':attribute不能在范围内',
            'between' => ':attribute必须在:value1和:value2之间',
            'notBetween' => ':attribute不能在:value1和:value2之间',
            'length' => ':attribute长度不符合要求',
            'min' => ':attribute长度不能小于:value',
            'max' => ':attribute长度不能大于:value',
            'regex' => ':attribute格式不正确',
        ];
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    public function setMessages(array $messages): self
    {
        $this->messages = array_merge($this->messages, $messages);
        return $this;
    }

    public function scene(string $scene): self
    {
        $this->scene = $scene;
        return $this;
    }

    public function check(array $data): bool
    {
        $rules = $this->getRules();

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $ruleList = is_string($rule) ? explode('|', $rule) : $rule;

            foreach ($ruleList as $r) {
                if (is_numeric($r)) continue;
                
                $result = $this->checkRule($field, $value, $r, $data);
                if ($result !== true) {
                    throw new \InvalidArgumentException($result);
                }
            }
        }

        return true;
    }

    protected function getRules(): array
    {
        if (empty($this->scene)) {
            return $this->rules;
        }

        if (isset($this->rules[$this->scene])) {
            $sceneRules = $this->rules[$this->scene];
            if (is_array($sceneRules)) {
                return array_intersect_key($this->rules, array_flip($sceneRules));
            }
            return $this->rules;
        }

        return $this->rules;
    }

    protected function checkRule($field, $value, $rule, $data): bool|string
    {
        $ruleName = $rule;
        $ruleParam = null;

        if (strpos($rule, ':') !== false) {
            list($ruleName, $ruleParam) = explode(':', $rule, 2);
        }

        $message = $this->messages[$ruleName] ?? ':attribute验证失败';
        $message = str_replace(':attribute', $field, $message);
        $message = str_replace(':value', $ruleParam ?? '', $message);

        switch ($ruleName) {
            case 'require':
                if (empty($value) && $value !== '0' && $value !== 0) {
                    return $message;
                }
                break;

            case 'number':
                if (!is_numeric($value)) {
                    return $message;
                }
                break;

            case 'integer':
                if (!is_int($value) && !ctype_digit(strval($value))) {
                    return $message;
                }
                break;

            case 'float':
                if (!is_float($value) && !is_numeric($value)) {
                    return $message;
                }
                break;

            case 'boolean':
                if (!in_array($value, [0, 1, '0', '1', true, false], true)) {
                    return $message;
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $message;
                }
                break;

            case 'mobile':
                if (!preg_match('/^1[3-9]\d{9}$/', $value)) {
                    return $message;
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return $message;
                }
                break;

            case 'ip':
                if (!filter_var($value, FILTER_VALIDATE_IP)) {
                    return $message;
                }
                break;

            case 'alpha':
                if (!preg_match('/^[a-zA-Z]+$/', $value)) {
                    return $message;
                }
                break;

            case 'alphaNum':
                if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
                    return $message;
                }
                break;

            case 'alphaDash':
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
                    return $message;
                }
                break;

            case 'in':
                $values = explode(',', $ruleParam);
                if (!in_array($value, $values)) {
                    return $message;
                }
                break;

            case 'notIn':
                $values = explode(',', $ruleParam);
                if (in_array($value, $values)) {
                    return $message;
                }
                break;

            case 'between':
                list($min, $max) = explode(',', $ruleParam);
                $value = floatval($value);
                if ($value < floatval($min) || $value > floatval($max)) {
                    return $message;
                }
                break;

            case 'length':
                if (is_string($value) && strlen($value) != intval($ruleParam)) {
                    return $message;
                }
                break;

            case 'min':
                if (is_string($value) && strlen($value) < intval($ruleParam)) {
                    return $message;
                }
                if (is_numeric($value) && floatval($value) < floatval($ruleParam)) {
                    return $message;
                }
                break;

            case 'max':
                if (is_string($value) && strlen($value) > intval($ruleParam)) {
                    return $message;
                }
                if (is_numeric($value) && floatval($value) > floatval($ruleParam)) {
                    return $message;
                }
                break;

            case 'regex':
                if (!preg_match('/' . $ruleParam . '/', $value)) {
                    return $message;
                }
                break;
        }

        return true;
    }
}
