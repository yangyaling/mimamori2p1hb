<?php
/**
 * センサー情報同期化処理。
 * User: NITIOSRD
 * Date: 2016/12/21
 * Time: 10:01
 */

include '../cmslib.php';
include 'sensor_common.php';

$loginUser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

@$isApi = $_POST['isapi'];

function cleanNITSensorInfo($conn, &$code, &$errors)
{
    $sql = "TRUNCATE TABLE AZW137_zworksdevicemst";
    $result = sqlsrv_query($conn, $sql);
    if (!$result) {
        $errors = sqlsrv_errors();
        $code = '501';
        return;
    }

    $sql = "TRUNCATE TABLE AZW136_zworksnodemst";
    $result = sqlsrv_query($conn, $sql);
    if (!$result) {
        $errors = sqlsrv_errors();
        $code = '502';
        return;
    }

    $sql = "TRUNCATE TABLE AZW135_zworksgatewaymst";
    $result = sqlsrv_query($conn, $sql);
    if (!$result) {
        $errors = sqlsrv_errors();
        $code = '502';
        return;
    }
}

function syncNITSensorInfo($conn, $sensorInfo, $adminEmail, &$code, &$errors)
{
    $giSql = "INSERT INTO AZW135_zworksgatewaymst(gatewayid,gatewayname,adminemail)VALUES";
    $niSql = "INSERT INTO AZW136_zworksnodemst(nodeid,nodename,gatewayid,displayname)VALUES";
    $diSql = "INSERT INTO AZW137_zworksdevicemst(deviceid,devicename,gatewayid,nodeid,devicetype,unit,[timestamp],deviceclass)VALUES";
    $gvSql = '';
    $nvSql = '';
    $dvSql = '';
    $gi = 0;
    $ni = 0;
    $di = 0;
    foreach ($sensorInfo as $gk => $gv) {
        $gName = $gv['name'];
        $gvSql .= "('$gk','$gName','$adminEmail'),";
        if ($gi >= 10000) {
            $result = sqlsrv_query($conn, $giSql . substr($gvSql, 0, strlen($gvSql) - 1));
            if (!$result) {
                $errors = sqlsrv_errors();
                $code = '501';
                return;
            }
            $gvSql = '';
            $gi = 0;
        };

        foreach ($gv['nodes'] as $nk => $nv) {
            $nName = $nv['name'];
            $nvSql .= "('$nk','$nName','$gk',''),";
            if ($ni >= 10000) {
                $result = sqlsrv_query($conn, $niSql . substr($nvSql, 0, strlen($nvSql) - 1));
                if (!$result) {
                    $errors = sqlsrv_errors();
                    $code = '502';
                    return;
                }
                $nvSql = '';
                $ni = 0;
            }

            foreach ($nv['devices'] as $dk => $dv) {
                $dName = $dv['name'];
                $dType = getDeviceType($dv['type']);
                $dvSql .= "('$dk','$dName','$gk','$nk','$dType','',NULL,'2'),";
                if ($di >= 10000) {
                    $result = sqlsrv_query($conn, $diSql . substr($dvSql, 0, strlen($dvSql) - 1));
                    if (!$result) {
                        $errors = sqlsrv_errors();
                        $code = '503';
                        return;
                    }
                    $dvSql = '';
                    $di = 0;
                }
                $di++;
            }
            $ni++;
        }
        $gi++;
    }

    if (!is_empty($gvSql)) {
        $result = sqlsrv_query($conn, $giSql . substr($gvSql, 0, strlen($gvSql) - 1));
        if (!$result) {
            $errors = sqlsrv_errors();
            $code = '501';
            return;
        }
    }

    if (!is_empty($nvSql)) {
        $result = sqlsrv_query($conn, $niSql . substr($nvSql, 0, strlen($nvSql) - 1));
        if (!$result) {
            $errors = sqlsrv_errors();
            $code = '502';
            return;
        }
    }

    if (!is_empty($dvSql)) {
        $result = sqlsrv_query($conn, $diSql . substr($dvSql, 0, strlen($dvSql) - 1));
        if (!$result) {
            $errors = sqlsrv_errors();
            $code = '503';
            return;
        }
    }
}

if ($conn && sqlsrv_begin_transaction($conn)) {
    if (!session_id()) session_start();
    $sensorInfo = array();
    if ($_SESSION[SENSORINFO]) {
        $sensorInfo = $_SESSION[SENSORINFO];
    } else {
        //TODO 'aimi.f507@gmail.com' $loginUser->getZworksEmail()
        //ZWorksからセンサー情報を取得する
        unset($_SESSION[SENSORINFO]);
        $sensorInfo = getSensorInfo(getSessionId($conn, 'aimi.f507@gmail.com'));
        $_SESSION[SENSORINFO] = $sensorInfo;
    }

    if ($sensorInfo) {
        //元のデータをクリアする
        cleanNITSensorInfo($conn, $code, $errors);

        //同期化処理を行う
        syncNITSensorInfo($conn, $sensorInfo, 'aimi.f507@gmail.com', $code, $errors);
    }
} else {
    $code = '500';
}

if ($code == '200') {
    if (!sqlsrv_commit($conn)) {
        $code = '506';
        sqlsrv_rollback($conn);
    }
} else {
    $code = '505';
    sqlsrv_rollback($conn);
}
sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    $arrReturn['code'] = $code;
    $arrReturn['errors'] = $errors;
    sendResponse(json_encode($arrReturn));
} else {
    if ($code == '200') {
        header("Location: gateway.php");
    } else {
        header("Location: gateway.php?errno=$code");
    }
    exit();
}

?>