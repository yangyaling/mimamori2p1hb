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

$arrReturn['code'] = '200';
$arrReturn['list'] = array();

@$isApi = $_POST['isapi'];
@$gatewayid = $_POST['gatewayid'];
@$nodeid = $_POST['nodeid'];

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

function getDisplayDevices($zs, $ns)
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

if ($isApi && $conn) {
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

    $list = getDisplayDevices($sensorinfo[$gatewayid]['nodes'][$nodeid]['devices'], $ndevices);
}

sqlsrv_close($conn);

$arrReturn['list'] = $list;

sendResponse(json_encode($arrReturn));
?>
