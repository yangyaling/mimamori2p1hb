<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

$companyCd = $_POST['companycd'];

//$companyCd = '1111';
if ($conn && !is_empty($companyCd)) {
    $sql = "SELECT 1 FROM AZW001_companymst WHERE companycd='$companyCd'";

    if (sqlsrv_has_rows(sqlsrv_query($conn, $sql))) {
        $sql = "DELETE FROM AZW001_companymst WHERE companycd='$companyCd'";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '501';
            $errors = sqlsrv_errors();
        }
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