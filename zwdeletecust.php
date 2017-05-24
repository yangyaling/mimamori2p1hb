<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/13
 * Time: 9:50
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';

$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];

if ($conn) {
    //見守り関係を解除
    $sql = "SELECT * FROM AZW008_custrelation WHERE custid='$customerId'";
    $result = sqlsrv_query($conn, $sql);
    if (sqlsrv_has_rows($result)) {
        $sql = "DELETE FROM AZW008_custrelation WHERE custid='$customerId'";
        $result = sqlsrv_query($conn, $sql);
        if ($result > 0) {
            $arrReturn['code'] = '201';
        } else {
            $arrReturn['code'] = '502';
        }

        //センサーアラートテーブルを削除する
        $sql = "DELETE FROM AZW143_sensorresulttbl WHERE staffid='$staffId' AND custid='$customerId'";
        $result = sqlsrv_query($conn, $sql);
        if ($result > 0) {
            $arrReturn['code'] = '203';
        } else {
            $arrReturn['code'] = '504';
        }

        //シナリオを削除する
        $sql = "SELECT scenarioid FROM AZW141_scenarioinfo WHERE staffid='$staffId' AND custid='$customerId'";
        $result = sqlsrv_query($conn, $sql);

        if (sqlsrv_has_rows($result)) {
            $sids = '';
            while ($row = sqlsrv_fetch_array($result)) {
                $sids = $sids . ",'" . strval($row[0]) . "'";
            }
            $sids = substr($sids, 1);

            $sql = "DELETE FROM AZW142_scenariodtl WHERE scenarioid in ($sids)";
            $result = sqlsrv_query($conn, $sql);
            if ($result > 0) {
                $arrReturn['code'] = '204';
            } else {
                $arrReturn['code'] = '505';
            }

            $sql = "DELETE FROM AZW141_scenarioinfo WHERE staffid='$staffId' AND custid='$customerId'";
            $result = sqlsrv_query($conn, $sql);
            if ($result > 0) {
                $arrReturn['code'] = '205';
            } else {
                $arrReturn['code'] = '506';
            }
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