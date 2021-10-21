<?php


namespace App\HttpController\Admin;


use App\Model\AccountNumberModel;
use App\Model\BabyInformationModel;
use App\Model\StatisticsModel;
use App\Model\UserModel;
use EasySwoole\ORM\DbManager;

class StatisticsController extends AdminBase
{


    # 获取 用户今日数据
    function get_statistics()
    {


        try {
            DbManager::getInstance()->invoke(function ($client) {
                $date = $this->request()->getQueryParam('date');
                $kinds = $this->request()->getQueryParam('kinds');
                $page = $this->request()->getQueryParam('page');
                $limit = $this->request()->getQueryParam("limit");
                $user_id = $this->request()->getQueryParam('user_id');
                $model = StatisticsModel::create()->limit($limit * ($page - 1), $limit)->withTotalCount();
                if (isset($date)) {
                    $model = $model->where(['date' => $date]);
                }
                if (isset($user_id)) {
                    $model = $model->where(['user_id' => $user_id]);
                }


                $list = $model->all(["kinds" => $kinds]);
                foreach ($list as $k => $item) {
                    $res = AccountNumberModel::invoke($client)->get(['id' => $item['account_number_id']]);

                    if ($res) {
                        $list[$k]['name'] = $res['remark'];
                    }

                    $re = UserModel::invoke($client)->get(['id' => $user_id]);
                    if ($re) {
                        $list[$k]['user_name'] = $re['remark'];
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