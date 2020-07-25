<?php


namespace app\common\library\mission;

use app\common\library\Mission;
use app\common\model\User;

class Login extends Mission
{
    protected $names = [
        'private' => ['login'],
        'parent'  => ['sublogin']
    ];
}