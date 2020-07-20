<?php

namespace app\admin\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Model;


class Issue extends Model
{


    // 表名
    protected $name = 'game_issue';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'earliestwritetime_text',
        'writetime_text',
        'verifytime_text',
        'salestart_text',
        'saleend_text',
        'canneldeadline_text'
    ];


    public function getEarliestwritetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['earliestwritetime']) ? $data['earliestwritetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getWritetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['writetime']) ? $data['writetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getVerifytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['verifytime']) ? $data['verifytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getSalestartTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['salestart']) ? $data['salestart'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getSaleendTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['saleend']) ? $data['saleend'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getCanneldeadlineTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['canneldeadline']) ? $data['canneldeadline'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setEarliestwritetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setWritetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setVerifytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setSalestartAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setSaleendAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function setCanneldeadlineAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    /**
     * 私有方法 分析奖期规则
     * @param $issuerule
     * @return mixed
     */
    private static function analyze($issuerule)
    {
        $tmp = explode('|', $issuerule);
        $result['sample'] = $tmp[0];
        $result['ymd'] = '';
        $result['n'] = 0;
        preg_match_all('`\[(n)(\d+)\]`', $tmp[0], $matches);
        if ($matches[1]) {
            $result['n'] = $matches[2][0];
        }

        // must be ahead if exist date
        if (preg_match('`^[yY][md]*`i', $tmp[0], $match)) {
            $result['ymd'] = $match[0];
        }

        $result['ymd_length'] = strlen(date($result['ymd']));
        $result['length'] = $result['n'];
        if ($result['ymd']) {
            $result['length'] += $result['ymd_length'];
        }
        $result['length'] += strlen(preg_replace(array('`^[yY][md]*`i', '`\[(n)(\d+)\]`i'), '', $result['sample']));

        $tmp3 = explode(',', $tmp[1]);
        $result['y'] = $tmp3[0] ? true : false;
        $result['m'] = $tmp3[1] ? true : false;
        $result['d'] = $tmp3[2] ? true : false;

        return $result;
    }

    /**
     * 检查奖期格式是否正确
     * @param $issue
     * @param $issuerule
     * @return bool
     */
    public static function checkIssueRule($issue, $issuerule)
    {
        // 必须是数字或数字和-符号
        if (!preg_match('`^\w+[\w-]+$`', $issue, $match)) {
            return false;
        }

        $result = self::analyze($issuerule);
        if (strlen($issue) !== $result['length']) {
            return false;
        }

        $pattern = preg_replace(array('`^[yY][md]*`i', '`\[(n)(\d+)\]`'), array("\\d{{$result['ymd_length']}}", "\\d{{$result['n']}}"), $result['sample']);
        if (!preg_match("`^{$pattern}$`i", $issue)) {
            return false;
        }

        if ($result['ymd'] && $result['ymd_length']) {
            preg_match("`^\d{{$result['ymd_length']}}`i", $issue, $match);
            $date = date('Y-m-d', strtotime($match[0]));
            if ((int)$result['ymd_length'] === 2) {
                $date = date('Y-m-d', strtotime($match[0] + 2000));
            }
            if ($date < '2010-01-01' || $date > '2038-01-19') {
                return false;
            }
        }

        return true;
    }

    public function generalIssue($gid, $firstIssue, $startDate, $endDate)
    {
        $startDate = strtotime(date('Y-m-d', $startDate));
        $endDate = strtotime(date('Y-m-d', $endDate));
        if ($startDate > $endDate || $endDate - $startDate > 86400 * 366) {
            $this->error = '日期范围不合法';
            return false;
        }
        if (!$game = Game::get($gid)) {
            $this->error = '找不到彩种信息';
            return false;
        }

        // 判断是否需要起始期号
        if (strpos($game['issuerule'], 'd') === false) {
            if (!$firstIssue) {
                $this->error = '没有天数的奖期规则必须指定起始期号';
                return false;
            }
            if ($firstIssue === 'auto') {   //每月自动生成奖期
                // 首先获取当前彩种最后一期的期号
                $lastIssueInfo = $this->where(['game_id' => $gid])->whereTime('belongdate', '<', date('Y-m-d', $startDate))->order('issue', 'desc')->find();
                if (empty($lastIssueInfo)) {
                    $this->error = '没有天数的奖期规则必须指定起始期号';
                    return false;
                }
                $firstIssue = $lastIssueInfo['issue'] + 1;
                $currules = self::analyze($game['issuerule']);
                $firstIssue = str_pad($firstIssue, $currules['n'], '0', STR_PAD_LEFT);
            }
            if (!self::checkIssueRule($firstIssue, $game['issuerule'])) {
                $this->error = '请正确输入起始奖期';
                return false;
            }
        }

        // 删除开始日期之后的奖期
        $this->deleteItemByDate($gid, $startDate);

        $rules = self::analyze($game['issuerule']);
        $totalCounter = 0;
        // 获取期号，一般在最后几位
        $curIssueNumber = (int)substr($firstIssue, 0 - $rules['n']);

        for ($i = $startDate; $i <= $endDate; $i += 86400) {
            $belongDate = date('Y-m-d', $i);    // 属于哪天的奖期
            $sample = $rules['sample'];
            // 先替换日期大部
            if ($rules['ymd']) {
                $sample = preg_replace_callback("/([ymd]+)/i", function ($matches) use ($i) {
                    return date($matches[1], $i);
                }, $sample);
            }
            // 得到当前期号$curIssue
            if ($rules['n']) {
                // 如果按天清零，或者按年清零的时候跨年了，则数字部分从头开始
                if (!$rules['d'] || (!$rules['y'] && date('Y', $i) > date('Y', $i - 86400))) {
                    $curIssueNumber = 1;
                }
            }
            // 开始生成
            foreach ($game['issueset_arr'] as $v) {
                if (!$v['status']) {
                    continue;
                }
                $todayStartTime = strtotime(date('Ymd'));
                $startTime = strtotime($v['starttime']) - $todayStartTime;
                $endTime = strtotime($v['endtime']) - $todayStartTime;
                $firstEndTime = strtotime($v['firstendtime']) - $todayStartTime;
                $isFirst = 0;
                if ($endTime <= $startTime) {
                    $endTime += 86400;
                }

                if ($v['cycle'] < 86400) { // 一天销售1期或多期
                    for ($j = $startTime; $j <= $endTime - $v['cycle'];) {
                        $curIssueStartTime = date('Y-m-d H:i:s', $i + $j - $v['endsale']);
                        if (!$isFirst) {
                            $curIssueEndTimeStamp = $i + $firstEndTime;
                        } else {
                            $curIssueEndTimeStamp = $i + $j + $v['cycle'];
                        }
                        $curIssueEndTime = date('Y-m-d H:i:s', $curIssueEndTimeStamp - $v['endsale']);
                        $curDropTime = date('Y-m-d H:i:s', $curIssueEndTimeStamp - $v['droptime']);
                        $curInputCodeTime = date('Y-m-d H:i:s', $curIssueEndTimeStamp + $v['inputcodetime']);
                        $finalIssue = str_replace("[n{$rules['n']}]", str_pad($curIssueNumber, $rules['n'], '0', STR_PAD_LEFT), $sample);

                        // 写入
                        $insertData = [
                            'game_id'           => $gid,
                            'belongdate'        => $belongDate,
                            'issue'             => $finalIssue,
                            'salestart'         => $curIssueStartTime,
                            'saleend'           => $curIssueEndTime,
                            'canneldeadline'    => $curDropTime,
                            'earliestwritetime' => $curInputCodeTime,
                        ];
                        $issueData = (new self)->save($insertData);
                        if (!$issueData) {
                            $this->error = "添加失败！($gid, $belongDate, $finalIssue, $curIssueStartTime, $curIssueEndTime, $curDropTime)";
                            return false;
                        }
                        if (!$isFirst) {
                            $j = $firstEndTime;
                        } else {
                            $j += $v['cycle'];
                        }
                        $isFirst++;
                        $curIssueNumber++;
                        $totalCounter++;
                    }
                } else { // 多天销售1期
                    $this->error = '未开发的功能';
                    return false;
                }
            }
        }
        return $totalCounter;
    }

    /**
     * 删除日期之后的奖期
     * @param $gid
     * @param int $belongdate 奖期所属日期
     * @param int $salestart 销售开始日期
     * @return bool|int
     * @author Rojer
     */
    public function deleteItemByDate($gid, $belongdate = 0, $salestart = 0)
    {
        if ($gid <= 0) {
            $this->error = '游戏ID非法';
            return false;
        }
        if (!$belongdate && !$salestart) {
            $this->error = '必须指定时间';
            return false;
        }
        $condition = ['game_id' => (int)$gid];
        if ($belongdate) {
            $condition['belongdate'] = ['>=', date('Y-m-d', $belongdate)];
        }
        if ($salestart) {
            $condition['salestart'] = ['>=', date('Y-m-d H:i:s', $salestart)];
        }

        return $this->where($condition)->delete();
    }

    /**
     * 返回最近一期没有开奖的奖期
     * for CLI usage
     * @param $gid
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @author  Rojer
     */
    public function getLastNoDrawIssue($gid)
    {
        if ($gid <= 0) {
            return [];
        }

        $where = [
            'game_id'   => $gid,
            'statuscode' => ['<', 2]
        ];
        $result = $this->force('idx_gameid')->where($where)->order('id')->limit(1)->find();

        if (!$result) {
            return [];
        }

        return $result;
    }

    /**
     * 得到当前奖期
     * @param $gid
     * @param int $date
     * @return array
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @author John
     */
    public function getCurrentIssue($gid, $date = 0)
    {
        if ($date === 0) {
            $date = time();
        }
        $result = $this->where(['game_id' => (int)$gid, 'saleend' => ['>=', $date]])->limit(1)->find();
        if (!$result) {
            return [];
        }
        return $result;
    }
}
