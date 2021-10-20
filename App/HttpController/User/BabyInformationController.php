<?php


namespace App\HttpController\User;


use App\Model\AccountNumberModel;
use App\Model\BabyInformationModel;
use App\Model\UserModel;
use EasySwoole\ORM\DbManager;


class BabyInformationController extends UserBase
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
                    $page = $this->request()->getQueryParam('page');
                    $limit = $this->request()->getQueryParam("limit");
                    $account_number_id = $this->request()->getQueryParam("account_number_id");
                    $model = BabyInformationModel::create()->limit($limit * ($page - 1), $limit)->withTotalCount();

                    if ($account_number_id) {
                        $model = $model->where(['account_number_id' => $account_number_id]);
                    }
                    $list = $model->all(['status' => 1,'user_id'=>$this->who['id']]);
                    foreach ($list as $k => $item) {
                        $res = AccountNumberModel::invoke($client)->get(['id' => $item['account_number_id']]);
                        if ($res) {
                            $list[$k]['name'] = $res['remark'];
                        }
                    }

                    $result = $model->lastQueryResult();
                    $total = $result->getTotalCount();
                    $return_data = [
                        "code" => 0,
                        "msg" => '',
                        'count' => $total,
                        'data' => $list
                    ];
                    $this->response()->write(json_encode($return_data));
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