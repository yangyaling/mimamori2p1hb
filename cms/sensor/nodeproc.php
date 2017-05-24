<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include '../cmslib.php';
include 'sensor_common.php';

//$loginuser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['list'] = array();

@$isApi = $_POST['isapi'];
$gatewayId = $_POST['gatewayid'];

$list = array();

function getNITNodes($conn, $gatewayId)
{
    $nodes = array();

    $sql = "SELECT znm.nodeid,znm.nodename,znm.displayname,znm.place,cm.value FROM AZW136_zworksnodemst znm
            LEFT OUTER JOIN AZW110_classmst cm ON cm.classcd='" . CLASS_NODE_TYPE . "' AND cm.code=znm.nodetype
            WHERE znm.gatewayid='$gatewayId'";
    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        while ($row = sqlsrv_fetch_array($result)) {
            $nodes[$row[0]] = array(
                'nodeid' => $row[0],
                'nodename' => $row[1],
                'displayname' => $row[2],
                'place' => $row[3],
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
                $list[$index]['place'] = $nv['place'];
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
            $list[$index]['place'] = $nv['place'];
            $list[$index]['ntype'] = $nv['nodetype'];
            $list[$index]['hasdevices'] = 0;
            $index = $index + 1;
        }
    }

    return $list;
}

if ($isApi && $conn) {
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
    $nNodes = getNITNodes($conn, $gatewayId);

    $list = getDisplayNodes($sensorInfo[$gatewayId]['nodes'], $nNodes);
}

sqlsrv_close($conn);

$arrReturn['list'] = $list;

sendResponse(json_encode($arrReturn));
?>
