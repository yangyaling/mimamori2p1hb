<?php
/**
 * 通知状態更新処理。
 * User: NITIOSRD
 * Date: 2017/2/16
 * Time: 17:19
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$userId0 = $_POST['userid0'];

if ($conn) {
//    $userId0 = '00002';

    if (!is_empty($userId0)) {
        $sql = "UPDATE AZW152_vznoticetbl SET status = '1'
                WHERE senduser='$userId0' AND status='0' AND noticetype='S'";
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