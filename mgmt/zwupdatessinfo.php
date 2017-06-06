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

if ($conn && sqlsrv_begin_transaction($conn)) {
    if (!is_empty($ssList)) {
        foreach ($ssList as $data) {
            $customerId = $data['custid'];
            $oldCustomerId = $data['oldcustid'];
            $serial = bin2hex($data['serial']);
            $oldSerial = bin2hex($data['oldserial']);
            $sensorId = $data['sensorid'];
            $oldSensorId = $data['oldsensorid'];
            $startDate = $data['startdate'];

            if (!is_empty($customerId) && !checkCustomer($conn, $customerId)) {
                $code = '502';
                $errors = array('入居者：' . $customerId . 'が存在しません');
                break;
            }

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
                        $sql = "UPDATE AZW009_serialrelation SET enddate=CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120)
                            WHERE serial='$oldSerial' AND sensorid='$oldSensorId' AND startdate='$startDate'";

                        if (!$result = sqlsrv_query($conn, $sql)) {
                            $code = '506';
                            $errors = sqlsrv_errors();
                            break;
                        }

                        if (!is_empty($serial) && !is_empty($sensorId)) {
                            $sql = "INSERT INTO AZW009_serialrelation(serial,sensorid,facilitycd,custid,startdate) VALUES
                                ('$serial','$sensorId','$facilityCd','$customerId',CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE()+1,120))";

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
                } else {
                    $code = '505';
                    $errors = array('db\'s data is not exists..', $sql);
                    break;
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