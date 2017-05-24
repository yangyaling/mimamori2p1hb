<?php
/**
 * 可視化設定ファイルインポート
 * User: NITIOSRD
 * Date: 2017/05/11
 * Time: 18:19
 */

include '../cmslib.php';

$loginUser = checkSession();

?>
<!DOCTYPE html>
<html>
<head>
    <title>可視化設定ファイルインポート</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.css"/>
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap-responsive.css"/>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="../assets/css/pagination.css"/>
    <script type="text/javascript" src="../assets/js/jquery.js"></script>
    <script type="text/javascript" src="../assets/js/jquery.sorted.js"></script>
    <script type="text/javascript" src="../assets/js/bootstrap.js"></script>
    <script type="text/javascript" src="../assets/js/ckform.js"></script>
    <script type="text/javascript" src="../assets/js/common.js"></script>
    <style type="text/css">
        body {
            padding: 10px;
            padding-top: 40px;
            padding-bottom: 40px;
        }
    </style>
</head>
<body>
<h2>可視化設定ファイルインポート</h2>
<br/>

<form action="csvimport.php" style="margin-left:50px;float: left;" method="post" enctype="multipart/form-data">
    <input type="file" name="vz_config_csv" accept=".csv"/>
    <input type="submit" value="提交"/>
    <br/><br/>

    <h4>凡例：</h4>

    <div style="width:80%">
        入居者(数字 5桁),活動/環境(数字 2桁),区分(数字 1桁),名称(文字列 20),優先順位(数字 1桁),活動解釈方法(数字 1桁),活動集計方法(数字 1桁),設置場所1(数字 2桁),項目種類1(数字
        1桁),データ解釈方法1(数字 1桁),色1(Hex英数字 #FFFFFF,明るさの場合#FFFFFF|#CCCCCC),設置場所2(数字 2桁),項目種類2(数字 1桁),データ解釈方法2(数字 1桁)
    </div>
</form>
</body>
</html>
