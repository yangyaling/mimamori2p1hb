<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$errors = array();
//$code = '200';

$cd = $_POST['cd'];

//    $cd = '04';

if ($conn && !is_empty($cd)) {
    $sql = "SELECT TOP 1 cm.code,sr.displayname FROM AZW010_nodelocationmst cm 
            LEFT OUTER JOIN AZW009_serialrelation sr ON cm.code=sr.displayname
            WHERE cm.code='$cd' AND sr.displayname IS NOT NULL";
    $result = sqlsrv_query($conn, $sql);

    if (!sqlsrv_has_rows($result)) {
        $sql = "DELETE FROM AZW010_nodelocationmst WHERE code='$cd'";

        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '501';
            $errors = sqlsrv_errors();
        }
    } else {
        $code = '502';
        $errors = array('設置情報（他機能）にて使用されている場合、削除は不可');
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