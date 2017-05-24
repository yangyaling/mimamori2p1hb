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

$list = array();

function getNITGateways($conn)
{
    $gatewaylist = array();

    $sql = "SELECT gatewayid,gatewayname FROM AZW135_zworksgatewaymst";
    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        while ($row = sqlsrv_fetch_array($result)) {
            $gatewaylist[$row[0]] = array(
                'gatewayid' => $row[0],
                'gatewayname' => $row[1]
            );
        }
    }

    return $gatewaylist;
}

function getDisplayGateways($zs, $ns)
{
    $list = array();
    $distlist = array();

    $index = 0;
    foreach ($zs as $zk => $zv) {
        $list[$index]['gatewayid'] = $zv['id'];
        $list[$index]['zname'] = $zv['name'];
        $list[$index]['status'] = $zv['status'];
        $list[$index]['nname'] = '';
        $list[$index]['iszwgateway'] = 1;
        if ($zv['nodes']) {
            $list[$index]['hasnodes'] = 1;
        } else {
            $list[$index]['hasnodes'] = 0;
        }
        foreach ($ns as $nk => $nv) {
            if ($zk == $nk) {
                $distlist[$nv['gatewayid']] = $nv['gatewayid'];
                $list[$index]['nname'] = $nv['gatewayname'];
                break;
            }
        }
        $index = $index + 1;
    }

    foreach ($ns as $nk => $nv) {
        if (!array_key_exists($nk, $distlist)) {
            $list[$index]['gatewayid'] = $nv['gatewayid'];
            $list[$index]['zname'] = '';
            $list[$index]['nname'] = $nv['gatewayname'];
            $list[$index]['iszwgateway'] = 0;
            $list[$index]['hasnodes'] = 0;
            $index = $index + 1;
        }
    }

    return $list;
}

if ($conn) {
    if (!session_id()) session_start();
    if ($_SESSION[SENSORINFO]) {
        $sensorinfo = $_SESSION[SENSORINFO];
    } else {
        //TODO 'aimi.f507@gmail.com' $loginuser->getZworksEmail()
        //ZWorksからセンサー情報を取得する
        unset($_SESSION[SENSORINFO]);
        $sensorinfo = getSensorInfo(getSessionId($conn, 'aimi.f507@gmail.com'));
        $_SESSION[SENSORINFO] = $sensorinfo;
    }

    //DBからGateway情報を取得する
    $ngateways = getNITGateways($conn);

    $list = getDisplayGateways($sensorinfo, $ngateways);
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
            $('#doSync').click(function () {
                if (confirm("Zworksからセンサー情報を一括取得して、\nデータベースに更新されることを行うか？\nデータベースの情報が全てクリアするので、ご確認ください。")) {
                    window.location.href = "dosync.php";
                }
            });
            $("tr[name^=tr_gw]").click(function () {
                if (!canClick) {
                    return false;
                }
                if (isEmpty($(this).children('td').find(':hidden').first().val() == "0")) {
                    var msg = "このゲートウエーがZworksにもう存在しません。";
                    alert(msg);
                } else if ($(this).children('td').find(':hidden').first().next().val() == "0") {
                    var msg = "このゲートウエーにノードが存在しません。";
                    alert(msg);
                } else {
                    canClick = false;
                    window.location.href = "node.php?gatewayid=" + $(this).children('td').first().next().next().text();
                }
            });
            $("tr:odd").css("background-color", "#CCFFFF");
        });

        function editGateway(e, gatewayid, zname) {
            if (!canClick) {
                return false;
            }
            if (isEmpty(zname)) {
                var msg = "このゲートウエーがZworksにもう配置されないが、修正できません。\n削除しますか？";
                if (confirm(msg)) {
                    canClick = false;
                    window.location.href = "deletegatewayproc.php?gatewayid=" + gatewayid;
                }
            } else {
                canClick = false;
                window.location.href = "editgateway.php?gatewayid=" + gatewayid + "&zname=" + zname;
            }
            e.stopPropagation();
        }
    </script>
</head>
<body>
<h2>ゲートウェイ情報一覧</h2>

<form class="form-inline definewidth m20" action="gateway.php" method="get">
    <button type="button" class="btn btn-success" id="doSync" name="doSync">同期化を行う</button>
</form>
<table class="table table-bordered table-hover definewidth m10">
    <thead>
    <tr>
        <th>No.</th>
        <th></th>
        <th>ゲートウェイID</th>
        <th>ZWorks ゲートウェイ名</th>
        <th>NIT ゲートウェイ名</th>
    </tr>
    </thead>
    <?php if ($list) {
        $no = 1; ?>
        <?php foreach ($list as $v) { ?>
            <tr name="tr_gw">
                <td><?= $no++; ?></td>
                <td>
                    <input type="hidden" name="iszwgateway" value="<?= ($v['iszwgateway']) ?>"/>
                    <input type="hidden" name="hasnodes" value="<?= $v['hasnodes'] ?>"/>
                    <input type="button" class="btn btn-mini btn-block" value="編集"
                           onclick="editGateway(event,'<?= $v['gatewayid'] ?>','<?= $v['zname'] ?>');"/>
                </td>
                <td><?= $v['gatewayid'] ?></td>
                <td><?= $v['zname'] ?></td>
                <td><?= $v['nname'] ?></td>
            </tr>
        <?php } ?>
    <?php } ?>
</table>
</body>
</html>
