<?php


//$colorArr = [
//    "primary", "success", "danger",
//    "warning", "info", "gray", "red",
//    "yellow", "aqua", "blue", "navy",
//    "teal", "olive", "lime", "fuchsia",
//    "purple", "maroon"
//];


return [
    'fa_recharge_order' => [
        'field' => [
            'status' => [
                'default' => [0, 'primary', '待支付'],
                'success' => [1, 'success', '成功'],
                'fail'    => [2, 'danger', '失败'],
            ]
        ]
    ],
    'fa_withdraw_order' => [
        'field' => [
            'status' => [
                'default' => [0, 'primary', '待审核'],
                'success' => [1, 'success', '成功'],
                'fail'    => [2, 'danger', '失败'],
            ]
        ]
    ],
    'fa_projects'       => [
        'field' => [
            'prizestatus' => [
                'default' => [0, 'primary', '未派'],
                'success' => [1, 'success', '已派']
            ],
            'isgetprize'  => [
                'default' => [0, 'primary', '未派'],
                'right'   => [1, 'success', '中奖'],
                'miss'    => [2, 'gray', '未中奖'],
            ]
        ]
    ]
];