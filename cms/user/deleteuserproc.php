<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 15:37
 */

include '../cmslib.php';

//$loginuser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';

@$isApi = $_POST['isapi'];
$userId = $_POST['userid'];

if ($conn) {
    $sql = "select password FROM AZW101_usermst WHERE userid='$userId'";
    $result = sqlsrv_query($conn, $sql);
    if (sqlsrv_has_rows($result)) {
        $sql = "DELETE FROM AZW101_usermst WHERE userid='$userId'";

        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $arrReturn['code'] = '501';
        }
    } else {
        $arrReturn['code'] = '521';
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    sendResponse(json_encode($arrReturn));
} else {
    $code = $arrReturn['code'];
    if ($code == '200') {
        header("Location: user.php");
    } else if ($code == '521') {
        header("Location: edituser.php?errno=$code&userid=$userId");
    } else {
        header("Location: edituser.php?errno=2&userid=$userId");
    }
    exit();
}

?>