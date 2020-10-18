<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use fast\Random;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'id,username,nickname';

    protected $dataFilter = false;

    protected $dataFilterCondition = [];

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    /**
     * @var \app\admin\model\User
     */
    protected $currentUser = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
        if ($this->auth->frontend_user_id) {
            $this->currentUser = $this->model::get($this->auth->frontend_user_id);
            if ($this->currentUser) {
                $this->dataFilter = true;
                $this->dataFilterCondition = [
                    ['lft', '>', $this->currentUser->lft],
                    ['rgt', '<', $this->currentUser->rgt],
                ];
            }
        }
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with('group,parentUser')
                ->where($where)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->with('group,parentUser')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v) {
                $v->agent_level = $v->depth - $this->currentUser->depth;
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            if ($params) {
                $result = false;
                Db::startTrans();
                try {
                    $params['jointime'] = time();
                    $salt = \fast\Random::alnum();
                    $userAuth = \app\common\library\Auth::instance();
                    if (!$userAuth) {
                        throw new \Exception('创建失败');
                    }
                    $params['password'] = $userAuth->getEncryptPassword($params['password'], $salt);
                    $params['salt'] = $salt;
                    if ($params['payment_password']) {
                        $params['payment_password'] = md5($params['payment_password']);
                    }
                    $params = $this->preExcludeFields($params);
                    $this->model->allowField(true);
                    $nested = new \Nested($this->model);
                    $userId = $nested->insert($params['pid'] ?? 0, $params);
                    if (!$userId) {
                        throw new \Exception($nested->getError());
                    }
                    $user = $this->model::get($userId);
                    if (!$user) {
                        throw new \Exception('没有找到用户');
                    }
                    $user->referrer = Random::id2code($user->id);
                    $result = $user->save();
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->modelValidate = true;
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), '', ['class' => 'form-control selectpicker']));
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }

}
