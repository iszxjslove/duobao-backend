<?php

namespace app\admin\validate;

use think\Validate;

class RedEnvelopes extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'amount' => 'require|number|min:1',
        'number' => 'require|number|min:1',
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => []
    ];
    
}
