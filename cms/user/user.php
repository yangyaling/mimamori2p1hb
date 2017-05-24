<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include '../cmslib.php';

$loginUser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';

@$argpage = $_GET['page'];

$list = array();

if ($conn) {
//    // count
//    $sql = "SELECT count(1) cnt FROM AZW101_usermst";
//
//    $result = sqlsrv_query($conn, $sql);
//    if ($result) {
//        $row = sqlsrv_fetch_array($result);
//        $count = $row[0];
//        $Page_size = 10;
//        $page_count = ceil($count / $Page_size);
//
//        $init = 1;
//        $page_len = 7;
//        $max_p = $page_count;
//        $pages = $page_count;
//
//        if (is_empty($argpage) || $argpage < 0) {
//            $page = 1;
//        } else {
//            $page = $argpage;
//        }
//
//        $offset = $Page_size * ($page - 1);

//        //info
//        $sql = "SELECT TOP $Page_size userid,ISNULL(G.[value],'') groupname,U.[value] usertypename,username,email,
//                CONVERT(VARCHAR(10),updatedate,120) updatedate,yobi FROM (SELECT ROW_NUMBER() OVER (ORDER BY id) rownumber,
//                userid,groupid,usertype,username,email,CONVERT(VARCHAR(10),updatedate,120) updatedate,yobi FROM AZW101_usermst) A
//                LEFT OUTER JOIN AZW110_classmst G ON A.groupid = G.code AND G.classcd='" . CLASS_GROUP . "'
//                LEFT OUTER JOIN AZW110_classmst U ON A.usertype = U.code AND U.classcd='" . CLASS_USER_TYPE . "'
//                WHERE rownumber > $offset";

    $sql = "SELECT userid,ISNULL(G.[value],'') groupname,U.[value] usertypename,username,email,updatedate,yobi FROM (
            SELECT userid,groupid,usertype,username,email,CONVERT(VARCHAR(10),updatedate,120) updatedate,yobi FROM AZW101_usermst) A
            LEFT OUTER JOIN AZW110_classmst G ON A.groupid = G.code AND G.classcd='" . CLASS_GROUP . "'
            LEFT OUTER JOIN AZW110_classmst U ON A.usertype = U.code AND U.classcd='" . CLASS_USER_TYPE . "'";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $list[$index] = array(
                'userid' => $row[0],
                'groupname' => $row[1],
                'usertypename' => $row[2],
                'username' => $row[3],
                'email' => $row[4],
                'updatedate' => $row[5],
                'yobi' => $row[6]
            );

            $index = $index + 1;
        }
    } else {
        $arrReturn['code'] = '502';
    }
//    } else {
//        $arrReturn['code'] = '501';
//    }

//    $page_len = ($page_len % 2) ? $page_len : $pagelen + 1;
//    $pageOffset = ($page_len - 1) / 2;
//
//    $key = '<div class="pagination definewidth m20">';
//    if ($page != 1) {
//        $key .= "<a class='prev' href=\"" . $_SERVER['PHP_SELF'] . "?page=1\">First</a> ";
//        $key .= "<a class='prev' href=\"" . $_SERVER['PHP_SELF'] . "?page=" . ($page - 1) . "\">Prev</a>";
//    } else {
//        $key .= "<a disabled='true' class='prev'>First</a>";
//        $key .= "<a disabled='true' class='prev'>Prev</a>";
//    }
//    if ($pages > $page_len) {
//        if ($page <= $pageOffset) {
//            $init = 1;
//            $max_p = $page_len;
//        } else {
//            if ($page + $pageOffset >= $pages + 1) {
//                $init = $pages - $page_len + 1;
//            } else {
//                $init = $page - $pageOffset;
//                $max_p = $page + $pageOffset;
//            }
//        }
//    }
//    for ($i = $init; $i <= $max_p; $i++) {
//        if ($i == $page) {
//            $key .= "<span class='current'>$i</span>";
//        } else {
//            $key .= "<a href=\"" . $_SERVER['PHP_SELF'] . "?page=" . $i . "\">" . $i . "</a>";
//        }
//    }
//    if ($page != $pages) {
//        $key .= "<a class='next' href=\"" . $_SERVER['PHP_SELF'] . "?page=" . ($page + 1) . "\">Next</a> ";
//        $key .= "<a class='next' href=\"" . $_SERVER['PHP_SELF'] . "?page={$pages}\">Last</a>";
//    } else {
//        $key .= "<a disabled='true' class='next'>Next</a>";
//        $key .= "<a disabled='true' class='next'>Last</a>";
//    }
//    $key .= "<span class='current'>総計 $pages ページの $page ページ目</span>";
//    $key .= '</div>';
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
            $('#adduser').click(function () {
                window.location.href = "adduser.php";
            });
            $("tr:odd").css("background-color", "#CCFFFF");
        });

        function editUser(userid) {
            window.location.href = "edituser.php?userid=" + userid;
        }
    </script>
</head>
<body>
<form class="form-inline definewidth m20" action="user.php" method="get">
    <button type="button" class="btn btn-success" id="adduser">ユーザ追加</button>
</form>
<?php //echo $key ?>
<table class="table table-bordered table-hover definewidth m10">
    <thead>
    <tr>
        <th>No.</th>
        <th></th>
        <th>ユーザID</th>
        <th>ユーザタイプ</th>
        <th>グループ</th>
        <th>ユーザ名</th>
        <!--<th>ニック名</th>-->
        <!--<th>連絡先</th>-->
        <!--<th>パスワード</th>-->
        <th>メール</th>
        <th>更新日付</th>
        <th>備考</th>
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
                <td><?= $v['userid'] ?></td>
                <td><?= $v['usertypename'] ?></td>
                <td><?= $v['groupname'] ?></td>
                <td><?= $v['username'] ?></td>
                <!--<td><? /*= $v['nickname'] */ ?></td>-->
                <!--<td><? /*= $v['contact'] */ ?></td>-->
                <!--<td><? /*= $v['password'] */ ?></td>-->
                <td><?= $v['email'] ?></td>
                <td><?= $v['updatedate'] ?></td>
                <td><?= $v['yobi'] ?></td>
                <!--<td><? /*= $v['zworksemail'] */ ?></td>-->
                <!--<td><? /*= $v['zworkspassword'] */ ?></td>-->
            </tr>
        <?php } ?>
    <?php } ?>
</table>
</body>
</html>
