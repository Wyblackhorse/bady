<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\HttpClient\Exception\InvalidUrl;
use PharIo\Manifest\ElementCollection;

class Index extends Controller
{

    public function index()
    {
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    function test()
    {

        try {

            $response = "";
            for ($i = 0; $i < 5; $i++) {
                $id = '0xcc8ddcd8fdc045966c67325d6bba8feeff09b8a1';
                $client = new \EasySwoole\HttpClient\HttpClient('https://api.axie.management/v1/battles/0xcc8ddcd8fdc045966c67325d6bba8feeff09b8a1');
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
                    $data_array = [];
                    foreach ($data['battles'] as $datum) {

                        if ($datum['battle_type'] == 0) {  #pvp
                            #只需要今日的数据大于 6点的数据
                            $today = strtotime(date("Y-m-d"), time());
                            $today_six = $today + 21600;
                            $today = "2021-10-27";
                            $today_six = 1635285600;
                            #替换今日的
                            $time = str_replace(array('T', 'Z'), ' ', $datum['created_at']);
                            $unix = strtotime($time);
                            $unix = $unix + 8 * 60 * 60;
                            $new_data = date("Y-m-d H:i:s", $unix);

                            $new_data = date("Y-m-d", $unix);
                            if ($new_data == $today && $unix > $today_six) {
                                array_push($data_array, $unix);
                            }
                        }
                    }
                    #计算 差值
                    if (count($data_array) == 0) {
                        return 0;
                    }
                    if (count($data_array) == 1) {
                        return $data_array[0];
                    }
//                    var_dump($data_array);
                    if (count($data_array) > 1) {
                        return ($data_array[0] - $data_array[count($data_array) - 1]) / count($data_array);
                    }
                }
            }

            $this->writeJson(200, [], $response);
            return false;
        } catch (InvalidUrl $e) {
            var_dump($e->getMessage());
            $this->writeJson(-1, [], []);
            return false;
        }
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }
}