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

$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['list'] = array();

@$isApi = $_POST['isapi'];

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
            $index = $index + 1;
        }
    }

    return $list;
}

if ($isApi && $conn) {
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

$arrReturn['list'] = $list;

sendResponse(json_encode($arrReturn));
?>
