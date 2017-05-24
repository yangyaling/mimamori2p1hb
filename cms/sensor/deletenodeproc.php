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

if (!$gatewayId) {
    $gatewayId = $_GET['gatewayid'];
}
if (!$nodeId) {
    $nodeId = $_GET['nodeid'];
}

if ($conn && sqlsrv_begin_transaction($conn)) {
    $sql = "DELETE FROM AZW137_zworksdevicemst WHERE nodeid='$nodeId'";
    $result = sqlsrv_query($conn, $sql);
    if (!$result) {
        $code = '501';
        $errors = sqlsrv_errors();
    }

    $sql = "DELETE FROM AZW136_zworksnodemst WHERE nodeid='$nodeId'";
    $result = sqlsrv_query($conn, $sql);
    if (!$result) {
        $code = '502';
        $errors = sqlsrv_errors();
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

if ($code == '200') {
    if (!sqlsrv_commit($conn)) {
        $code = '506';
        sqlsrv_rollback($conn);
    }
} else {
    $code = '505';
    sqlsrv_rollback($conn);
}

sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    $arrReturn['code'] = $code;
    $arrReturn['errors'] = $errors;
    sendResponse(json_encode($arrReturn));
} else {
    if ($code == '200') {
        header("Location: node.php");
    } else {
        $nodeinfo = implode('-', array(
            $gatewayId,
            $nodeId,
            $zName,
            $nName,
            $dName
        ));
        header("Location: editnode.php?errno=$code&nodeinfo=$nodeinfo");
    }
    exit();
}

?>