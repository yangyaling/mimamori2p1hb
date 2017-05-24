<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/14
 * Time: 17:29
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
$zName = $_POST['zname'];
$nName = $_POST['nname'];

if ($conn) {
    $sql = "SELECT gatewayid FROM AZW135_zworksgatewaymst WHERE gatewayid='$gatewayId'";
    $result = sqlsrv_query($conn, $sql);
    if (!sqlsrv_has_rows($result)) {
        //TODO 'aimi.f507@gmail.com' $loginuser->getZworksEmail()
        $sql = "INSERT INTO AZW135_zworksgatewaymst (gatewayid,gatewayname,adminemail,gworder)
                VALUES ('$gatewayId','$nName','aimi.f507@gmail.com',$gatewayId)";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '501';
            $errors = sqlsrv_errors();
        }
    } else {
        $sql = "UPDATE AZW135_zworksgatewaymst SET gatewayname='$nName' WHERE gatewayid='$gatewayId'";
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
        header("Location: gateway.php");
    } else {
        $gatewayinfo = implode('-', array(
            $gatewayId,
            $zName,
            $nName
        ));
        header("Location: editgateway.php?errno=$code&gatewayinfo=$gatewayinfo");
    }
    exit();
}

?>