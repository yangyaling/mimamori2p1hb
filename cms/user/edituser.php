<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include '../cmslib.php';

@$isApi = $_POST['isapi'];
$userId = $_REQUEST['userid'];

if ($isApi && !is_empty($isApi)) {
} else {
    $loginUser = checkSession();
}

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$code = '200';

$userInfo = array();
$groupList = array();
$userTypeList = array();

function getUserInfo($conn, $userId)
{
    $userInfo = array();

    $sql = "SELECT userid,groupid,usertype,username,nickname,contact,password,email,convert(varchar(10),updatedate,120) updatedate,
            yobi,zworksemail,zworkspassword FROM AZW101_usermst where userid='$userId'";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $row = sqlsrv_fetch_array($result);
        if ($row) {
            $userInfo = array(
                'userid' => $row[0],
                'groupid' => $row[1],
                'usertype' => $row[2],
                'username' => $row[3],
                'nickname' => $row[4],
                'contact' => $row[5],
                'password' => $row[6],
                'email' => $row[7],
                'updatedate' => $row[8],
                'yobi' => $row[9],
                'zworksemail' => $row[10],
                'zworkspassword' => $row[11]
            );
        }
    }

    return $userInfo;
}

if ($conn) {
    $userInfo = getUserInfo($conn, $userId);

    $groupList = getCVList($conn, CLASS_GROUP);

    $userTypeList = getCVList($conn, CLASS_USER_TYPE);
} else {
    $code = '500';
}

sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    $arrReturn = array();
    $arrReturn['code'] = $code;
    $arrReturn['userinfo'] = $userInfo;
    $arrReturn['grouplist'] = $groupList;
    $arrReturn['usertypelist'] = $userTypeList;
    sendResponse(json_encode($arrReturn));
} else {
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
                $('#btndelete').click(function () {
                    if (confirm("削除しますか？")) {
                        $("#form1").attr("action", "deleteuserproc.php");
                        $('#form1').submit();
                    }
                });
                $('#btnsave').click(function () {
                    if (confirm("更新しますか？")) {
                        if ($('#editpwdflg').prop("checked")) {
                            var msg = "";
                            if ($('#password').val() == '') {
                                msg += "旧パスワードが必須です。\n";
                            }
                            if ($('#newpassword').val() == '') {
                                msg += "新パスワードが必須です。\n";
                            }
                            if ($('#confirmpassword').val() == '') {
                                msg += "確認パスワードが必須です。\n";
                            }
                            if (msg == '' && $('#newpassword').val() != $('#confirmpassword').val()) {
                                msg += "新パスワードと確認パスワードが不正です。\n";
                            }
                            if (msg != "") {
                                alert(msg);
                                return false;
                            }
                        }
                        $('#form1').submit();
                    }
                });
                $('#backid').click(function () {
                    window.location.href = "user.php";
                });
                <?php if ($userInfo['usertype'] == '0') { ?>
                toggleGroupid(true);
                <?php } else {?>
                toggleGroupid(false);
                <?php }?>
                toggleEditPwd(true);
                $('#editpwdflg').click(function () {
                    if ($('#editpwdflg').prop("checked")) {
                        toggleEditPwd(false);
                    } else {
                        toggleEditPwd(true);
                    }
                });
                $('#usertype').change(function () {
                    if ($(this).val() == '0') {
                        toggleGroupid(true);
                    } else {
                        toggleGroupid(false);
                    }
                });
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

            function toggleEditPwd(flg) {
                if (flg) {
                    $("tr[name='tr_editpwd']").hide();
                } else {
                    $("tr[name='tr_editpwd']").show();
                }
            }
        </script>
    </head>
    <body>
    <?php
    @$errno = $_GET['errno'];
    if ($errno && !is_empty($errno)) {
        if ($errno == '1') {
            echo "<p class='error-msg'>ユーザ情報の更新が失敗しました。</p>";
        } else if ($errno == '2') {
            echo "<p class='error-msg'>ユーザ情報の削除が失敗しました。</p>";
        } else if ($errno == '511') {
            echo "<p class='error-msg'>旧パスワードが不正です。</p>";
        } else if ($errno == '510') {
            echo "<p class='error-msg'>新パスワードと確認パスワードが不正です。</p>";
        } else if ($errno == '521') {
            echo "<p class='error-msg'>ユーザ情報が既に削除しました。</p>";
        }
    }
    ?>
    <form id="form1" action="edituserproc.php" method="post" class="definewidth m20" target="_self">
        <input type="hidden" name="userid" value="<?= $userInfo['userid'] ?>"/>
        <table class="table table-bordered table-hover definewidth m10">
            <tr>
                <td width="15%" class="tableleft">ユーザID</td>
                <td><input type="text" disabled="disabled" value="<?= $userInfo['userid'] ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">ユーザ名</td>
                <td><input type="text" name="username" value="<?= $userInfo['username'] ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">パスワード修正</td>
                <td><label><input type="checkbox" id="editpwdflg" name="editpwdflg" value="1">パスワード修正</label></td>
            </tr>
            <tr name="tr_editpwd">
                <td width="15%" class="tableleft">旧パスワード</td>
                <td><input type="text" id="password" name="password" value=""/></td>
            </tr>
            <tr name="tr_editpwd">
                <td width="15%" class="tableleft">新パスワード</td>
                <td><input type="text" id="newpassword" name="newpassword" value=""/></td>
            </tr>
            <tr name="tr_editpwd">
                <td width="15%" class="tableleft">新パスワード確認</td>
                <td><input type="text" id="confirmpassword" name="confirmpassword" value=""/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">ユーザタイプ</td>
                <td>
                    <input type="hidden" name="usertype" value="<?= $userInfo['usertype'] ?>"/>
                    <?php if ($userTypeList) { ?>
                        <?php foreach ($userTypeList as $usertype) { ?>
                            <?php
                            if ($userInfo['usertype'] == $usertype['id']) {
                                echo $usertype['name'];
                                break;
                            }
                            ?>
                        <?php } ?>
                    <?php } ?>
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
                    <button type="button" class="btn btn-primary" name="btnsave" id="btnsave">更新</button>
                    &nbsp;&nbsp;
                    <button type="button" class="btn btn-primary" name="btndelete" id="btndelete">削除</button>
                    &nbsp;&nbsp;
                    <button type="button" class="btn btn-success" name="backid" id="backid">戻る</button>
                </td>
            </tr>
        </table>
    </form>
    </body>
    </html>
<?php
}
?>
