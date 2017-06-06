<?php
/**
 * 顧客名取得
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';

$customerName = '';

$customerId = $_POST['custid'];

if ($conn) {
//    $customerId = '0002';

    $sql = "SELECT cm.custname FROM AZW005_custmst cm WHERE cm.custid='$customerId'";
    $result = sqlsrv_query($conn, $sql);
    if ($result && $row = sqlsrv_fetch_array($result)) {
        $customerName = $row[0];
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['custname'] = $customerName;

sendResponse(json_encode($arrReturn));

?>