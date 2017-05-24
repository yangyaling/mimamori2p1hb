<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 13:19
 */

include 'cmslib.php';

session_start();
session_unset();
session_destroy();

?>

<!DOCTYPE html>
<html>
<head>
    <title>システム管理</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap-responsive.css"/>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css"/>
    <script type="text/javascript" src="assets/js/jquery.js"></script>
    <script type="text/javascript" src="assets/js/jquery.sorted.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.js"></script>
    <script type="text/javascript" src="assets/js/ckform.js"></script>
    <script type="text/javascript" src="assets/js/common.js"></script>
    <style type="text/css">
        body {
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }

        .form-signin {
            max-width: 300px;
            padding: 19px 29px 29px;
            margin: 0 auto 20px;
            background-color: #fff;
            border: 1px solid #e5e5e5;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            -webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
            -moz-box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
        }

        .form-signin .form-signin-heading,
        .form-signin {
            margin-bottom: 10px;
        }

        .form-signin input[type="text"],
        .form-signin input[type="password"] {
            font-size: 16px;
            height: auto;
            margin-bottom: 15px;
            padding: 7px 9px;
        }
    </style>
    <script type="text/javascript">
        $(function () {
            $("#hostcd").focus();
        });
        function check() {
            var txt_hostcd = $.trim($("#hostcd").val());
            var txt_userid = $.trim($("#userid").val());
            var txt_password = $.trim($("#password").val());
            var isSuccess = '';
            if (txt_hostcd.length == 0) {
                isSuccess = "ホストコードが必須！";
                $("#hostcd").focus();
            } else if (txt_userid.length == 0) {
                isSuccess = "ユーザIDが必須！";
                $("#userid").focus();
            } else if (txt_password.length == 0) {
                isSuccess = "パスワードが必須！";
                $("#password").focus();
            }
            if (isSuccess.length > 0) {
                $("#errmsg").text(isSuccess);
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<div class="container">
    <form class="form-signin" method="post" action="loginproc.php" onsubmit="return check()" target="_self">
        <h2 class="form-signin-heading">LOGIN</h2>

        <div id='errmsg' class='error-msg'>
            <?php
            @$errNo = $_GET['errno'];
            if ($errNo && !is_empty($errNo)) {
                if ($errNo == '1') {
                    echo "入力された情報が不正。";
                } else if ($errNo == '2') {
                    echo "登録してください。";
                }
            }
            ?>
        </div>

        <input type="text" id="hostcd" name="hostcd" class="input-block-level" placeholder="ホストコード">
        <input type="text" id="userid" name="userid" class="input-block-level" placeholder="ユーザID">
        <input type="password" id="password" name="password" class="input-block-level" placeholder="パスワード">

        <p>
            <button class="btn btn-large btn-primary" type="submit">Login</button>
        </p>
    </form>
</div>
<script type='text/javascript'>
    <?php
    @$hostCd = $_GET['hostcd'];
    @$userId = $_GET['userid'];
    if (!is_empty($hostCd)) {
        echo "$('#hostcd').val('$hostCd');";
    }
    if (!is_empty($userId)) {
        echo "$('#userid').val('$userId');";
    }
    ?></script>
</body>
</html>