<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/16
 * Time: 15:29
 */

include '../cmslib.php';

//$loginuser = checkSession();

$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

$arrReturn = array();
$code = '200';
$errors = array();

@$isApi = $_POST['isapi'];
$gatewayId = $_POST['gatewayid'];
$nodeId = $_POST['nodeid'];
$deviceId = $_POST['deviceid'];
$zType = $_POST['ztype'];
$zName = $_POST['zname'];
$nName = $_POST['nname'];
$deviceType = $_POST['devicetype'];
$deviceTypeName = $_POST['devicetypename'];
$unit = $_POST['unit'];
$deviceClass = $_POST['deviceclass'];
$deviceClassName = $_POST['deviceclassname'];
$timestamp = $_POST['timestamp'];

if ($conn) {
    $sql = "SELECT deviceid FROM AZW137_zworksdevicemst WHERE deviceid='$deviceId'";
    $result = sqlsrv_query($conn, $sql);
    if (!sqlsrv_has_rows($result)) {
        $sql = "INSERT INTO AZW137_zworksdevicemst (deviceid,devicename,gatewayid,nodeid,devicetype,unit,timestamp,deviceclass,dorder)
                VALUES ('$deviceId','$nName','$gatewayId','$nodeId','$deviceType','$unit',NULL,'$deviceClass',$deviceId)";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '501';
            $errors = sqlsrv_errors();
        }
    } else {
        $sql = "UPDATE AZW137_zworksdevicemst SET gatewayid='$gatewayId',nodeid='$nodeId',devicename='$nName'
                WHERE deviceid='$deviceId'";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '502';
            $errors = sqlsrv_errors();
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    $arrReturn['code'] = $code;
    $arrReturn['errors'] = $errors;
    sendResponse(json_encode($arrReturn));
} else {
    if ($code == '200') {
        header("Location: device.php?gatewayid=$gatewayId&nodeid=$nodeId");
    } else {
        $deviceinfo = implode('-', array(
            $gatewayId,
            $nodeId,
            $deviceId,
            $zType,
            $zName,
            $nName,
            $deviceType,
            $deviceTypeName,
            $unit,
            $deviceClass,
            $deviceClassName,
            $timestamp
        ));
        header("Location: editdevice.php?errno=$code&deviceinfo=$deviceinfo");
    }
    exit();
}

?>