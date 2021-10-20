<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {


        /**
         * App/HttpController/Admin/UserController.php
         */
        $routeCollector->get('/admin/login', '/Admin/UserController/login');
        #memberInformation
        $routeCollector->get('/admin/memberInformation', '/Admin/UserController/memberInformation');


        /**
         * AccountNumberController
         */

        #addAccount
        $routeCollector->get('/admin/addAccount', '/Admin/AccountNumberController/addAccount');


        /**
         * BabyInformationController
         */

        #addBaby
        $routeCollector->get('/admin/addBaby', '/Admin/BabyInformationController/addBaby');


        /**
         * StatisticsController
         */

        $routeCollector->get('/admin/get_statistics', '/Admin/StatisticsController/get_statistics');


        /**
         * User 登录
         */
        $routeCollector->get('/user/login', '/User/UserController/login');

        #addTodayTotal
        $routeCollector->get('/user/addTodayTotal', '/User/StatisticsController/addTodayTotal');
        #memberInformation
        $routeCollector->get('/user/addAccount', '/User/AccountNumberController/addAccount');
        $routeCollector->get('/user/addBaby', '/User/BabyInformationController/addBaby');
        $routeCollector->get('/user/get_statistics', '/User/StatisticsController/get_statistics');


        /*
          * eg path : /router/index.html  ; /router/ ;  /router
         */
        $routeCollector->get('/router', '/test');
        /*
         * eg path : /closure/index.html  ; /closure/ ;  /closure
         */
        $routeCollector->get('/closure', function (Request $request, Response $response) {
            $response->write('this is closure router');
            //不再进入控制器解析
            return false;
        });
    }
}