<?php


namespace App\HttpController\User;


use App\HttpController\User\UserBase;
use App\Model\AccountNumberModel;
use App\Model\StatisticsModel;
use App\Model\UserModel;
use Cassandra\Date;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Pool\Exception\PoolEmpty;

class StatisticsController extends UserBase
{


    # 获取 用户今日数据
    function get_statistics()
    {
        $date = $this->request()->getQueryParam('date');

        try {
            DbManager::getInstance()->invoke(function ($client) use ($date) {

                $page = $this->request()->getQueryParam('page');
                $limit = $this->request()->getQueryParam("limit");
                $date = $this->request()->getQueryParam('date');
                $account_number_id = $this->request()->getQueryParam('account_number_id');

                $model = StatisticsModel::create()->limit($limit * ($page - 1), $limit)->withTotalCount();

                if (isset($account_number_id)) {
                    $model = $model->where(['account_number_id' => $account_number_id]);
                }

                if (isset($date)) {
                    $list = $model->all(['kinds' => 1, 'date' => $date, 'user_id' => $this->who['id']]);
                } else {
                    $list = $model->all(['kinds' => 1, 'user_id' => $this->who['id']]);
                }


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
                $date = $this->request()->getQueryParam('date');
                $action = $this->request()->getQueryParam('action');
                $subsection = $this->request()->getQueryParam('subsection');
                $ppp = strtotime(date($date));

                if ($action == "add") {
                    $one = AccountNumberModel::invoke($client)->get(['id' => $account_number_id]);
                    if (!$one) {
                        $this->writeJson(-101, [], "账号不存在");
                        return false;
                    }
                    $win_rate = $today_victory / ($today_victory + $today_fail);
                    #查看 昨天是否存在数据
                    $two = StatisticsModel::invoke($client)->get(['account_number_id' => $account_number_id, 'date' => Date("Y-m-d", $ppp - 86400)]);
                    if ($two) {
                        # 存在  昨天的数据需要更新    用更新的瓶子数-昨日的瓶子数

                        $compare = $today_bottle - $two['today_bottle'];
                        $subsection_yes = $subsection - $two['subsection'];
                        var_dump($compare);
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
                        'user_id' => $this->who['id'],
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




                    $zz = StatisticsModel::invoke($client)->get(['date' => $date, 'kinds' => 3, "user_id" => $this->who['id']]);
                    if (!$zz) {
                        $add['kinds'] = 3;
                        $add['account_number_id'] = 0;
                        $add['user_id'] = $this->who['id'];
                        $res = StatisticsModel::invoke($client)->data($add)->save();
                    } else {
                        $ll = StatisticsModel::invoke($client)->get(['date' => Date("Y-m-d", $ppp - 86400), 'kinds' => 3, "user_id" => $this->who['id']]);
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
                        StatisticsModel::invoke($client)->where(['id' => $three['id']])->update([
                            'compare' => $compare
                        ]);

                        #修改明天的是 个人总统计
                        $one = StatisticsModel::invoke($client)->get(['kinds' => 3, "date" => Date("Y-m-d", $ppp + 86400), 'user_id' => $this->who['id']]);
                        if ($one) {
                            $zz = StatisticsModel::invoke($client)->get(['date' => $date, 'kinds' => 3, "user_id" => $this->who['id']]);
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
                }
            });
        } catch (\Throwable $e) {
            $this->writeJson(-1, [], "异常:" . $e->getMessage());
        }


    }

}