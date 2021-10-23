<?php


namespace App\HttpController\Admin;


use App\Model\AccountNumberModel;
use App\Model\BabyInformationModel;
use App\Model\StatisticsModel;
use App\Model\UserModel;
use EasySwoole\DDL\Filter\FilterUnsigned;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Pool\Exception\PoolEmpty;

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
                    $re = UserModel::invoke($client)->get(['id' => $item['user_id']]);
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


    function addStatistics()
    {

        try {


            DbManager::getInstance()->invoke(function ($client) {
                $account_number_id = $this->request()->getQueryParam('account_number_id');
                $today_victory = $this->request()->getQueryParam('today_victory');
                $today_fail = $this->request()->getQueryParam('today_fail');
                $today_bottle = $this->request()->getQueryParam('today_bottle');
                $date = $this->request()->getQueryParam('date');
                $subsection = $this->request()->getQueryParam('subsection');
                $user_id = $this->request()->getQueryParam('user_id');
                $one = AccountNumberModel::invoke($client)->get(['id' => $account_number_id]);
                $ppp = strtotime(date($date));

                if (!$one) {
                    $this->writeJson(-101, [], "账号不存在");
                    return false;
                }
                $win_rate = $today_victory / ($today_victory + $today_fail);
                $two = StatisticsModel::invoke($client)->get(['account_number_id' => $account_number_id, 'date' => Date("Y-m-d", $ppp - 86400)]);
                if ($two) {
                    $compare = $today_bottle - $two['today_bottle'];
                    $subsection_yes = $subsection - $two['subsection'];
                } else {
                    $compare = $today_bottle;
                    $subsection_yes = $subsection;
                }
                $add = [
                    'account_number_id' => $account_number_id,
                    'today_victory' => $today_victory,
                    'today_fail' => $today_fail,
                    'today_bottle' => $today_bottle,
                    'win_rate' => $win_rate,
                    'status' => 1,
                    'date' => $date,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'compare' => $compare,
                    'user_id' => $user_id,
                    'subsection' => $subsection,
                    'subsection_yes' => $subsection_yes
                ];


                $pp = StatisticsModel::invoke($client)->get(['date' => $date, 'account_number_id' => $account_number_id]);
                if ($pp) {
                    $res = StatisticsModel::invoke($client)->where(['id' => $pp['id']])->update($add);
                    if (!$res) {
                        $this->writeJson(-101, [], "添加失败");
                        return false;
                    }
                    $this->writeJson(200, [], "添加成功");
                } else {
                    $res = StatisticsModel::invoke($client)->data($add)->save();
                    if (!$res) {
                        $this->writeJson(-101, [], "添加失败");
                        return false;
                    }
                    $this->writeJson(200, [], "添加成功");
                }
                #查询kinds =2 是否存在
                $zz = StatisticsModel::invoke($client)->get(['date' => $date, 'kinds' => 2]);
                if (!$zz) {
                    $add['kinds'] = 2;
                    $add['account_number_id'] = 0;
                    $res = StatisticsModel::invoke($client)->data($add)->save();
                } else {
                    $ll = StatisticsModel::invoke($client)->get(['date' => Date("Y-m-d", $ppp - 86400), 'kinds' => 2]);
                    if ($ll) {
                        $compare = $zz['today_bottle'] + $add['today_bottle'] - $ll['today_bottle'];
                    } else {
                        $compare = $zz['today_bottle'] + $add['today_bottle'];
                    }

                    $win_rate = ($zz['today_victory'] + $add['today_victory']) / ($zz['today_victory'] + $add['today_victory'] + $zz['today_fail'] + $add['today_fail']);
                    StatisticsModel::invoke($client)->where(['id' => $zz['id']])->update(
                        [
                            'today_victory' => QueryBuilder::inc($add['today_victory']),
                            'today_fail' => QueryBuilder::inc($add['today_fail']),
                            'today_bottle' => QueryBuilder::inc($add['today_bottle']),
                            'updated_at' => time(),
                            'compare' => $compare,
                            'win_rate' => $win_rate
                        ]
                    );
                }

                $zz = StatisticsModel::invoke($client)->get(['date' => $date, 'kinds' => 3, "user_id" => $user_id]);
                if (!$zz) {
                    $add['kinds'] = 3;
                    $add['account_number_id'] = 0;
                    $add['user_id'] = $user_id;
                    $res = StatisticsModel::invoke($client)->data($add)->save();
                } else {
                    $ll = StatisticsModel::invoke($client)->get(['date' => Date("Y-m-d", $ppp - 86400), 'kinds' => 2]);
                    if ($ll) {
                        $compare = $zz['today_bottle'] + $add['today_bottle'] - $ll['today_bottle'];
                    } else {
                        $compare = $zz['today_bottle'] + $add['today_bottle'];
                    }
                    $win_rate = ($zz['today_victory'] + $add['today_victory']) / ($zz['today_victory'] + $add['today_victory'] + $zz['today_fail'] + $add['today_fail']);
                    StatisticsModel::invoke($client)->where(['id' => $zz['id']])->update(
                        [
                            'today_victory' => QueryBuilder::inc($add['today_victory']),
                            'today_fail' => QueryBuilder::inc($add['today_fail']),
                            'today_bottle' => QueryBuilder::inc($add['today_bottle']),
                            'updated_at' => time(),
                            'compare' => $compare,
                            'win_rate' => $win_rate
                        ]
                    );
                }

                #查看 明天是否存在
                $three = StatisticsModel::invoke($client)->get(['account_number_id' => $account_number_id, 'date' => Date("Y-m-d", $ppp + 86400)]);
                if ($three) {
                    $compare = $three['today_bottle'] - $add['today_bottle']; #修改明天的数据
                    var_dump($compare);
                    StatisticsModel::invoke($client)->where(['id' => $three['id']])->update([
                        'compare' => $compare
                    ]);
                    #修改明天的是 个人总统计
                    $one = StatisticsModel::invoke($client)->get(['kinds' => 3, "date" => Date("Y-m-d", $ppp + 86400), 'user_id' => $user_id]);
                    if ($one) {
                        $zz = StatisticsModel::invoke($client)->get(['date' => $date, 'kinds' => 3, "user_id" => $user_id]);
                        if ($zz) {
                            $compare = $one['today_bottle'] - $zz['today_bottle'];
                            StatisticsModel::invoke($client)->where(['id' => $one['id']])->update(['compare' => $compare]);
                        }
                    }
                    #修改 明天的 总统计
                    $one = StatisticsModel::invoke($client)->get(['kinds' => 2, "date" => Date("Y-m-d", $ppp + 86400)]);
                    if ($one) {
                        $zz = StatisticsModel::invoke($client)->get(['date' => $date, 'kinds' => 2]);
                        if ($zz) {
                            $compare = $one['today_bottle'] - $zz['today_bottle'];
                            StatisticsModel::invoke($client)->where(['id' => $one['id']])->update(['compare' => $compare]);
                        }
                    }

                }


                return true;

            });

        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "异常:" . $e->getMessage());
        }
    }


    function updateStatistics()
    {
        try {
            DbManager::getInstance()->invoke(function ($client) {
                $action = $this->request()->getQueryParam('action');
//                $account_number_id = $this->request()->getQueryParam('account_number_id');
//                $today_victory = $this->request()->getQueryParam('today_victory');
//                $today_fail = $this->request()->getQueryParam('today_fail');
//                $today_bottle = $this->request()->getQueryParam('today_bottle');
//                $date = $this->request()->getQueryParam('date');
//                $subsection = $this->request()->getQueryParam('subsection');
//                $one = AccountNumberModel::invoke($client)->get(['id' => $account_number_id]);

                if ($action == "del") {
                    $id = $subsection = $this->request()->getQueryParam('id');
                    $one = StatisticsModel::invoke($client)->get(['id' => $id]);
                    $date = $one['date'];
                    $ppp = strtotime(date($date));
                    $user_id = $one['user_id'];

                    if ($one) {
                        #删除成功
                        #个人总统计 重新计算
                        #对今日的 先重新计算
                        StatisticsModel::invoke($client)->destroy(['id' => $id]);

                        # 今天的总统计 减少 QueryBuilder::dec($one['today_bottle'])
                        StatisticsModel::invoke($client)->where(['kinds' => 3, "user_id" => $user_id, "date" => $date])->update([
                            'today_bottle' => QueryBuilder::dec($one['today_bottle']),
                            'compare' => QueryBuilder::dec($one['today_bottle'])
                        ]);

                        #对明天总统计
                        $two = StatisticsModel::invoke($client)
                            ->where(['kinds' => 3, "user_id" => $user_id, "date" => Date("Y-m-d", $ppp + 86400)])
                            ->update(['compare' => QueryBuilder::inc($one['today_bottle'])]);


                        #总统计
                        StatisticsModel::invoke($client)->where(['kinds' => 2, "date" => $date])->update([
                            'today_bottle' => QueryBuilder::dec($one['today_bottle']),
                            'compare' => QueryBuilder::dec($one['today_bottle'])
                        ]);

                        $two = StatisticsModel::invoke($client)
                            ->where(['kinds' => 2, "date" => Date("Y-m-d", $ppp + 86400)])
                            ->update(['compare' => QueryBuilder::inc($one['today_bottle'])]);

                    }

                }


            });
        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "异常:" . $e->getMessage());
        }
    }


}