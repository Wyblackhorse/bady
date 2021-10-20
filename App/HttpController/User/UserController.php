<?php


namespace App\HttpController\User;


use App\Model\UserModel;
use EasySwoole\ORM\DbManager;

class UserController extends UserBase
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
                $one = UserModel::invoke($client)->get(['username' => $username, "kinds" => 2]);
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


}