<?php
/**
 * 活動色更新処理。
 * User: NITIOSRD
 * Date: 2017/2/16
 * Time: 17:19
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

$userId0 = $_POST['userid0'];
$actionId = $_POST['actionid'];
$actionColor = $_POST['actioncolor'];

if ($conn) {
//    $userId0 = '00002';
//    $actionId = "01|1|2017-05-12 00:00:00";
//    $actionColor = '#FF56A7';

    if (!is_empty($userId0) && !is_empty($actionId) && !is_empty($actionColor)) {
        $parameters = explode('|', $actionId);

        $sql = "UPDATE AZW150_vzconfig SET color1 = '$actionColor'
                WHERE userid='$userId0' AND actionid='$parameters[0]' AND actionclass='$parameters[1]'";

        if (!sqlsrv_query($conn, $sql)) {
            $code = '502';
            $errors = sqlsrv_errors();
        }
    } else {
        $code = '501';
        $errors = array('パラメータ不正');
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>