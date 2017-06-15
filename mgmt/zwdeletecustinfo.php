<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/5/3
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];
$roomCd = $_POST['roomcd'];
$floorNo = $_POST['floorno'];

//$staffId = 'sw00001';
//$customerId = '00009';
//$roomCd = '201号室';
//$floorNo = 'B棟2';

//if ($conn && sqlsrv_begin_transaction($conn)) {
if ($conn) {
    $customerName = '';
    $sql = "SELECT custname FROM AZW005_custmst WHERE custid='$customerId'";
    if ($row = sqlsrv_fetch_array(sqlsrv_query($conn, $sql))) {
        $customerName = $row[0];
        $sql = "DELETE FROM AZW005_custmst WHERE custid='$customerId'";;

        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '501';
            $errors = sqlsrv_errors();
        }
    } else {
        $code = '502';
    }

    if ($code == '200') {
        $sql = "SELECT 1 FROM AZW008_custrelation WHERE custid='$customerId' AND roomcd='$roomCd' AND floorno='$floorNo'";

        if (sqlsrv_has_rows(sqlsrv_query($conn, $sql))) {
            $sql = "DELETE FROM AZW008_custrelation WHERE custid='$customerId' AND roomcd='$roomCd' AND floorno='$floorNo'";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $code = '504';
                $errors = sqlsrv_errors();
            }
        } else {
            $code = '503';
        }
    }

    if ($code == '200') {
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
    }

    if ($code == '200') {
        $sql = <<<AAA
                MERGE INTO AZW009_serialrelation s
                USING (
                SELECT 
                    serial,sensorid,facilitycd,'' custid,
                    CONVERT(VARCHAR(10),$SCH.GETJPDATE(),120) startdate,
                    'Sys' createuser,CONVERT(VARCHAR(19),$SCH.GETJPDATE(),120) createdate
                FROM AZW009_serialrelation
                WHERE custid='$customerId' AND enddate IS NULL
                ) u ON u.serial=s.serial AND u.sensorid=s.sensorid AND u.startdate=s.startdate
                WHEN MATCHED AND s.startdate=CONVERT(VARCHAR(10),$SCH.GETJPDATE(),120) THEN
                UPDATE SET custid='',initflag='0',displayname=null,place=null,memo=null
                WHEN NOT MATCHED THEN
                INSERT (serial,sensorid,facilitycd,custid,startdate,createuser,createdate)
                VALUES (u.serial,u.sensorid,u.facilitycd,u.custid,u.startdate,u.createuser,u.createdate);
                UPDATE AZW009_serialrelation 
                SET enddate=CONVERT(VARCHAR(10),$SCH.GETJPDATE()-1,120)
                WHERE  custid='$customerId' AND enddate IS NULL;
AAA;

        if (!$result = sqlsrv_query($conn, $sql)) {
            $code = '508';
            $errors = sqlsrv_errors();
        }
    }

    // 入居者情報が削除された場合、変更履歴を作成する
    if ($code == '200') {
        $msg = "【" . $roomCd . "】 " . $customerName . "さんの情報が削除されました。";
        $insertSql = "INSERT INTO AZW152_vznoticetbl(receiveuser,senduser,noticetype,title,registdate,content,createuser,createdate) VALUES
                      ('$staffId','$customerId','S','センサーの設置情報が更新されました',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120),'$msg','$staffId',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120))";

        if (!sqlsrv_query($conn, $insertSql)) {
            $code = '507';
            $errors = array('sqlsrv_errors' => sqlsrv_errors(), 'sql' => $insertSql);
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

//if ($code == '200') {
//    if (!sqlsrv_commit($conn)) {
//        sqlsrv_rollback($conn);
//    }
//} else {
//    sqlsrv_rollback($conn);
//}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>