<?php


namespace App\HttpController\Admin;


use App\Model\StatisticsModel;
use App\Model\UserModel;
use EasySwoole\ORM\DbManager;

class StatisticsController extends AdminBase
{


    # 获取 用户今日数据
    function get_statistics()
    {

        $date = $this->request()->getQueryParam('date');
        if (!$this->check_parameter($date, "用户名") || !$this->check_parameter($date, "密码")) {
            return false;
        }
        try {
            DbManager::getInstance()->invoke(function ($client) use ($date) {
                $res = StatisticsModel::invoke($client)->all(['kinds' => 1, 'date' => $date]);
                $this->writeJson(200, $res, "获取成功");


            });
        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "get_statistics异常:" . $e->getMessage());
            return false;
        }

    }


    #获取一共
    function get_statistics_admin()
    {

        $date = $this->request()->getQueryParam('date');
        if (!$this->check_parameter($date, "用户名") || !$this->check_parameter($date, "密码")) {
            return false;
        }
        try {
            DbManager::getInstance()->invoke(function ($client) use ($date) {
                $res = StatisticsModel::invoke($client)->all(['kinds' => 2, 'date' => $date]);
                $this->writeJson(200, $res, "获取成功");

            });
        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "get_statistics异常:" . $e->getMessage());
            return false;
        }
    }

}