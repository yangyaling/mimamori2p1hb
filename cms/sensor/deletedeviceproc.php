<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/16
 * Time: 15:29
 */

include '../cmslib.php';

//$loginuser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

@$isApi = $_POST['isapi'];
$gatewayId = $_POST['gatewayid'];
$nodeId = $_POST['nodeid'];
$deviceId = $_POST['deviceid'];

if (!$gatewayId) {
    $gatewayId = $_GET['gatewayid'];
}
if (!$nodeId) {
    $nodeId = $_GET['nodeid'];
}
if (!$deviceId) {
    $deviceId = $_GET['deviceid'];
}

if ($conn) {
    $sql = "DELETE FROM AZW137_zworksdevicemst WHERE deviceid='$deviceId'";
    $result = sqlsrv_query($conn, $sql);
    if (!$result) {
        $code = '502';
        $errors = sqlsrv_errors();
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
        header("Location: device.php");
    } else {
        $deviceInfo = implode('-', array(
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
        header("Location: editdevice.php?errno=$code&deviceinfo=$deviceInfo");
    }
    exit();
}

?>