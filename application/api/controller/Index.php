<?php

namespace app\api\controller;

use app\admin\model\Article;
use app\admin\model\RedEnvelopes;
use app\common\controller\Api;
use app\common\model\IssueSales;
use app\common\model\Test;
use app\common\model\UserMission;
use app\common\model\UserMissionLog;
use Endroid\QrCode\QrCode;
use fast\Random;
use Firebase\JWT\JWT;
use gmars\nestedsets\NestedSets;
use think\Config;
use think\Db;
use think\exception\DbException;
use think\Hook;
use think\Request;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['index','agreement'];
    protected $noNeedRight = ['*'];


    /**
     * 首页
     *
     */
    public function index($uid = 8)
    {
        $issueSales = IssueSales::get(['issue_id' => 6730]);
        if ($issueSales) {
            $numbers = [];
            for ($i = 0; $i < 10; $i++) {
                $numbers[$i] = $issueSales["EE{$i}"] ?? 0.00;
            }
            arsort($numbers);
            dump($numbers);
            $keys = array_flip($numbers);
            dump($keys);
            $singular = end($keys);
            $code = substr(date('Ymd') / 17658, -3) . random_int(10, 99) . $singular;
            dump($code);
        }
//        Hook::listen('user_login_successed', $user);
//        if(!$user){
//            $this->error('用户不存在');
//        }
//        $parents = (new \Nested($user))->getParent($user->id, 2);
//        if(!$parents){
//            return false;
//        }
//////        dump(array_column($parents, 'id'));exit;
//        $teams = [$user->id => $user->toArray()];
//        foreach ($parents as $parent) {
//            $teams[$parent['id']] = $parent;
//        }
//        $ids = array_column($teams, 'id');
////
//        $missions = UserMission::all(['user_id' => ['in',$ids], 'mission_name' => ['in',['login','sublogin']], 'status' => ['<', 3]]);
////
//        $logs = [];
//        foreach ($missions as $mission) {
//            $ip =  Request::instance()->ip();
//            if($mission['mission_name'] === 'login' && $mission['user_id'] === $user->id){
//                // 自己的login 登录任务 （排队父级的）
//                $logs[] = [
//                    'content' => json_encode(['ip' => $ip]),
//                    'user_mission_id' => $mission['id']
//                ];
//            }elseif($mission['mission_name'] === 'sublogin' && in_array('id', array_column($parents, 'id'), true)){
//                // 父级的 sublogin 下级登录任务 （排除自己的）
//                $logs[] = [
//                    'content' => json_encode(['ip' => $ip, 'user_id'=>$user->id ,'username'=>$user->username, 'nickname'=>$user->nickname]),
//                    'user_mission_id' => $mission['id']
//                ];
//            }
//
//        }
//        if($logs) {
//            UserMissionLog::saveAll($logs);
//        }
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
