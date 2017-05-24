<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include '../cmslib.php';
include 'sensor_common.php';

$loginuser = checkSession();

$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

@$gatewayid = $_GET['gatewayid'];
@$nodeid = $_GET['nodeid'];

$list = array();

function getNITDevices($conn, $gatewayid, $nodeid)
{
    $devices = array();

    $sql = "SELECT D.deviceid,D.devicename,D.devicetype,T.[value] dtname,D.unit,
            D.deviceclass,T.[value] dcname,D.timestamp FROM AZW137_zworksdevicemst D
            LEFT OUTER JOIN AZW110_classmst C ON C.classcd='" . CLASS_DEVICE_CLASS . "' AND C.code=D.deviceclass
            LEFT OUTER JOIN AZW110_classmst T ON T.classcd='" . CLASS_DEVICE_TYPE . "' AND T.code=D.devicetype
            WHERE gatewayid='$gatewayid' AND nodeid='$nodeid'";
    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        while ($row = sqlsrv_fetch_array($result)) {
            $devices[$row[0]] = array(
                'deviceid' => $row[0],
                'devicename' => $row[1],
                'devicetype' => $row[2],
                'devicetypename' => $row[3],
                'unit' => $row[4],
                'deviceclass' => $row[5],
                'deviceclassname' => $row[6],
                'timestamp' => $row[7]
            );
        }
    }

    return $devices;
}

function getDisplayNodes($zs, $ns)
{
    $list = array();
    $distlist = array();

    $index = 0;
    foreach ($zs as $zk => $zv) {
        $list[$index]['deviceid'] = $zv['id'];
        $list[$index]['zname'] = $zv['name'];
        $list[$index]['nname'] = '';
        $list[$index]['ztype'] = $zv['type'];
        $list[$index]['ntype'] = '';
        $list[$index]['ntypename'] = '';
        foreach ($ns as $nk => $nv) {
            if ($zk == $nk) {
                $distlist[$nv['deviceid']] = $nv['deviceid'];
                $list[$index]['nname'] = $nv['devicename'];
                $list[$index]['ntype'] = $nv['devicetype'];
                $list[$index]['ntypename'] = $nv['devicetypename'];
                break;
            }
        }
        $index = $index + 1;
    }

    foreach ($ns as $nk => $nv) {
        if (!array_key_exists($nk, $distlist)) {
            $list[$index]['deviceid'] = $nv['deviceid'];
            $list[$index]['zname'] = '';
            $list[$index]['nname'] = $nv['devicename'];
            $list[$index]['ztype'] = '';
            $list[$index]['ntype'] = $nv['devicetype'];
            $list[$index]['ntypename'] = $nv['devicetypename'];
            $index = $index + 1;
        }
    }

    return $list;
}

if ($conn) {
    if (!session_id()) session_start();
    $sensorinfo = array();
    if ($_SESSION[SENSORINFO]) {
        $sensorinfo = $_SESSION[SENSORINFO];
    } else {
        //TODO 'aimi.f507@gmail.com' $loginuser->getZworksEmail()
        //ZWorksからセンサー情報を取得する
        unset($_SESSION[SENSORINFO]);
        $sensorinfo = getSensorInfo(getSessionId($conn, 'aimi.f507@gmail.com'));
        $_SESSION[SENSORINFO] = $sensorinfo;
    }

    //DBからDevice情報を取得する
    $ndevices = getNITDevices($conn, $gatewayid, $nodeid);

    $list = getDisplayNodes($sensorinfo[$gatewayid]['nodes'][$nodeid]['devices'], $ndevices);
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
        var canClick = true;
        $(function () {
            $('#backid').click(function () {
                window.location.href = "gateway.php?gatewayid=<?= $gatewayid ?>";
            });
            $('#backid2').click(function () {
                window.location.href = "node.php?gatewayid=<?= $gatewayid ?>&nodeid=<?= $nodeid ?>";
            });
            $("tr:odd").css("background-color", "#CCFFFF");
        });

        function editDevice(e, gatewayid, nodeid, deviceid, ztype, zname) {
            if (!canClick) {
                return false;
            }
            if (isEmpty(ztype)) {
                var msg = "このデバイスがZworksにもう配置されないが、修正できません。\n削除しますか？";
                if (confirm(msg)) {
                    canClick = false;
                    window.location.href = "deletedeviceproc.php?gatewayid=" + gatewayid + "&nodeid=" + nodeid + "&deviceid=" + deviceid;
                }
            } else {
                canClick = false;
                window.location.href = "editdevice.php?gatewayid=" + gatewayid + "&nodeid=" + nodeid
                + "&deviceid=" + deviceid + "&ztype=" + ztype + "&zname=" + zname;
            }
            e.stopPropagation();
        }
    </script>
</head>
<body>
<h2>デバイス情報一覧</h2>

<form class="form-inline definewidth m20" action="node.php" method="get">
    <button type="button" class="btn btn-success" id="backid" name="backid">ゲートウエー一覧に戻る</button>
    <button type="button" class="btn btn-success" id="backid2" name="backid2">ノード一覧に戻る</button>
</form>
<input type="hidden" id="gatewayid" name="gatewayid" value="<?= $gatewayid ?>"/>
<input type="hidden" id="nodeid" name="nodeid" value="<?= $nodeid ?>"/>
<table class="table table-bordered table-hover definewidth m10">
    <thead>
    <tr>
        <th>No.</th>
        <th></th>
        <th>デバイスID</th>
        <th>ZWorks デバイスタイプ</th>
        <th>ZWorks デバイス名</th>
        <th>NIT デバイスタイプ</th>
        <th>NIT デバイス名</th>
    </tr>
    </thead>
    <?php if ($list) {
        $no = 1; ?>
        <?php foreach ($list as $v) { ?>
            <tr name="tr_device">
                <td><?= $no++; ?></td>
                <td>
                    <input type="button" class="btn btn-mini btn-block" value="編集"
                           onclick="editDevice(event,'<?= $gatewayid ?>','<?= $nodeid ?>','<?= $v['deviceid'] ?>','<?= $v['ztype'] ?>','<?= $v['zname'] ?>');"/>
                </td>
                <td><?= $v['deviceid'] ?></td>
                <td><?= $v['ztype'] ?></td>
                <td><?= $v['zname'] ?></td>
                <td><?= $v['ntypename'] ?></td>
                <td><?= $v['nname'] ?></td>
            </tr>
        <?php } ?>
    <?php } ?>
</table>
</body>
</html>
