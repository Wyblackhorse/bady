<?php

namespace App\HttpController\User;

use App\Model\UserModel;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\ORM\DbManager;

class UserBase extends Controller
{


    protected $who;
    protected $white_router = array('/user/login');

    protected function onRequest(?string $action): ?bool
    {
        #token 校验
        $router_url = $this->request()->getServerParams()['request_uri'];;
        #白名单 不需要检查token
        if (in_array($router_url, $this->white_router)) {
            return true;
        } else {
            #判断 token 是否存在
            try {
                $token = $this->request()->getQueryParam('token');
                if (!isset($token) || empty($token)) {
                    $this->writeJson(-1, [], "token 不可以为空");
                    return false;
                }
                return DbManager::getInstance()->invoke(function ($client) use ($token) {
                    $one = UserModel::invoke($client)->get(['token' => $token]);
                    if ($one) {
                        # 赋值给  who
                        $this->who = $one->toArray();
                        return true;
                    }
                    $this->writeJson(-1, [], "token 非法");
                    return false;
                });

            } catch (\Throwable $e) {
                $this->writeJson(-1, [], "非法参数!");
                return false;
            }
        }


    }


    #检查 参数是否缺少
    function check_parameter($parameter, $str)
    {
        if (isset($parameter) && !empty($parameter)) {
            return true;
        }

        $this->writeJson(-101, [], "参数缺少:" . $str);
        return false;
    }

}