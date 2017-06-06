<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/5/26
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$errors = array();
//$code = '200';

$protoId = $_POST['protoid'];

//$protoId = '0015';

if ($conn) {
    if (!is_empty($protoId)) {
        $sql = "SELECT 1 FROM AZW006_scenarioprotomst WHERE protoid='$protoId'";
        if ($result = sqlsrv_query($conn, $sql)) {
            if (sqlsrv_has_rows($result)) {
                $sql = "DELETE FROM AZW006_scenarioprotomst WHERE protoid='$protoId'";
                if (!$result = sqlsrv_query($conn, $sql)) {
                    $code = '503';
                    $errors = sqlsrv_errors();
                }
            } else {
                $code = '502';
                $errors = array("$protoId が存在しません。");
            }
        } else {
            $code = '502';
            $errors = sqlsrv_errors();
        }
    } else {
        $code = '501';
        $errors = array('param is error.');
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