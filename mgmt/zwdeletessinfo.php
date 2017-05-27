<?php
/**
 * 機器情報IU
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

$facilityCd = $_POST['facilitycd'];
$staffId = $_POST['staffid'];
$oldCustomerId = $_POST['custid'];
$oldSerial = bin2hex($_POST['serial']);
$oldSensorId = $_POST['sensorid'];
$startDate = $_POST['startdate'];

//$facilityCd='0001';
//$ssList=array(array(
//    "custid"=>"00013",
//    "oldcustid"=>"00012",
//    "serial"=>"2016062200000396",
//    "oldserial"=>"2016062200000396",
//    "nodename"=>"M3",
//    "oldnodename"=>"M3",
//    "custname"=>"(A)ニッセイ　花子さん"
//));

//$customerId = '';
//$oldCustomerId = '';
//$oldSerial = bin2hex('');
//$oldSensorId = '';
//$startDate = '';

if ($conn) {
    $displayName = '';
    $placeName = '';
    $whereSql = '';

    if (!is_empty($oldCustomerId)) {
        $whereSql = " AND custid='$oldCustomerId'";
    }

    $sql = "SELECT cmd.value displayname,cmp.value placename FROM AZW009_serialrelation
            LEFT OUTER JOIN AZW110_classmst AS cmd ON cmd.classcd='" . CLASS_NODE_LOCATION . "' AND cmd.code=displayname
            LEFT OUTER JOIN AZW110_classmst AS cmp ON cmp.classcd='" . CLASS_NODE_PLACE . "' AND cmp.code=place
            WHERE serial='$oldSerial' $whereSql AND sensorid='$oldSensorId' AND startdate='$startDate'";

    if ($row = sqlsrv_fetch_array(sqlsrv_query($conn, $sql))) {
        $displayName = $row[0];
        $placeName = $row[1];
        $sql = "UPDATE AZW009_serialrelation SET enddate=CONVERT(VARCHAR(10)," . SCH . ".GETJPDATE(),120)
                WHERE serial='$oldSerial' $whereSql AND sensorid='$oldSensorId' AND startdate='$startDate'";

        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '504';
            $errors = sqlsrv_errors();
        }
    } else {
        $code = '503';
        $errors = array('db\'s data is not exists..');
    }
    // 設置情報変更履歴を作成する
    if ($code == '200' && !is_empty($oldCustomerId)) {
        $msgContent = $oldSensorId . ' ' . $displayName . ' ' . $placeName;
        $insertSql = "INSERT INTO AZW152_vznoticetbl(receiveuser,senduser,noticetype,title,registdate,content)
                          SELECT TOP 1 '$staffId','$oldCustomerId','S','センサーの設置情報が更新されました',CONVERT(VARCHAR(19)," . SCH . ".GETJPDATE(),120),
                          '【'+fv.roomcd+'】　'+fv.custname+'さんのセンサーの設置情報が更新されました。\n\n以下のセンサー情報が削除されました。\n$msgContent\n'
                          FROM AZW001_frscview fv WHERE fv.custid='$oldCustomerId'";

        if (!sqlsrv_query($conn, $insertSql)) {
            $code = '505';
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