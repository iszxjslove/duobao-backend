<?php


namespace app\api\validate;


use think\Validate;

class UserBank extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'actual_name' => 'require|lt:30',
        'ifsc_code' => 'require',
        'account_number' => 'require|unique:bank',
        'country'    => 'require',
        'city' => 'require',
        'address' => 'require',
        'mobile_number' => 'require'
    ];

    /**
     * 提示消息
     */
    protected $message = [
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        parent::__construct($rules, $message, $field);
    }
}