<?php


namespace App\HttpController\Admin;


use App\Model\UserModel;
use EasySwoole\ORM\DbManager;

class UserController extends AdminBase
{


    #登录
    function login()
    {
        $username = $this->request()->getQueryParam('username');
        $password = $this->request()->getQueryParam('password');

        if (!$this->check_parameter($username, "用户名") || !$this->check_parameter($password, "密码")) {
            return false;
        }
        try {
            DbManager::getInstance()->invoke(function ($client) use ($username, $password) {
                $one = UserModel::invoke($client)->get(['username' => $username, "kinds" => 1]);
                if (!$one) {
                    $this->writeJson(-101, [], "登录失败,账号或者密码错误");
                    return false;
                }
                if ($one['password'] != $password) {
                    $this->writeJson(-101, [], "登录失败,账号或者密码错误");
                    return false;
                }
                $this->writeJson(200, $one, "登录成功");
                return false;
            });
        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "登录异常:" . $e->getMessage());
            return false;
        }
    }


    #创建用户  更新 修改 获取


    function memberInformation()
    {
        $username = $this->request()->getQueryParam('username');
        $password = $this->request()->getQueryParam('password');
        $action = $this->request()->getQueryParam('action');
        $remark = $this->request()->getQueryParam("remark");


        /*        if (!$this->check_parameter($username, "用户名") || !$this->check_parameter($password, "密码") || !$this->check_parameter($action, "方法")
                    || !$this->check_parameter($remark, "备注")) {
                    return false;
                }*/
        try {
            DbManager::getInstance()->invoke(function ($client) use ($username, $password, $action, $remark) {
                if ($action == "add") {
                    #添加
                    $one = UserModel::invoke($client)->get(['username' => $username, 'status' => 1]);
                    if ($one) {
                        $this->writeJson(-101, [], "不要重复添加");
                        return false;
                    }
                    $add = [
                        'username' => $username,
                        'password' => $password,
                        'status' => 1,
                        'kinds' => 2,#普通成员
                        'remark' => $remark,
                        'token' => $this->GetRandStr(36),
                        'updated_at' => time(),
                        'created_at' => time()
                    ];

                    $two = UserModel::invoke($client)->data($add)->save();
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
                    $model = UserModel::create()->limit($limit * ($page - 1), $limit)->withTotalCount();
                    $list = $model->all(['status' => 1, "kinds" => 2]);
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
                    $one = UserModel::invoke($client)->where(['id' => $id])->update(['status' => 2, 'updated_at' => time()]);
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
                        'username' => $username,
                        'password' => $password,
                        'remark' => $remark,
                        'updated_at' => time(),
                    ];
                    $one = UserModel::invoke($client)->where(['id' => $id])->update($add);
                    if (!$one) {
                        $this->writeJson(-101, [], "更新失败");
                        return false;
                    }
                    $this->writeJson(200, [], "更新成功");
                    return true;
                }
            });
        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "memberInformation 异常:" . $e->getMessage());
            return false;
        }
    }


    # token 随机生成
    function GetRandStr($length)
    {
        //字符组合
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len = strlen($str) - 1;
        $randStr = '';
        for ($i = 0; $i < $length; $i++) {
            $num = mt_rand(0, $len);
            $randStr .= $str[$num];
        }
        return $randStr;
    }
}