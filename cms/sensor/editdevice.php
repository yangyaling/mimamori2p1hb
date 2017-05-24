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
@$nodeId = $_REQUEST['nodeid'];
@$deviceId = $_REQUEST['deviceid'];
@$zType = $_REQUEST['ztype'];
@$zName = $_REQUEST['zname'];
@$argUi = $_REQUEST["nodeinfo"];

if ($isApi && !is_empty($isApi)) {
} else {
    $loginUser = checkSession();
}

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$code = "200";
$deviceInfo = array();
$dtList = array();
$dcList = array();

function getDeviceInfoById($conn, $gatewayId, $nodeId, $deviceId, $zType, $zName)
{
    $sql = "SELECT devicename,devicetype,unit,deviceclass,timestamp FROM AZW137_zworksdevicemst
            WHERE gatewayid='$gatewayId' AND nodeid='$nodeId' AND deviceid='$deviceId'";
    $result = sqlsrv_query($conn, $sql);
    if ($result && $row = sqlsrv_fetch_array($result)) {
        $deviceInfo = array(
            'gatewayid' => $gatewayId,
            'nodeid' => $nodeId,
            'deviceid' => $deviceId,
            'ztype' => $zType,
            'zname' => $zName,
            'nname' => $row[0],
            'devicetype' => $row[1],
            'devicetypename' => '',
            'unit' => $row[2],
            'deviceclass' => $row[3],
            'deviceclassname' => '',
            'timestamp' => $row[4]
        );
    } else {
        $deviceInfo = array(
            'gatewayid' => $gatewayId,
            'nodeid' => $nodeId,
            'deviceid' => $deviceId,
            'ztype' => $zType,
            'zname' => $zName,
            'nname' => '',
            'devicetype' => CLASS_UNREGIST,
            'devicetypename' => CLASS_NAME_UNREGIST,
            'unit' => '',
            'deviceclass' => CLASS_UNREGIST,
            'deviceclassname' => CLASS_NAME_UNREGIST,
            'timestamp' => ''
        );
    }

    return $deviceInfo;
}

if ($conn) {
    if ($argUi) {
        $row = explode('-', $argUi);
        $deviceInfo = array(
            'gatewayid' => $row[0],
            'nodeid' => $row[1],
            'deviceid' => $row[2],
            'ztype' => $row[3],
            'zname' => $row[4],
            'nname' => $row[5],
            'devicetype' => $row[6],
            'devicetypename' => $row[7],
            'unit' => $row[8],
            'deviceclass' => $row[9],
            'deviceclassname' => $row[10],
            'timestamp' => $row[11]
        );
    } else {
        $deviceInfo = getDeviceInfoById($conn, $gatewayId, $nodeId, $deviceId, $zType, $zName);
    }
    $dtList = getCVList($conn, CLASS_DEVICE_TYPE);
    $dcList = getCVList($conn, CLASS_DEVICE_CLASS);
}

sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    $arrReturn = array();
    $arrReturn['code'] = $code;
    $arrReturn['deviceinfo'] = $deviceInfo;
    $arrReturn['dtlist'] = $dtList;
    $arrReturn['dclist'] = $dcList;
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
                    <?php if (is_empty($deviceInfo['nname'])) { ?>
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
                    window.location.href = "device.php?gatewayid=<?= $gatewayId ?>&nodeid=<?= $nodeId ?>";
                });
                $('#devicetype').change(function () {
                    if ($(this).val() != '1') {
                        toggleGroupid(true);
                    } else {
                        toggleGroupid(false);
                    }
                });
                $('#deviceclass').change(function () {
                    if ($(this).val() != '1') {
                        toggleGroupid(true);
                    } else {
                        toggleGroupid(false);
                    }
                });
            });
        </script>
    </head>
    <body>
    <?php
    @$errno = $_GET['errno'];
    if ($errno && !is_empty($errno)) {
        if ($errno == '501') {
            echo "<p class='error-msg'>デバイス情報の登録が失敗しました。</p>";
        } else if ($errno == '502') {
            echo "<p class='error-msg'>デバイス情報の更新が失敗しました。</p>";
        }
    }
    ?>
    <form id="form1" action="savedeviceproc.php" method="post" class="definewidth m20" target="_self">
        <input type="hidden" name="gatewayid" value="<?= $deviceInfo['gatewayid'] ?>"/>
        <input type="hidden" name="nodeid" value="<?= $deviceInfo['nodeid'] ?>"/>
        <input type="hidden" name="deviceid" value="<?= $deviceInfo['deviceid'] ?>"/>
        <table class="table table-bordered table-hover definewidth m10">
            <tr>
                <td width="15%" class="tableleft">デバイスID</td>
                <td><input type="text" disabled="disabled" value="<?= $deviceInfo['deviceid'] ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">ZWorks デバイス名</td>
                <td><input type="text" name="zname" readonly="true" value="<?= $deviceInfo['zname'] ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">ZWorks デバイスタイプ</td>
                <td><input type="text" name="ztype" readonly="true" value="<?= $deviceInfo['ztype'] ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">NIT デバイス名</td>
                <td><input type="text" name="nname" value="<?php if (is_empty($deviceInfo['nname'])) {
                        echo $deviceInfo['zname'];
                    } else {
                        echo $deviceInfo['nname'];
                    } ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">デバイスタイプ</td>
                <td>
                    <select id="devicetype" name="devicetype">
                        <option
                            value="未登録"<?php if ($deviceInfo['devicetype'] == '未登録') { ?> selected="selected" <?php } ?>>
                            未登録
                        </option>
                        <?php if ($dtList) { ?>
                            <?php foreach ($dtList as $dt) { ?>
                                <option value="<?= $dt['id'] ?>"
                                    <?php if ($deviceInfo['devicetype'] == $dt['id']) { ?> selected="selected" <?php } ?>><?= $dt['name'] ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">単位</td>
                <td><input type="text" name="unit" value="<?= $deviceInfo['unit'] ?>"/></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">最後更新時間</td>
                <td><?= $deviceInfo['timestamp'] ?></td>
            </tr>
            <tr>
                <td width="15%" class="tableleft">デバイスクラス</td>
                <td>
                    <select id="deviceclass" name="deviceclass">
                        <option
                            value="未登録"<?php if ($deviceInfo['deviceclass'] == '未登録') { ?> selected="selected" <?php } ?>>
                            未登録
                        </option>
                        <?php if ($dcList) { ?>
                            <?php foreach ($dcList as $dc) { ?>
                                <option value="<?= $dc['id'] ?>"
                                    <?php if ($deviceInfo['deviceclass'] == $dc['id']) { ?> selected="selected" <?php } ?>><?= $dc['name'] ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="15%" class="tableleft"></td>
                <td>
                    <button type="button" class="btn btn-primary" name="btnsave"
                            id="btnsave"><?php if (is_empty($deviceInfo['nname'])) { ?>登録<?php } else { ?>更新<?php } ?></button>
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
