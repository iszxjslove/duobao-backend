<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\common\controller\Api;
use app\common\model\Test;
use fast\Random;
use gmars\nestedsets\NestedSets;
use think\exception\DbException;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['agreement'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    /**
     * 注册协议
     * @param string $name
     * @throws DbException
     */
    public function agreement($name = '')
    {
        if ($name) {
            $names = explode(',', $name);
            $articles = Article::all(['name' => ['in', $names], 'status' => 'normal']);
            foreach ($articles as $article) {
                if ($article->need_login && !$this->auth->isLogin()) {
                    $this->error('需要登录', '', 401);
                }
            }
            if (count($names) === 1) {
                $articles = $articles[0];
            }
            $this->success('', $articles);
        }
        $this->error();
    }
}
