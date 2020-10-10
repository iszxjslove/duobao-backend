<?php

return [
    [
        //配置唯一标识
        'name'    => 'uid',
        //显示的标题
        'title'   => '融合通信用户名',
        //类型
        'type'    => 'string',
        //数据字典
        'content' => [
        ],
        //值
        'value'   => 'hotlove',
        //验证规则 
        'rule'    => 'required',
        //错误消息
        'msg'     => '',
        //提示消息
        'tip'     => '',
        //成功消息
        'ok'      => '',
        //扩展信息
        'extend'  => ''
    ],
    [
        'name'    => 'pwd',
        'title'   => '融合通信密码',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => 'a123123',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'sign',
        'title'   => '短信签名',
        'type'    => 'string',
        'content' =>
            array(),
        'value'   => '【Treasuer Store】',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '例如【融合通信】',
        'ok'      => '',
        'extend'  => '',
    ]
];
