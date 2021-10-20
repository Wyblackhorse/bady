<?php


namespace App\HttpController\Admin;


use App\Model\AccountNumberModel;
use App\Model\UserModel;
use EasySwoole\ORM\DbManager;

class AccountNumberController extends AdminBase
{

    #添加账号 修改 深处 获取
    function addAccount()
    {


        #这里如果是删除 更新的时候 全部都是改成 空值 就好了
        $address = $this->request()->getQueryParam('address');
        $mail = $this->request()->getQueryParam('mail');
        $remark = $this->request()->getQueryParam('remark');
        $user_id = $this->request()->getQueryParam('user_id');
        $action = $this->request()->getQueryParam('action');

        if (!$this->check_parameter($address, "address") || !$this->check_parameter($mail, "mail") || !$this->check_parameter($remark, "remark")
            || !$this->check_parameter($user_id, "user_id") || !$this->check_parameter($action, "action")) {
            return false;
        }
        try {
            DbManager::getInstance()->invoke(function ($client) use ($address, $mail, $remark, $action, $user_id) {
                if ($action == "add") {
                    #添加
                    $one = UserModel::invoke($client)->get(['id' => $user_id]);
                    if (!$one) {
                        $this->writeJson(-101, [], "改用户不存在,非法添加");
                        return false;
                    }
                    $add = [
                        'updated_at' => time(),
                        'created_at' => time(),
                        'address' => $address,
                        'mail' => $mail,
                        'user_id' => $user_id,
                        'remark' => $remark,
                        'status' => 1
                    ];
                    $two = AccountNumberModel::invoke($client)->data($add)->save();
                    if (!$two) {
                        $this->writeJson(-101, [], "添加失败");
                        return false;
                    }
                    $this->writeJson(200, [], "添加成功");
                    return true;
                }

                if ($action == "select") {
                    $one = AccountNumberModel::invoke($client)->all(['status' => 1]);
                    foreach ($one as $k => $item) {
                        $res = UserModel::invoke($client)->get(['id' => $item['user_id']]);
                        if ($res) {
                            $one[$k]['name'] = $res['remark'];
                        }
                    }
                    $this->writeJson(200, [], "获取成功");
                    return true;
                }


                if ($action == "del") {
                    $id = $this->request()->getQueryParam('id');
                    if (!$this->check_parameter($id, "id")) {
                        return false;
                    }
                    $one = AccountNumberModel::invoke($client)->where(['id' => $id])->update(['status' => 2, 'updated_at' => time()]);
                    if (!$one) {
                        $this->writeJson(-101, [], "删除失败");
                        return false;
                    }
                    $this->writeJson(200, [], "删除成功");
                    return true;
                }


                if ($action == "update") {
                    $id = $this->request()->getQueryParam('id');
                    if (!$this->check_parameter($id, "id")) {
                        return false;
                    }

                    $add = [
                        'updated_at' => time(),
                        'address' => $address,
                        'mail' => $mail,
                        'user_id' => $user_id,
                        'remark' => $remark,
                    ];
                    $one = AccountNumberModel::invoke($client)->where(['id' => $id])->update($add);
                    if (!$one) {
                        $this->writeJson(-101, [], "更新失败");
                        return false;
                    }
                    $this->writeJson(200, [], "更新成功");
                    return true;
                }
            });
        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "addAccount 异常:" . $e->getMessage());
            return false;
        }

    }

}