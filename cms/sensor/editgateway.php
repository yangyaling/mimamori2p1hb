<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include '../cmslib.php';

@$isApi = $_POST['isapi'];
@$gatewayId = $_REQUEST['gatewayid'];
@$zName = $_REQUEST['zname'];
@$argUi = $_REQUEST["gatewayinfo"];

if ($isApi && !is_empty($isApi)) {
} else {
    $loginUser = checkSession();
}

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$code = "200";
$gatewayInfo = array();

function getGatewayInfoById($conn, $gatewayId, $zName)
{
    $sql = "SELECT gatewayname FROM AZW135_zworksgatewaymst WHERE gatewayid='$gatewayId'";
    $result = sqlsrv_query($conn, $sql);
    if ($result && $row = sqlsrv_fetch_array($result)) {
        $gatewayInfo = array(
            'gatewayid' => $gatewayId,
            'zname' => $zName,
            'nname' => $row[0]
        );
    } else {
        $gatewayInfo = array(
            'gatewayid' => $gatewayId,
            'zname' => $zName,
            'nname' => ''
        );
    }

    return $gatewayInfo;
}

if ($conn) {
    if ($argUi) {
        $row = explode('-', $argUi);
        $gatewayInfo = array(
            'gatewayid' => $row[0],
            'zname' => $row[1],
            'nname' => $row[2]
        );
    } else {
        $gatewayInfo = getGatewayInfoById($conn, $gatewayId, $zName);
    }
}

sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    $arrReturn = array();
    $arrReturn['code'] = $code;
    $arrReturn['gatewayinfo'] = $gatewayInfo;
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
                $('#btnsave').click(function () {
                    <?php if (is_empty($gatewayInfo['nname'])) { ?>
                    if (confirm("新規登録しますか？")) {
                        $('#form1').submit();
                    }
                    <?php } else { ?>
                    if (confirm("更新しますか？")) {
                        $('#form1').submit();
                    }
                    <?php } ?>
                });
                $('#backid').click(function () {
                    window.location.href = "gateway.php";
                });
            });
        </script>
    </head>
    <body>
    <?php
    @$errno = $_GET['errno'];
    if ($errno && !is_empty($errno)) {
        if ($errno == '501') {
            echo "<p class='error-msg'>ゲートウエー情報の登録が失敗しました。</p>";
        } else if ($errno == '502') {
            echo "<p class='error-msg'>ゲートウエー情報の更新が失敗しました。</p>";
        }
    }
    ?>
    <form id="form1" action="savegatewayproc.php" method="post" class="definewidth m20" target="_self">
        <input type="hidden" name="gatewayid" value="<?= $gatewayInfo['gatewayid'] ?>"/>
        <table class="table table-bordered table-hover definewidth m10">
            <tr>
                <td width="15%" class="tableleft">ゲートウェイID</td>
                <td><input type="text" disabled="disabled" value="<?= $gatewayInfo['gatewayid'] ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">ZWorks ゲートウェイ名</td>
                <td><input type="text" name="zname" readonly="true" value="<?= $gatewayInfo['zname'] ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">NIT ゲートウェイ名</td>
                <td><input type="text" name="nname" value="<?php if (is_empty($gatewayInfo['nname'])) {
                        echo $gatewayInfo['zname'];
                    } else {
                        echo $gatewayInfo['nname'];
                    } ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft"></td>
                <td>
                    <button type="button" class="btn btn-primary" name="btnsave"
                            id="btnsave"><?php if (is_empty($gatewayInfo['nname'])) { ?>登録<?php } else { ?>更新<?php } ?></button>
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
