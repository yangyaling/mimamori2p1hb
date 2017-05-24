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
$zName = $_POST['zname'];
$nName = $_POST['nname'];
$dName = $_POST['dname'];
$place = $_POST['place'];
$nType = $_POST['ntype'];

if ($conn) {
    $sql = "SELECT nodeid FROM AZW136_zworksnodemst WHERE nodeid='$nodeId'";
    $result = sqlsrv_query($conn, $sql);
    if (!sqlsrv_has_rows($result)) {
        $sql = "INSERT INTO AZW136_zworksnodemst (nodeid,nodename,gatewayid,displayname,nodetype,place,norder)
                VALUES ('$nodeId','$nName','$gatewayId','$dName','$nType','$place',$nodeId)";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '501';
            $errors = sqlsrv_errors();
        }
    } else {
        $sql = "UPDATE AZW136_zworksnodemst SET gatewayid='$gatewayId',nodename='$nName',displayname='$dName',place='$place',nodetype='$nType'
                WHERE nodeid='$nodeId'";
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
        header("Location: node.php?gatewayid=$gatewayId");
    } else {
        $nodeInfo = implode('-', array(
            $gatewayId,
            $nodeId,
            $zName,
            $nName,
            $dName,
            $place,
            $nType
        ));
        header("Location: editnode.php?errno=$code&nodeinfo=$nodeInfo");
    }
    exit();
}

?>