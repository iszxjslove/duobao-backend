<?php


namespace app\api\controller;


use app\api\model\FeeLog;
use app\api\model\Issue;
use app\api\model\Game as GameModel;
use app\common\controller\Api;
use app\common\model\IssueSales;
use app\common\model\Projects;
use app\common\model\UserStatistics;
use think\Config;
use think\Db;
use think\Exception;
use app\common\model\User;
use think\Hook;

class Game extends Api
{
    protected $model = null;

    public function play()
    {
        $gid = 1;
        $time = time();
        $issue = Issue::get(['saleend' => ['>', $time]]);
        if (!$issue) {
            $this->error('There is no period to bet on');
        }
        $data = $issue->visible(['id', 'issue', 'salestart', 'saleend', 'earliestwritetime', 'canneldeadline'])->toArray();
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
        $total = $issueModel
            ->where($where)
            ->order('id', 'desc')
            ->count();
        $list = $issueModel
            ->where($where)
            ->field('issue,code,last_digits,colors,statuscode,statusbonus')
            ->limit($offset, $limit)
            ->order('id', 'desc')
            ->select();
        return json(['total' => $total, 'rows' => $list]);
    }

    public function projects()
    {
        $this->model = new Projects();
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $total = $this->model
            ->where($where)
            ->order($sort, $order)
            ->count();

        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        return json(['total' => $total, 'rows' => $list]);
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
        $issueinfo = Issue::get(['issue' => $issue]);
        if (!$issueinfo || $issueinfo->saleend < $time) {
            $this->error("Can't bet");
        }

        // 计算手续费 ---------- START ------
        $wager_rate = Config::get('site.wager_rate');
        ksort($wager_rate);
        $rate = 0;
        foreach ($wager_rate as $key => $value) {
            if ($totalprice >= $key) {
                $rate = $value;
            }
        }
        $fee = bcmul($totalprice, bcdiv($rate, 100, 2), 2);
        // 合同金额
        $contract_amount = $totalprice - $fee;
        // --------------- END ----------------

        $colors = [
            'green'  => '1|3|5|7|9',
            'red'    => '0|2|4|6|8',
            'violet' => '0|5'
        ];
        $maxbouns = 0;
        if (isset($colors[$selected])) {
            $color = $selected;
            $codes = $colors[$selected];
            switch ($selected) {
                case 'green':
                    $max = $game->green_ordinary;
                    if ($game->green_ordinary < $game->green_lucky_odds) {
                        $max = $game->green_lucky_odds;
                    }
                    $maxbouns = bcmul($contract_amount, $max, 2);
                    break;
                case 'red':
                    $max = $game->red_ordinary;
                    if ($game->red_ordinary < $game->red_lucky_odds) {
                        $max = $game->red_lucky_odds;
                    }
                    $maxbouns = bcmul($contract_amount, $max, 2);
                    break;
                case 'violet':
                    $maxbouns = $contract_amount * $game->violet_odds;
                    break;
            }
        } else {
            if (!is_numeric($selected)) {
                $this->error('Illegal choice');
            }
            $codes = $selected;
            $color = 'singular';
            $maxbouns = $contract_amount * $game->singular_odds;
        }

        $insertData = [
            'user_id'         => $this->auth->id,
            'game_id'         => $gid,
            'issue'           => $issue,
            'issue_id'        => $issueinfo->id,
            'color'           => $color,
            'code'            => $codes,
            'selected'        => $selected,
            'singleprice'     => $money,
            'multiple'        => $number,
            'deducttime'      => time(),
            'maxbouns'        => $maxbouns,
            'totalprice'      => $totalprice,
            'contract_amount' => $contract_amount,
            'fee'             => $fee,
            'userip'          => $this->request->ip(),
            'cdnip'           => $this->request->host(),
        ];

        $output = [];
        try {
            Db::startTrans();
            $user = $this->auth->getUser();
            // 下注前的操作
            Hook::listen("game_wager_before", $user, $insertData);
            // 扣款
            User::payment($this->auth->id, $totalprice, 'game wager');
            // 下注方案
            $projects = new Projects();
            $projects->data($insertData)->save();
            if (!$projects->id) {
                throw new Exception('Bet failed');
            }
            // 下注成功后
            Hook::listen("game_wager_after", $user, $projects);
            $output = [
                'selected'   => $selected,
                'money'      => $money,
                'issue'      => $issue,
                'issue_id'   => $issueinfo->id,
                'color'      => $color,
                'code'       => $codes,
                'totalprice' => $totalprice,
                'maxbouns'   => $maxbouns,
            ];
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('', $output);
    }
}