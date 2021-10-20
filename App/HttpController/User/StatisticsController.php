<?php


namespace App\HttpController\User;


use App\HttpController\User\UserBase;
use App\Model\AccountNumberModel;
use App\Model\StatisticsModel;
use App\Model\UserModel;
use Cassandra\Date;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Pool\Exception\PoolEmpty;

class StatisticsController extends UserBase
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


    # 添加 今日的数据 删除修改
    function addTodayTotal()
    {


        try {
            DbManager::getInstance()->invoke(function ($client) {
                $account_number_id = $this->request()->getQueryParam('account_number_id');
                $today_victory = $this->request()->getQueryParam('today_victory');
                $today_fail = $this->request()->getQueryParam('today_fail');
                $today_bottle = $this->request()->getQueryParam('today_bottle');
                $earnings = $this->request()->getQueryParam('earnings');
                $date = $this->request()->getQueryParam('date');
                $action = $this->request()->getQueryParam('action');
                if ($action == "add") {
                    $one = AccountNumberModel::invoke($client)->get(['id' => $account_number_id]);
                    if (!$one) {
                        $this->writeJson(-101, [], "账号不存在");
                        return false;
                    }

                    $win_rate = $today_victory / ($today_victory + $today_fail);


                    $two = StatisticsModel::invoke($client)->get(['account_number_id' => $account_number_id, 'date' => Date("Y-m-d", time() - 86400)]);
                    if ($two) {
                        $compare = $earnings - $two['earnings'];
                    } else {
                        $compare = $earnings;
                    }


                    $add = [
                        'account_number_id' => $account_number_id,
                        'today_victory' => $today_victory,
                        'today_fail' => $today_fail,
                        'today_bottle' => $today_bottle,
                        'win_rate' => $win_rate,
                        'earnings' => $earnings,
                        'status' => 1,
                        'date' => $date,
                        'created_at' => time(),
                        'updated_at' => time(),
                        'compare' => $compare
                    ];

                    $res = StatisticsModel::invoke($client)->data($add)->save();
                    if (!$res) {
                        $this->writeJson(-101, [], "添加失败");
                        return false;
                    }
                    $this->writeJson(200, [], "添加成功");

                    return true;
                }


            });
        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "异常:" . $e->getMessage());
        }


    }

}