<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include 'cmslib.php';

$loginUser = checkSession();

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>システム管理</title>
    <meta charset="UTF-8">
    <link href="assets/css/dpl-min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/bui-min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/main-min.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<div class="header">
    <div class="dl-log">Welcome,<span class="dl-log-user"><?= $loginUser->getUserName() ?></span><a
            href="logoutproc.php"
            title="logout" class="dl-log-quit">[LOGOUT]</a>
    </div>
</div>
<div class="content">
    <div class="dl-main-nav">
        <div class="dl-inform">
            <div class="dl-inform-title"><s class="dl-inform-icon dl-up"></s></div>
        </div>
        <ul id="J_Nav" class="nav-list ks-clear">
            <li class="nav-item dl-selected">
                <div class="nav-item-inner nav-monitor">システム管理</div>
            </li>
            <!--li class="nav-item dl-selected">
                <div class="nav-item-inner nav-monitor">ユーザ管理</div>
            </li-->
        </ul>
    </div>
    <ul id="J_NavContent" class="dl-tab-conten"></ul>
</div>
<script type="text/javascript" src="assets/js/jquery-1.8.1.min.js"></script>
<script type="text/javascript" src="assets/js/bui-min.js"></script>
<script type="text/javascript" src="assets/js/common/main-min.js"></script>
<script type="text/javascript" src="assets/js/config-min.js"></script>
<script>
    BUI.use('common/main', function () {
        var config = [
            {
                id: '1',
                homePage: '1',
                menu: [{
                    text: 'システム管理', items: [
                        {id: '1', text: '可視化設定管理', href: 'dashboard/vzconfig.php'},
//                        {id: '2', text: 'センサー管理', href: 'sensor/gateway.php'},
//                        {id: '3', text: 'データ比較', href: 'dm/index.php'}
                    ]
                }]
//            },{
//            id: '2',
//                homePage: '1',
//                menu: [{
//                    text: 'ユーザ管理', items: [
//                        {id: '1', text: 'スタッフ管理', href: 'user/staff.php'},
//                        {id: '2', text: 'ユーザ管理', href: 'user/api/cust.php'}
//                    ]
//            }]
        }
        ];
        new PageUtil.MainPage({
            modulesConfig: config
        });
    });
</script>
</body>
</html>