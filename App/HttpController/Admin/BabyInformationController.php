<?php


namespace App\HttpController\Admin;


use App\Model\AccountNumberModel;
use App\Model\BabyInformationModel;
use App\Model\UserModel;
use EasySwoole\ORM\DbManager;

class BabyInformationController extends UserController
{


    #添加宠物宝宝
    function addBaby()
    {


        #这里如果是删除 更新的时候 全部都是改成 空值 就好了
        $baby_id = $this->request()->getQueryParam('baby_id');
        $price = $this->request()->getQueryParam('price');
        $remark = $this->request()->getQueryParam('remark');
        $account_number_id = $this->request()->getQueryParam('account_number_id');
        $action = $this->request()->getQueryParam('action');

        if (!$this->check_parameter($baby_id, "baby_id") || !$this->check_parameter($price, "price") || !$this->check_parameter($remark, "remark")
            || !$this->check_parameter($account_number_id, "account_number_id") || !$this->check_parameter($action, "action")) {
            return false;
        }
        try {
            DbManager::getInstance()->invoke(function ($client) use ($baby_id, $price, $remark, $action, $account_number_id) {
                if ($action == "add") {
                    #添加
                    $one = AccountNumberModel::invoke($client)->get(['id' => $account_number_id]);
                    if (!$one) {
                        $this->writeJson(-101, [], "账号不存在,非法添加");
                        return false;
                    }
                    $add = [
                        'updated_at' => time(),
                        'created_at' => time(),
                        'price' => $price,
                        'baby_id' => $baby_id,
                        'account_number_id' => $account_number_id,
                        'remark' => $remark,
                        'status' => 1
                    ];
                    $two = BabyInformationModel::invoke($client)->data($add)->save();
                    if (!$two) {
                        $this->writeJson(-101, [], "添加失败");
                        return false;
                    }
                    $this->writeJson(200, [], "添加成功");
                    return true;
                }

                if ($action == "select") {
                    $one = BabyInformationModel::invoke($client)->all(['status' => 1]);
                    $this->writeJson(200, [], "获取成功");
                    return true;
                }

                if ($action == "del") {
                    $id = $this->request()->getQueryParam('id');
                    if (!$this->check_parameter($id, "id")) {
                        return false;
                    }
                    $one = BabyInformationModel::invoke($client)->where(['id' => $id])->update(['status' => 2, 'updated_at' => time()]);
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
                        'price' => $price,
                        'baby_id' => $baby_id,
                        'account_number_id' => $account_number_id,
                        'remark' => $remark,
                    ];
                    $one = BabyInformationModel::invoke($client)->where(['id' => $id])->update($add);
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