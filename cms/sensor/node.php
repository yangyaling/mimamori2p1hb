<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include '../cmslib.php';
include 'sensor_common.php';

$loginUser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

@$gatewayId = $_GET['gatewayid'];

$list = array();

function getNITNodes($conn, $gatewayId)
{
    $nodes = array();

    $sql = "SELECT znm.nodeid,znm.nodename,znm.displayname,cmp.value,cmt.value FROM AZW136_zworksnodemst znm
            LEFT OUTER JOIN AZW110_classmst cmt ON cmt.classcd='" . CLASS_NODE_TYPE . "' AND cmt.code=znm.nodetype
            LEFT OUTER JOIN AZW110_classmst cmp ON cmp.classcd='" . CLASS_NODE_PLACE . "' AND cmp.code=znm.place
            WHERE znm.gatewayid='$gatewayId'";
    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        while ($row = sqlsrv_fetch_array($result)) {
            $nodes[$row[0]] = array(
                'nodeid' => $row[0],
                'nodename' => $row[1],
                'displayname' => $row[2],
                'placename' => $row[3],
                'nodetype' => $row[4]
            );
        }
    }

    return $nodes;
}

function getDisplayNodes($zs, $ns)
{
    $list = array();
    $distList = array();

    $index = 0;
    foreach ($zs as $zk => $zv) {
        $list[$index]['nodeid'] = $zv['id'];
        $list[$index]['zname'] = $zv['name'];
        $list[$index]['nname'] = '';
        $list[$index]['dname'] = '';
        $list[$index]['ntype'] = '';
        $list[$index]['iszwnode'] = 1;
        if ($zv['devices']) {
            $list[$index]['hasdevices'] = 1;
        } else {
            $list[$index]['hasdevices'] = 0;
        }
        foreach ($ns as $nk => $nv) {
            if ($zk == $nk) {
                $distList[$nv['nodeid']] = $nv['nodeid'];
                $list[$index]['nname'] = $nv['nodename'];
                $list[$index]['dname'] = $nv['displayname'];
                $list[$index]['placename'] = $nv['placename'];
                $list[$index]['ntype'] = $nv['nodetype'];
                break;
            }
        }
        $index = $index + 1;
    }

    foreach ($ns as $nk => $nv) {
        if (!array_key_exists($nk, $distList)) {
            $list[$index]['nodeid'] = $nv['nodeid'];
            $list[$index]['zname'] = '';
            $list[$index]['nname'] = $nv['nodename'];
            $list[$index]['dname'] = $nv['displayname'];
            $list[$index]['placename'] = $nv['placename'];
            $list[$index]['ntype'] = $nv['nodetype'];
            $list[$index]['iszwnode'] = 0;
            $list[$index]['hasdevices'] = 0;
            $index = $index + 1;
        }
    }

    return $list;
}

if ($conn) {
    if (!session_id()) session_start();
    $sensorInfo = array();
    if ($_SESSION[SENSORINFO]) {
        $sensorInfo = $_SESSION[SENSORINFO];
    } else {
        //TODO 'aimi.f507@gmail.com' $loginuser->getZworksEmail()
        //ZWorksからセンサー情報を取得する
        unset($_SESSION[SENSORINFO]);
        $sensorInfo = getSensorInfo(getSessionId($conn, 'aimi.f507@gmail.com'));
        $_SESSION[SENSORINFO] = $sensorInfo;
    }

    //DBからNode情報を取得する
    $nitNodes = getNITNodes($conn, $gatewayId);

    $list = getDisplayNodes($sensorInfo[$gatewayId]['nodes'], $nitNodes);
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
                window.location.href = "gateway.php";
            });
            $("tr[name^=tr_node]").click(function () {
                if (!canClick) {
                    return false;
                }
                if (isEmpty($(this).children('td').find(':hidden').first().val() == "0")) {
                    var msg = "このノードがZworksにもう配置されません。";
                    alert(msg);
                } else if ($(this).children('td').find(':hidden').first().next().val() == "0") {
                    var msg = "このノードにデバイスがありません。";
                    alert(msg);
                } else {
                    canClick = false;
                    window.location.href = "device.php?gatewayid=" + $("#gatewayid").val()
                    + "&nodeid=" + $(this).children('td').first().next().next().text();
                }
            });
            $("tr:odd").css("background-color", "#CCFFFF");
        });

        function editNode(e, gatewayid, nodeid, zname) {
            if (!canClick) {
                return false;
            }
            if (isEmpty(zname)) {
                var msg = "このノードがZworksにもう配置されないが、修正できません。\n削除しますか？";
                if (confirm(msg)) {
                    canClick = false;
                    window.location.href = "deletenodeproc.php?gatewayid=" + gatewayid + "&nodeid=" + nodeid;
                }
            } else {
                canClick = false;
                window.location.href = "editnode.php?gatewayid=" + gatewayid + "&nodeid=" + nodeid + "&zname=" + zname;
            }
            e.stopPropagation();
        }
    </script>
</head>
<body>
<h2>ノード情報一覧</h2>

<form class="form-inline definewidth m20" action="gateway.php" method="get">
    <button type="button" class="btn btn-success" id="backid" name="backid">ゲートウエー一覧に戻る</button>
</form>
<input type="hidden" id="gatewayid" name="gatewayid" value="<?= $gatewayId ?>"/>
<table class="table table-bordered table-hover definewidth m10">
    <thead>
    <tr>
        <th>No.</th>
        <th></th>
        <th>ノードID</th>
        <th>ZWorks ノード名</th>
        <th>NIT ノード名</th>
        <th>表示名称</th>
        <th>ノードタイプ</th>
        <th>置き場所</th>
    </tr>
    </thead>
    <?php if ($list) {
        $no = 1; ?>
        <?php foreach ($list as $v) { ?>
            <tr name="tr_node">
                <td><?= $no++; ?></td>
                <td>
                    <input type="hidden" name="iszwnode" value="<?= ($v['iszwnode']) ?>"/>
                    <input type="hidden" name="hasdevices" value="<?= ($v['hasdevices']) ?>"/>
                    <input type="button" class="btn btn-mini btn-block" value="編集"
                           onclick="editNode(event,'<?= $gatewayId ?>','<?= $v['nodeid'] ?>','<?= $v['zname'] ?>');"/>
                </td>
                <td><?= $v['nodeid'] ?></td>
                <td><?= $v['zname'] ?></td>
                <td><?= $v['nname'] ?></td>
                <td><?= $v['dname'] ?></td>
                <td><?= $v['ntype'] ?></td>
                <td><?= $v['placename'] ?></td>
            </tr>
        <?php } ?>
    <?php } ?>
</table>
</body>
</html>
