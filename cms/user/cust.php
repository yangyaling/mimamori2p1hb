<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */
//　被介護者一覧

include '../cmslib.php';

$loginUser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';

@$argpage = $_GET['page'];

$list = array();

if ($conn) {

//    $sql = "SELECT userid,ISNULL(G.[value],'') groupname,U.[value] usertypename,username,email,updatedate,yobi FROM (
//            SELECT userid,groupid,usertype,username,email,CONVERT(VARCHAR(10),updatedate,120) updatedate,yobi FROM AZW101_usermst) A
//            LEFT OUTER JOIN AZW110_classmst G ON A.groupid = G.code AND G.classcd='" . CLASS_GROUP . "'
//            LEFT OUTER JOIN AZW110_classmst U ON A.usertype = U.code AND U.classcd='" . CLASS_USER_TYPE . "'";
    $sql = "SELECT [custid],[facilitycd],[floorno],[roomno],[custname],[sex],[birthday],[address],[kakaritsuke],[health],[other],[updatetime],[updatename],[updatedate],[picpath],[picudatedate]
             FROM [AZW005_custmst]";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $list[$index] = array(
                'custid' => $row[0],
                'facilitycd' => $row[1],
                'floorno' => $row[2],
                'roomno' => $row[3],
                'custname' => $row[4],
                'sex' => $row[5],
                'birthday' => $row[6],
                'address' => $row[7],
                'kakaritsuke' => $row[8],
                'health' => $row[9],
                'other' => $row[10],
                'updatetime' => $row[11],
                'updatename' => $row[12],
                'updatedate' => $row[13],
                'picpath' => $row[14],
                'picudatedate' => $row[15]
            );

            $index = $index + 1;
        }
    } else {
        $arrReturn['code'] = '502';
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
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
            padding-bottom: 40px;
        }
    </style>
    <script type="text/javascript">
        $(function () {
            $('#addcust').click(function () {
                window.location.href = "addcust.php";
            });
            $("tr:odd").css("background-color", "#CCFFFF");
        });

        function editUser(userid) {
            window.location.href = "edituser.php?userid=" + userid;
        }
    </script>
</head>
<body>
<form class="form-inline definewidth m20" action="cust.php" method="get">
    <button type="button" class="btn btn-success" id="addcust">被介護者追加</button>
</form>
<?php //echo $key ?>
<table class="table table-bordered table-hover definewidth m10">
    <thead>
    <tr>
        <th>No.</th>
        <th></th>
        <th>ユーザID</th>
        <!--        <th>ユーザタイプ</th>-->
<!--        <th>グループ</th>-->
        <th>ユーザ名</th>
        <th>施設</th>
        <th>ルーム</th>
        <!--<th>ニック名</th>-->
        <!--<th>連絡先</th>-->
        <!--<th>パスワード</th>-->
<!--        <th>メール</th>-->
        <th>更新日付</th>
<!--        <th>備考</th>-->
        <!--<th>Zワークスメール</th>-->
        <!--<th>Zワークスパスワード</th>-->
    </tr>
    </thead>
    <?php if ($list) {
        $no = 1; ?>
        <?php foreach ($list as $v) { ?>
            <tr>
                <td><?= $no++; ?></td>
                <td>
                    <input type="button" class="btn btn-mini btn-block" value="編集"
                           onclick="editUser('<?= $v['userid'] ?>');"/>
                </td>
                <td><?= $v['custid'] ?></td>
                <td><?= $v['custname'] ?></td>
<!--                <td>--><?//= $v['groupname'] ?><!--</td>-->
                <td><?= $v['facilitycd'] ?></td>
                <td><?= $v['roomno'] ?></td>
                <!--<td><? /*= $v['nickname'] */ ?></td>-->
                <!--<td><? /*= $v['contact'] */ ?></td>-->
                <!--<td><? /*= $v['password'] */ ?></td>-->
<!--                <td>--><?//= $v['email'] ?><!--</td>-->
                <td><?= $v['updatedate'] ?></td>
<!--                <td>--><?//= $v['yobi'] ?><!--</td>-->
                <!--<td><? /*= $v['zworksemail'] */ ?></td>-->
                <!--<td><? /*= $v['zworkspassword'] */ ?></td>-->
            </tr>
        <?php } ?>
    <?php } ?>
</table>
</body>
</html>
