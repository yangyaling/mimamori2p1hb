<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/17
 * Time: 14:16
 */
include 'lib.php';

//$conn=@mysql_connect(MYSQL_HOST_M,MYSQL_USER,MYSQL_PASS);
$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['userinfo'] = '';

$staffId = $_POST['staffid'];

if ($conn) {
//    $staffId='0001';
    $sql = "SELECT staffid,groupid,staffname,nickname,email,updatedate FROM AZW004_staffmst WHERE staffid='$staffId'";

    $result = sqlsrv_query($conn, $sql);
    $userInfo = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $userInfo[$index] = array(
                'userid' => $row[0],
                'groupid' => $row[1],
                'username' => $row[2],
                'nickname' => $row[3],
                'email' => $row[4],
                'updatedate' => $row[5]
            );
            $index = $index + 1;
        }
    }

    $arrReturn['userinfo'] = $userInfo;
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>