<?php


namespace app\api\controller;


use app\api\model\RedEnvelopes;
use app\api\model\RedEnvelopesLog;
use app\common\controller\Api;
use think\Db;
use think\Exception;

class Envelope extends Api
{
    public function show()
    {
        $code = $this->request->request('code');
        $token = $this->request->request('token');
        if (!$code || !$token) {
            $this->error('Red envelope does not exist');
        }
        $red_envelope = RedEnvelopes::with(['logs.users'])->where(['code' => $code])->find();
        if (!$red_envelope || !$red_envelope->token || $red_envelope->token != $token) {
            $this->error('Red envelope does not exist');
        }
        // 我的领取状态
        $red_envelope->my_claim_status = 0;
        foreach ($red_envelope->logs as $log) {
            if($log->user_id === $this->auth->id){
                $red_envelope->my_claim_status = 1;
                $red_envelope->my_claim_amount = $log->get_amount;
            }
        }
        $data = $red_envelope->hidden(['admin_id'])->toArray();
        $data['form_user'] = $red_envelope->admin->nickname;
        $this->success('', $data);
    }

    public function rob()
    {
        $code = $this->request->request('code');
        $token = $this->request->request('token');
        if (!$code || !$token) {
            $this->error('Red envelope does not exist');
        }
        $red_envelope = RedEnvelopes::get(['code' => $code]);
        if (!$red_envelope || !$red_envelope->token || $red_envelope->token != $token) {
            $this->error('Red envelope does not exist');
        }

        if ($red_envelope->claim_status >= 2 || $red_envelope->return_status > 0 || !$red_envelope->remaining_number || !$red_envelope->remaining_amount) {
            $this->error('empty');
        }

        $result = RedEnvelopesLog::get(['red_edvelopes_id'=>$red_envelope->id, 'user_id'=>$this->auth->id]);
        if($result){
            $this->error('repeat');
        }

        $get = RedEnvelopes::openRed($red_envelope, $this->auth->id);
        $this->success('', ['amount' => $get]);
    }
}