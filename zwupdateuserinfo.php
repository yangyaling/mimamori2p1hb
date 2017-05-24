<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/14
 * Time: 9:21
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';

$staffId = $_POST['staffid'];
$nickname = $_POST['nickname'];
$groupId = $_POST['groupid'];
$email = $_POST['email'];
$updateDate = $_POST['updatedate'];

if ($conn) {

    $sql = "SELECT staffid FROM AZW004_staffmst WHERE staffid='$staffId'";
    $result = sqlsrv_query($conn, $sql);

    //検索結果がある場合、更新処理を行う。
    if (sqlsrv_has_rows($result)) {
        $sql = "UPDATE AZW004_staffmst SET nickname='$nickname',groupid='$groupId',email='$email',updatedate='$updateDate' WHERE staffid='$staffId'";

        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $arrReturn['code'] = '503';
        }
    } else {
        $arrReturn['code'] = '501';
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>