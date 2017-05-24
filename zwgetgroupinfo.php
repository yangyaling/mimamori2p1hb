<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/08
 * Time: 14:56
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['groupinfo'] = '';

if ($conn) {
    $arrReturn['groupinfo'] = getCVList($conn, CLASS_GROUP);
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>