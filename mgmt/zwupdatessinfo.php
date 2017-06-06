<?php
/**
 * 機器情報IU
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
define("H_C", "C");
define("H_U", "U");

include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$facilityCd = $_POST['facilitycd'];
$staffId = $_POST['staffid'];
$ssList = json_decode($_POST['sslist'], true);

//$facilityCd = '0001';
//$staffId = 'sw00001';
//$ssList = array(array(
//    "custid" => "00020",
//    "oldcustid" => "00020",
//    "serial" => "2016062200000399",
//    "oldserial" => "2016062200000398",
//    "startdate" => "2017-06-06",
//    "sensorid" => "0012",
//    "oldsensorid" => "0011",
//    "custname" => "テスト3"
//));

function checkCustomer($conn, $customerId)
{
    $sql = "SELECT 1 FROM AZW005_custmst WHERE custid='$customerId'";

    if ($result = sqlsrv_query($conn, $sql)) {
        if ($row = sqlsrv_fetch_array($result)) {
            return true;
        }
    }
    return false;
}

function getHandleFlag($data)
{
    $customerId = $data['custid'];
    $oldCustomerId = $data['oldcustid'];
    $sensorId = $data['sensorid'];
    $oldSensorId = $data['oldsensorid'];
    $serial = bin2hex($data['serial']);
    $oldSerial = bin2hex($data['oldserial']);

    if (is_empty($oldSensorId) && is_empty($oldSerial)) {
        return H_C;
    }
    if ((!is_empty($oldSensorId) && !is_empty($oldSerial) && ($sensorId != $oldSensorId || $serial != $oldSerial))
        || $customerId != $oldCustomerId
    ) {
        return H_U;
    }
    return '';
}

function checkSensorId($conn, $sensorId)
{
    $sql = "SELECT 1 FROM AZW009_serialrelation WHERE sensorid='$sensorId' AND enddate IS NULL";

    if ($result = sqlsrv_query($conn, $sql)) {
        if ($row = sqlsrv_fetch_array($result)) {
            return true;
        }
    }
    return false;
}

function checkSerial($conn, $serial)
{
    $sql = "SELECT 1 FROM AZW009_serialrelation WHERE serial='$serial' AND enddate IS NULL";

    if ($result = sqlsrv_query($conn, $sql)) {
        if ($row = sqlsrv_fetch_array($result)) {
            return true;
        }
    }
    return false;
}

//if ($conn && sqlsrv_begin_transaction($conn)) {
if ($conn) {
    if (!is_empty($ssList)) {
        $today = date('Y-m-d');
        foreach ($ssList as $data) {
            $customerId = $data['custid'];
            $oldCustomerId = $data['oldcustid'];
            $serial = bin2hex($data['serial']);
            $oldSerial = bin2hex($data['oldserial']);
            $sensorId = $data['sensorid'];
            $oldSensorId = $data['oldsensorid'];
            $startDate = $data['startdate'];

            if (!is_empty($customerId) && !checkCustomer($conn, $customerId)) {
                $code = '600';
                $errors[] = array('入居者：' . $customerId . 'が存在しません');
            }

            if (!is_empty($sensorId) && $sensorId != $oldSensorId && checkSensorId($conn, $sensorId)) {
                $code = '600';
                $errors[] = array('センサーID：' . $sensorId . 'が既に存在しました');
            }

            if (!is_empty($serial) && $serial != $oldSerial && checkSerial($conn, $serial)) {
                $code = '600';
                $errors[] = array('シリアル：' . $serial . 'が既に存在しました');
            }
        }

        if ($code == '200') {
            foreach ($ssList as $data) {
                $customerId = $data['custid'];
                $oldCustomerId = $data['oldcustid'];
                $serial = bin2hex($data['serial']);
                $oldSerial = bin2hex($data['oldserial']);
                $sensorId = $data['sensorid'];
                $oldSensorId = $data['oldsensorid'];
                $startDate = $data['startdate'];

                $handleFlag = getHandleFlag($data);
                if ($handleFlag == H_C) {
                    if (!is_empty($serial) && !is_empty($sensorId)) {
                        $sql = "INSERT INTO AZW009_serialrelation(serial,sensorid,facilitycd,custid,startdate)
                            VALUES('$serial','$sensorId','$facilityCd','$customerId',CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120))";

                        if (!$result = sqlsrv_query($conn, $sql)) {
                            $code = '504';
                            $errors = array(sqlsrv_errors(), $sql);
                            break;
                        }
                    } else {
                        $code = '503';
                        $errors = array('parament is empty.');
                        break;
                    }
                } else if ($handleFlag == H_U) {
                    $sql = "SELECT 1 FROM AZW009_serialrelation WHERE serial='$oldSerial' AND sensorid='$oldSensorId' AND startdate='$startDate'";
                    $result = sqlsrv_query($conn, $sql);

                    if (sqlsrv_has_rows($result)) {
                        // add customer
                        if (!is_empty($customerId) && is_empty($oldCustomerId) && $sensorId == $oldSensorId && $serial == $oldSerial) {
                            $sql = "UPDATE AZW009_serialrelation SET custid='$customerId'
                                    WHERE serial='$oldSerial' AND sensorid='$oldSensorId' AND startdate='$startDate'";

                            if (!$result = sqlsrv_query($conn, $sql)) {
                                $code = '509';
                                $errors = sqlsrv_errors();
                                break;
                            }
//                    } // aremove customer
//                    else if (is_empty($customerId) && !is_empty($oldCustomerId) && $sensorId == $oldSensorId && $serial == $oldSerial) {
//                        //has history
//                        //hasn't history
//                        $sql = "UPDATE AZW009_serialrelation SET custid='$customerId',initflag=0
//                                WHERE serial='$oldSerial' AND sensorid='$oldSensorId' AND startdate='$startDate'";
//
//                        if (!$result = sqlsrv_query($conn, $sql)) {
//                            $code = '510';
//                            $errors = sqlsrv_errors();
//                            break;
//                        }
                        } // history
                        else {
                            if (strtotime($today) == strtotime($startDate)) {
//                                $customerId = $data['custid'];
//                                $oldCustomerId = $data['oldcustid'];
//                                $serial = bin2hex($data['serial']);
//                                $oldSerial = bin2hex($data['oldserial']);
//                                $sensorId = $data['sensorid'];
//                                $oldSensorId = $data['oldsensorid'];
//                                $startDate = $data['startdate'];
                                $itemSql = '';

                                if (!is_empty($customerId) && $customerId != $oldCustomerId) {
                                    $itemSql .= ",custid='$customerId'";
                                }

                                if (!is_empty($serial) && $serial != $oldSerial) {
                                    $itemSql .= ",serial='$serial'";
                                }

                                if (!is_empty($sensorId) && $sensorId != $oldSensorId) {
                                    $itemSql .= ",sensorid='$sensorId'";
                                }

                                if (!is_empty($itemSql)) {
                                    $sql = "UPDATE AZW009_serialrelation SET " . substr($itemSql, 1) . "
                                            WHERE serial='$oldSerial' AND custid='$oldCustomerId' AND sensorid='$oldSensorId'
                                            AND startdate='$startDate'";

                                    if (!$result = sqlsrv_query($conn, $sql)) {
                                        $code = '507';
                                        $errors = sqlsrv_errors();
                                        break;
                                    }
                                }
                            } else {
                                $sql = "UPDATE AZW009_serialrelation SET enddate=CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE()-1,120)
                                        WHERE serial='$oldSerial' AND sensorid='$oldSensorId' AND startdate='$startDate'";

                                if (!$result = sqlsrv_query($conn, $sql)) {
                                    $code = '506';
                                    $errors = sqlsrv_errors();
                                    break;
                                }

                                if (!is_empty($serial) && !is_empty($sensorId)) {
                                    $sql = "INSERT INTO AZW009_serialrelation(serial,sensorid,facilitycd,custid,startdate) VALUES
                                            ('$serial','$sensorId','$facilityCd','$customerId',CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120))";

                                    if (!$result = sqlsrv_query($conn, $sql)) {
                                        $code = '508';
                                        $errors = sqlsrv_errors();
                                        break;
                                    }
                                } else {
                                    $code = '507';
                                    $errors = array('parament is empty.');
                                    break;
                                }
                            }
                        }
                    } else {
                        $code = '505';
                        $errors = array('db\'s data is not exists..', $sql);
                        break;
                    }
                }
            }
        }
    } else {
        $code = '501';
        $errors = array('parament is empty.');
    }
} else {
    $code = '500';
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