<?php

namespace App\Task;

use App\Model\AccountNumberModel;
use App\Model\StatisticsModel;
use App\Model\UserModel;
use EasySwoole\HttpClient\Exception\InvalidUrl;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Exception\Exception;
use EasySwoole\Pool\Exception\PoolEmpty;
use EasySwoole\Task\AbstractInterface\TaskInterface;
use mysql_xdevapi\BaseResult;
use PHPUnit\Util\Xml\ValidationResult;

class GetTodayTask implements TaskInterface
{


    function run(int $taskId, int $workerIndex)
    {

        try {
            DbManager::getInstance()->invoke(function ($client) {
                $res = AccountNumberModel::invoke($client)->all();
                $date = Date("Y-m-d", time() - 21600);
                $ppp = strtotime(date($date));
                if ($res) {
                    foreach ($res as $re) {
                        $re['account_number_id'] = $re['id'];
                        $re_array = explode(":", $re['address']);
                        if (count($re_array) == 2) {
                            $data = $this->GetDate($re_array[1]);
                            $per = $this->GetWinRate($re_array[1]);
                            if ($data) {
                                $add['subsection'] = $data['leaderboard']['elo'];

                                #获取胜率
                                if ($per && isset($per['per'])) {
                                    $add['win_rate'] = $per['per'];
                                    $add['avg_time'] = $per['avg_time'];

                                } else {
                                    $add['win_rate'] = $data['leaderboard']['winRate'];
                                }
                                $add['today_bottle'] = $data['slp']['total'];
                                #判断昨日是否存在
                                $yesToday = StatisticsModel::invoke($client)
                                    ->get(['account_number_id' => $re['account_number_id'], 'date' => Date("Y-m-d", $ppp - 86400), "user_id" => $re['user_id']]);

                                if ($yesToday) {
                                    $add['earnings'] = $add['today_bottle'] - $yesToday['today_bottle'];
                                    $add['compare'] = $add['earnings'] - $yesToday['earnings'];
                                    $add['subsection_yes'] = $yesToday['subsection'];
                                } else {
                                    $add['earnings'] = $add['today_bottle'] - 0;
                                    $add['compare'] = $add['earnings'] - 0;
                                    $add['subsection_yes'] = 0;
                                }


                                #判断今日的数据是否存在
                                $one = StatisticsModel::invoke($client)->get(['date' => $date, 'account_number_id' => $re['account_number_id'], "user_id" => $re['user_id'], 'kinds' => 1]);
                                if (!$one) {
                                    #插入
                                    $add['account_number_id'] = $re['account_number_id'];
                                    $add['date'] = $date;
                                    $add['created_at'] = time();
                                    $add['updated_at'] = time();
                                    $add['kinds'] = 1;
                                    $add['user_id'] = $re['user_id'];

                                    StatisticsModel::invoke($client)->data($add)->save();
                                    var_dump("账号: " . $re['account_number_id'] . "插入成功");
                                } else {
                                    $add['updated_at'] = time();
                                    StatisticsModel::invoke($client)->where(['date' => $date, 'account_number_id' => $re['account_number_id'], "user_id" => $re['user_id']])
                                        ->update($add);
                                }

                            }
                        }

                    }

                }





                #个人总统计
                $res = UserModel::invoke($client)->all();
                if ($res) {
                    foreach ($res as $re) {
                        if ($re['id'] == 1) {
                            continue;
                        }
                        #昨天的数据是否存在
//                        var_dump($date);
                        $one = StatisticsModel::invoke($client)->get(['date' => $date, "user_id" => $re['id'], 'kinds' => 3]);
                        $add['earnings'] = StatisticsModel::invoke($client)->where(['date' => $date, "user_id" => $re['id'], 'kinds' => 1])->sum("earnings");
                        $add['compare'] = StatisticsModel::invoke($client)->where(['date' => $date, "user_id" => $re['id'], 'kinds' => 1])->sum("compare");
                        $add['today_bottle'] = StatisticsModel::invoke($client)->where(['date' => $date, "user_id" => $re['id'], 'kinds' => 1])->sum("today_bottle");
                        #$add['subsection_yes'] = StatisticsModel::invoke($client)->where(['date' => $date, "user_id" => $re['id'], 'kinds' => 1])->sum("subsection_yes");
                        $add['updated_at'] = time();
                        if (!$one) {
                            #插入
                            $add['date'] = $date;
                            $add['user_id'] = $re['id'];
                            $add['kinds'] = 3;
                            $add['created_at'] = time();
                            $add['account_number_id'] = 0;
                            StatisticsModel::invoke($client)->data($add)->save();
                        } else {
                            $add['updated_at'] = time();
                            $lp = StatisticsModel::invoke($client)->where(['id' => $one['id']])
                                ->update($add);
                        }
                    }

                }


                $res = StatisticsModel::invoke($client)->get(['date' => $date, "kinds" => 2]);
                $add['earnings'] = StatisticsModel::invoke($client)->where(['date' => $date, 'kinds' => 1])->sum("earnings");
                $add['compare'] = StatisticsModel::invoke($client)->where(['date' => $date, 'kinds' => 1])->sum("compare");
                $add['today_bottle'] = StatisticsModel::invoke($client)->where(['date' => $date, 'kinds' => 1])->sum("today_bottle");
                #$add['subsection_yes'] = StatisticsModel::invoke($client)->where(['date' => $date, 'kinds' => 1])->sum("subsection_yes");
                $add['updated_at'] = time();
                $add['user_id'] = 0;
                if (!$res) {
                    #插入
                    $add['date'] = $date;
                    $add['kinds'] = 2;
                    $add['created_at'] = time();
                    $add['account_number_id'] = 0;
                    StatisticsModel::invoke($client)->data($add)->save();
                } else {
                    $add['updated_at'] = time();
                    $add['kinds'] = 2;
                    StatisticsModel::invoke($client)->where(['date' => $date, 'kinds' => 2])
                        ->update($add);
                }



                var_dump("执行结束!");


            });

        } catch (\Throwable $e) {
            var_dump($e->getMessage());
        }
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // TODO: Implement onException() method.
    }

    function GetDate($id)
    {
        try {
            for ($i = 0; $i < 5; $i++) {

                $client = new \EasySwoole\HttpClient\HttpClient('https://api.axie.management/v1/overview/0x' . $id);
                $headers = array(
                    'authority' => 'api.axie.management',
                    'sec-ch-ua' => '"Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
                    'accept' => 'application/json, text/plain, */*',
                    'sec-ch-ua-mobile' => '?0',
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36',
                    'sec-ch-ua-platform' => '"Windows"',
                    'origin' => 'https://axie.management',
                    'sec-fetch-site' => 'same-site',
                    'sec-fetch-mode' => 'cors',
                    'sec-fetch-dest' => 'empty',
                    'referer' => 'https://axie.management/',
                    'accept-language' => 'zh-CN,zh;q=0.9',
                );
                $client->setHeaders($headers, false, false);
                $client->setTimeout(5);
                $client->setConnectTimeout(10);
                $response = $client->get();
                $response = $response->getBody();
                $data = json_decode($response, true);
                if ($data) {
                    return $data;
                }
            }
            return false;
        } catch (InvalidUrl $e) {
            var_dump($e->getMessage());
            return false;
        }

    }


    function GetWinRate($id)
    {
        try {
//            for ($i = 0; $i < 5; $i++) {
//
//                $client = new \EasySwoole\HttpClient\HttpClient('https://api.axie.management/v1/battles/0x' . $id);
//                $headers = array(
//                    'authority' => 'api.axie.management',
//                    'sec-ch-ua' => '"Chromium";v="94", "Google Chrome";v="94", ";Not A Brand";v="99"',
//                    'accept' => 'application/json, text/plain, */*',
//                    'sec-ch-ua-mobile' => '?0',
//                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36',
//                    'sec-ch-ua-platform' => '"Windows"',
//                    'origin' => 'https://axie.management',
//                    'sec-fetch-site' => 'same-site',
//                    'sec-fetch-mode' => 'cors',
//                    'sec-fetch-dest' => 'empty',
//                    'referer' => 'https://axie.management/',
//                    'accept-language' => 'zh-CN,zh;q=0.9',
//                );
//                $client->setHeaders($headers, false, false);
//                $client->setTimeout(5);
//                $client->setConnectTimeout(10);
//                $response = $client->get();
//                $response = $response->getBody();
//                $data = json_decode($response, true);
//                if ($data && isset($data['battles'])) {
//                    $win_num = 0;
//                    $nums = 0;
//                    $return = [
//                        'per' => 0,
//                        'avg_time' => 0
//                    ];
//                    $data_array = [];
//                    foreach ($data['battles'] as $datum) {
//                        if ($datum['battle_type'] == 0) {
//                            if ($datum['winner'] == 0) {
//                                $win_num++;
//                            }
//                            $nums++;
//
//
//                            #只需要今日的数据大于 6点的数据
//                            $today = strtotime(date("Y-m-d"), time());
//                            $today_six = $today + 21600;
////                            $today = "2021-10-27";
////                            $today_six = 1635285600;
//                            #替换今日的
//                            $time = str_replace(array('T', 'Z'), ' ', $datum['created_at']);
//                            $unix = strtotime($time);
//                            $unix = $unix + 8 * 60 * 60;
//                            $new_data = date("Y-m-d H:i:s", $unix);
//
//                            $new_data = date("Y-m-d", $unix);
//                            if ($new_data == $today && $unix > $today_six) {
//                                array_push($data_array, $unix);
//                            }
//
//                        }
//
//
//                    }
//
//
//                    #计算 差值
//                    if (count($data_array) == 0) {
//                        $return['avg_time'] = 0;
//                    }
//                    if (count($data_array) == 1) {
//                        $return['avg_time'] = $data_array[0];
//                    }
//                    if (count($data_array) > 1) {
//                        $return['avg_time'] = ($data_array[0] - $data_array[count($data_array) - 1]) / count($data_array);
//                    }
//
//                    if ($nums != 0) {
//                        $per = ($win_num / $nums) * 100;
//                        $return['per'] = (int)$per;
//                    }
////                    return 0;
//                    return $return;
//                }
//            }
            return [
                'per' => 0,
                'avg_time' => 0
            ];
        } catch (InvalidUrl $e) {
            var_dump($e->getMessage());
            return false;
        }


    }
}