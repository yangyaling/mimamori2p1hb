<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */
// 被介護者を追加

include '../cmslib.php';

$loginuser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';

@$argUi = $_GET["userinfo"];

$userInfo = array();
$groupList = array();

function getUserInfo($argUi)
{
    if ($argUi) {
        $row = explode('-', $argUi);
        $userInfo = array(
            'userid' => $row[0],
            'groupid' => $row[1],
            'usertype' => $row[2],
            'username' => $row[3],
            'nickname' => $row[4],
            'contact' => $row[5],
            'email' => $row[6],
            'yobi' => $row[7],
            'zworksemail' => $row[8],
            'zworkspassword' => $row[9]
        );
    } else {
        $userInfo = array(
            'userid' => '',
            'groupid' => '',
            'usertype' => '',
            'username' => '',
            'nickname' => '',
            'contact' => '',
            'email' => '',
            'yobi' => '',
            'zworksemail' => '',
            'zworkspassword' => ''
        );
    }

    return $userInfo;
}

if ($conn) {
    $userInfo = getUserInfo($argUi);

    $groupList = getCVList($conn, CLASS_GROUP);

    $userTypeList = getCVList($conn, CLASS_USER_TYPE);
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
            $('#btnsave').click(function () {
                if (confirm("登録しますか？")) {
                    var msg = "";
                    if ($('#userid').val() == '') {
                        msg += "ユーザIDが必須です。\n";
                    }
                    if ($('#password').val() == '') {
                        msg += "パスワードが必須です。\n";
                    }
                    if ($('#confirmpassword').val() == '') {
                        msg += "確認パスワードが必須です。\n";
                    }
                    if (msg == '' && $('#password').val() != $('#confirmpassword').val()) {
                        msg += "パスワードと確認パスワードが不正です。\n";
                    }
                    if (msg != "") {
                        alert(msg);
                        return false;
                    } else {
                        $('#form1').submit();
                    }
                }
            });
            $('#usertype').change(function () {
                if ($(this).val() == '0') {
                    toggleGroupid(true);
                } else {
                    toggleGroupid(false);
                }
            });
            $('#backid').click(function () {
                window.location.href = "user.php";
            });
            if ($('#usertype').val() == '0') {
                toggleGroupid(true);
            } else {
                toggleGroupid(false);
            }
        });

        function toggleGroupid(flg) {
            if (flg) {
                $("#tr_groupid").hide();
                $("#tr_zworksemail").hide();
                $("#tr_zworkspassword").hide();
            } else {
                $("#tr_groupid").show();
                $("#tr_zworksemail").show();
                $("#tr_zworkspassword").show();
            }
        }
    </script>
</head>
<body>
<?php
@$errno = $_GET['errno'];
if ($errno && !is_empty($errno)) {
    if ($errno == '511') {
        echo "<p class='error-msg'>ユーザIDが既に存在しました。</p>";
    } else if ($errno == '510') {
        echo "<p class='error-msg'>パスワードと確認パスワードが不正です。</p>";
    } else {
        echo "<p class='error-msg'>ユーザ情報の登録が失敗しました。</p>";
    }
}
?>
<form id="form1" action="adduserproc.php" method="post" class="definewidth m20" target="_self">
    <table class="table table-bordered table-hover definewidth m10">
        <tr>
            <td width="15%" class="tableleft">ユーザID</td>
            <td><input type="text" name="userid" value="<?= $userInfo['userid'] ?>" placeholder="必須項目"/></td>
        </tr>
        <tr>
            <td width="15%" class="tableleft">ユーザ名</td>
            <td><input type="text" name="username" value="<?= $userInfo['username'] ?>"/></td>
        </tr>
        <tr>
            <td width="15%" class="tableleft">パスワード</td>
            <td><input type="text" id="password" name="password" value="" placeholder="必須項目"/></td>
        </tr>
        <tr>
            <td width="15%" class="tableleft">パスワード確認</td>
            <td><input type="text" id="confirmpassword" name="confirmpassword" value="" placeholder="必須項目"/></td>
        </tr>
        <tr>
            <td width="15%" class="tableleft">ユーザタイプ</td>
            <td>
                <select id="usertype" name="usertype">
                    <?php if ($userTypeList) { ?>
                        <?php foreach ($userTypeList as $usertype) { ?>
                            <option value="<?= $usertype['id'] ?>"
                                <?php if ($userInfo['usertype'] == $usertype['id']) { ?> selected="selected" <?php } ?>><?= $usertype['name'] ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr id="tr_groupid">
            <td width="15%" class="tableleft">グループID</td>
            <td>
                <select name="groupid">
                    <?php if ($groupList) { ?>
                        <?php foreach ($groupList as $group) { ?>
                            <option value="<?= $group['id'] ?>"
                                <?php if ($userInfo['groupid'] == $group['id']) { ?> selected="selected" <?php } ?>><?= $group['name'] ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td width="15%" class="tableleft">ニック名</td>
            <td><input type="text" name="nickname" value="<?= $userInfo['nickname'] ?>"/></td>
        </tr>
        <tr>
            <td width="15%" class="tableleft">連絡先</td>
            <td><input type="text" name="contact" value="<?= $userInfo['contact'] ?>"/></td>
        </tr>
        <tr>
            <td width="15%" class="tableleft">メール</td>
            <td><input type="text" name="email" value="<?= $userInfo['email'] ?>"/></td>
        </tr>
        <tr>
            <td width="15%" class="tableleft">備考</td>
            <td><input type="text" name="yobi" value="<?= $userInfo['yobi'] ?>"/></td>
        </tr>
        <tr id="tr_zworksemail">
            <td width="15%" class="tableleft">Zワークスメール</td>
            <td><input type="text" name="zworksemail" value="<?= $userInfo['zworksemail'] ?>"/></td>
        </tr>
        <tr id="tr_zworkspassword">
            <td width="15%" class="tableleft">Zワークスパスワード</td>
            <td><input type="text" name="zworkspassword" value="<?= $userInfo['zworkspassword'] ?>"/></td>
        </tr>
        <tr>
            <td width="15%" class="tableleft"></td>
            <td>
                <button type="button" class="btn btn-primary" name="btnsave" id="btnsave">登録</button>
                &nbsp;&nbsp;
                <button type="button" class="btn btn-success" name="backid" id="backid">戻る</button>
            </td>
        </tr>
    </table>
</form>
</body>
</html>
