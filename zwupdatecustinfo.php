<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/13
 * Time: 14:12
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$arrReturn['code'] = '200';

$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];
$userName = $_POST['user0name'];
$sex = $_POST['sex'];
$birthday = $_POST['birthday'];
$address = $_POST['address'];
$kakaritsuke = $_POST['kakaritsuke'];
$drug = $_POST['drug'];
$health = $_POST['health'];
$other = $_POST['other'];
$updateUser = $_POST['updatename'];
$updateDate = $_POST['updatedate'];
//$roomCd = $_POST['roomcd'];
//$floorNo = $_POST['floorno'];

if ($conn) {

    $sql = "SELECT 1 FROM AZW005_custmst WHERE custid='$customerId'";
    $result = sqlsrv_query($conn, $sql);

    //検索結果がある場合、更新処理を行う。
    if (sqlsrv_has_rows($result)) {
        $sql = "UPDATE AZW005_custmst SET custname='$userName',sex='$sex',birthday='$birthday',address='$address',
                kakaritsuke='$kakaritsuke',drug='$drug',health='$health',other='$other',updateuser='$updateUser',
                updatedate='$updateDate' WHERE custid='$customerId'";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $arrReturn['code'] = '503';
        }
    } else {
        if (!is_empty($customerId) && !is_empty($updateDate)) {
            $sql = "INSERT INTO AZW005_custmst(custid,custname,sex,birthday,address,kakaritsuke,drug,health,other,updateuser,updatedate)
                    VALUES ('$customerId','$userName','$sex','$birthday','$address','$kakaritsuke','$drug','$health','$other','$updateUser','$updateDate')";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $arrReturn['code'] = '504';
            }
        } else {
            $arrReturn['code'] = '501';
        }
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>