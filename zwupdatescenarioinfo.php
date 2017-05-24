<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/16
 * Time: 9:29
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];
$scenarioId = $_POST['scenarioid'];
$scenarioName = $_POST['scenarioname'];
$scopeCd = $_POST['scopecd'];
$startTime = $_POST['starttime'];
$endTime = $_POST['endtime'];
$scenarioDtlInfo = json_decode($_POST['scenariodtlinfo'], true);
$updateDate = $_POST['updatedate'];

function checkScenario($conn, $scenarioId, $scenarioName, $staffId, $customerId, &$code, &$errors)
{
    $retVal = 1;

    $sql = "SELECT d.deviceid,d.pattern,d.time,d.value,d.rpoint,i.scopecd,CONVERT(VARCHAR(5),i.starttime,108) starttime,CONVERT(VARCHAR(5),i.endtime,108) endtime
            FROM AZW141_scenarioinfo i INNER JOIN AZW142_scenariodtl d ON i.scenarioid=d.scenarioid WHERE i.scenarioid = '$scenarioId'";
    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $sql = "DELETE FROM AZW143_sensorresulttbl WHERE staffid='$staffId' AND custid='$customerId' AND scenarioid = '$scenarioId'";
        $result2 = sqlsrv_query($conn, $sql);

        if (!$result2) {
            $code = '510';
            $errors = sqlsrv_errors();
            return '0';
        }

        $deviceId = '';
        while ($row = sqlsrv_fetch_array($result)) {
            $deviceId = $row[0];

            $sql = "SELECT zwcheckscenariodevice2('$scenarioId','$deviceId',$row[1],$row[2],$row[3],'$row[4]','$row[5]','$row[6]','$row[7]') checkresult";
            $checkResult = sqlsrv_query($conn, $sql);
            if ($checkResult) {
                $checkRow = sqlsrv_fetch_array($checkResult);
                $retVal = $checkRow[0];
                if ($retVal == '1') {
                    break;
                }
            }
        }

        if ($retVal == '0') {
            $sqlSensorResult = "MERGE INTO AZW143_sensorresulttbl s USING (SELECT '$staffId' staffid,'$customerId' custid,
                           '$scenarioId' scenarioid,'$deviceId' deviceid) u
                           ON s.scenarioid = u.scenarioid AND s.deviceid=u.deviceid WHEN MATCHED THEN
                           UPDATE SET updatedate = GETDATE() WHEN NOT MATCHED THEN
                           INSERT (staffid,custid,scenarioid,deviceid,result,updatedate)
                           VALUES ('$staffId','$customerId','$scenarioId','$deviceId','異常検知あり',GETDATE());";
            $resultSensorResult = sqlsrv_query($conn, $sqlSensorResult);

            if (!$resultSensorResult) {
                $code = '511';
                $errors = sqlsrv_errors();
                return '0';
            }

            $sqlNotice = "MERGE INTO AZW121_noticetbl s
                        USING (SELECT '$staffId' staffid,'$customerId' custid,'$scenarioId' scenarioid) u
                        ON s.receiveuser = u.staffid AND s.senduser=u.custid AND s.subtitle = u.scenarioid AND s.status = '0'
                        WHEN MATCHED THEN
                        UPDATE SET title = '$scenarioName',registdate = GETDATE()
                        WHEN NOT MATCHED THEN
                        INSERT (title,senduser,receiveuser,noticetype,status,subtitle,registdate)
                        VALUES ('$scenarioName','$customerId','$staffId','1','0','$scenarioId',GETDATE());";
            $resultNotice = sqlsrv_query($conn, $sqlNotice);

            if (!$resultNotice) {
                $code = '512';
                $errors = sqlsrv_errors();
                return '0';
            }
        }
    }

    return $retVal;
}

$arrReturn['aaa'] = !is_empty($staffId) && !is_empty($customerId);

if ($conn && sqlsrv_begin_transaction($conn) && !is_empty($staffId) && !is_empty($customerId)) {
    //シナリオ採番
    if (is_empty($scenarioId)) {
        $sql = "SELECT staffid + custid + RIGHT('0000' + CAST(CAST(RIGHT('0000' + MAX(scenarioid),4) AS INT) + 1 AS
                VARCHAR(4)),4) scenarioid FROM AZW141_scenarioinfo WHERE staffid = '$staffId' AND custid = '$customerId'
                GROUP BY staffid,custid";
        $result = sqlsrv_query($conn, $sql);

        if ($result) {
            $row = sqlsrv_fetch_array($result);
            if ($row) {
                $scenarioId = $row[0];
            } else {
                $scenarioId = $staffId . $customerId . '0001';
            }
        } else {
            $code = '501';
            $errors = sqlsrv_errors();
        }
    }

    // 詳細情報を更新
    if ($code == '200' && !is_empty($scenarioDtlInfo)) {
        $sql = "DELETE FROM AZW142_scenariodtl WHERE scenarioid = '$scenarioId'";
        $result = sqlsrv_query($conn, $sql);

        if (!$result) {
            $code = '502';
            $errors = sqlsrv_errors();
        } else {
            $dno = 1;
            foreach ($scenarioDtlInfo as $dtlAsNode) {
                foreach ($dtlAsNode as $data) {
                    $deviceId = $data['deviceid'];
                    $pattern = $data['pattern'];
                    $time = $data['time'];
                    $value = $data['value'];
                    $rPoint = $data['rpoint'];

                    $sql = "INSERT INTO AZW142_scenariodtl (scenarioid,detailno,deviceid,pattern,[time],[value],rpoint,updatedate)
                        VALUES ('$scenarioId',$dno,'$deviceId','$pattern','$time','$value','$rPoint','$updateDate');";
                    $result = sqlsrv_query($conn, $sql);

                    if (!$result) {
                        $code = '504';
                        $errors = sqlsrv_errors();
                        break;
                    }
                }
                $dno = $dno + 1;
            }
        }
    } else {
        $code = '503';
        $errors = sqlsrv_errors();
    }

    if ($code == '200') {
        // ヘッダー情報を更新
        $sql = "MERGE INTO AZW141_scenarioinfo s USING (SELECT '$scenarioId' scenarioid) u
              ON s.scenarioid = u.scenarioid WHEN MATCHED THEN
              UPDATE SET scenarioname='$scenarioName',updatedate='$updateDate',scopecd='$scopeCd',starttime='$startTime',endtime='$endTime'
              WHEN NOT MATCHED THEN INSERT (staffid,custid,scenarioid,scenarioname,updatedate,scopecd,starttime,endtime)
              VALUES ('$staffId','$customerId','$scenarioId','$scenarioName','$updateDate','$scopeCd','$startTime','$endTime');";

        $result = sqlsrv_query($conn, $sql);

        if (!$result) {
            $code = '505';
            $errors = sqlsrv_errors();
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

$arrReturn['result'] = '1';

if ($code == '200') {
    $arrReturn['result'] = checkScenario($conn, $scenarioId, $scenarioName, $staffId, $customerId, $code, $errors);
}

if ($code == '200') {
    if (!sqlsrv_commit($conn)) {
        sqlsrv_rollback($conn);
    }
} else {
    sqlsrv_rollback($conn);
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>