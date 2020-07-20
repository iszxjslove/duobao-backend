<?php


namespace app\api\controller;


use app\api\model\Issue;
use app\api\model\Game as GameModel;
use app\common\controller\Api;
use app\common\model\Projects;
use think\Db;
use think\Exception;
use app\common\model\User;

class Game extends Api
{

    public function play()
    {
        $gid = 1;
        $time = time();
        $issue = Issue::get(['game_id' => $gid, 'saleend' => ['>', $time]]);
        if (!$issue) {
            $this->error('There is no period to bet on');
        }
        $data = $issue->visible(['issue', 'salestart', 'saleend', 'earliestwritetime', 'canneldeadline'])->toArray();
        $data['server_time'] = $time;
        $this->success('', $data);
    }

    public function issues()
    {
        $gid = 1;
        $time = time();
        $limit = $this->request->request('limit', 10, 'int');
        $limit = $limit > 100 ? 100 : $limit;
        $page = $this->request->request('page', 1, 'int');
        $offset = ($page - 1) * $limit;
        $issueModel = new Issue();
        $where = [
            'game_id' => $gid,
            'saleend' => ['<', $time]
        ];
        $list = $issueModel
            ->where($where)
            ->field('issue,code,last_digits,colors,statuscode,statusbonus')
            ->limit($offset, $limit)
            ->order('id', 'desc')
            ->select();
        return json($list);
    }

    public function wager()
    {
        $gid = 1;
        $time = time();
        $money = $this->request->request('money', 0, 'int');
        $number = $this->request->request('number', 0, 'int');
        $issue = $this->request->request('issue', '', 'strip_tags');
        $selected = $this->request->request('selected', '', 'strip_tags');
        // 获取游戏及赔率
        $game = GameModel::get($gid);
        if (!$game || $game->status !== 'normal') {
            $this->error('Not open');
        }
        // 验证数据
        # 金额是否在允许的值
        $moneys = explode(',', $game->moneys);
        if (!in_array($money, $moneys, true)) {
            $this->error('Amount not allowed');
        }
        # 数量是否在允许的值
        if (!($number >= 1 && $number <= $game->max_hands)) {
            $this->error("Please select from 1 to {$game->max_hands}");
        }
        # 总金额
        $totalprice = $money * $number;
        # 奖期是否存在或能否下注
        $issueinfo = Issue::get(['game_id' => $gid, 'issue' => $issue]);
        if (!$issueinfo || $issueinfo->saleend < $time) {
            $this->error("Can't bet");
        }
        $colors = [
            'green'  => '1|3|5|7|9',
            'red'    => '0|2|4|6|8',
            'violet' => '0|5'
        ];
        $maxbouns = 0;
        if (isset($colors[$selected])) {
            $code_type = $selected;
            $codes = $colors[$selected];
            switch ($selected) {
                case 'green':
                    $maxbouns = $totalprice * $game->green_lucky_odds;
                    break;
                case 'red':
                    $maxbouns = $totalprice * $game->red_lucky_odds;
                    break;
                case 'violet':
                    $maxbouns = $totalprice * $game->violet_odds;
                    break;
            }
        } else {
            if (!is_numeric($selected)) {
                $this->error('Illegal choice');
            }
            $codes = $selected;
            $code_type = 'single';
            $maxbouns = $totalprice * $game->singular_odds;
        }

        $insertData = [
            'user_id'     => $this->auth->id,
            'game_id'     => $gid,
            'issue'       => $issue,
            'issue_id'    => $issueinfo->id,
            'code_type'   => $code_type,
            'code'        => $codes,
            'singleprice' => $money,
            'multiple'    => $number,
            'totalprice'  => $totalprice,
            'maxbouns'    => $maxbouns,
            'userip'      => $this->request->ip(),
            'cdnip'       => $this->request->host(),
        ];

        $order = [];
        try {
            Db::startTrans();
            // 下注前的操作
            // 扣款
            User::payment($totalprice, $this->auth->id, '投注扣款');
            // 下注方案
            $projects = new Projects();
            $projects->data($insertData)->save();
            if (!$projects->id) {
                throw new Exception('Bet failed');
            }
            // 下注成功后
            $order = [
                'selected'   => $selected,
                'money'      => $money,
                'issue'      => $issue,
                'code_type'  => $code_type,
                'code'       => $codes,
                'totalprice' => $totalprice,
                'maxbouns'   => $maxbouns,
            ];
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('', $order);
    }
}