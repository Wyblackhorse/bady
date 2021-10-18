<?php


namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\ORM\Db\Config;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;


class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {

        #注册mysql连接池
        $mysql_config = new Config(\EasySwoole\EasySwoole\Config::getInstance()->getConf('MYSQL'));
        DbManager::getInstance()->addConnection(new Connection($mysql_config));

    }
}