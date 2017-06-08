<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/9/11
 * Time: 13:40
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';

$deviceInfo = array();

//基準日時
$baseDate = $_POST['nowdate'];
//ユーザID１
$staffId = $_POST['staffid'];
//ユーザID０
$customerId = $_POST['custid'];

include 'zwgetbatterystatus.php';
include 'zwgetdailydeviceinfocommon.php';

function getDays($conn, $baseDate)
{
    $sql = "SELECT CONVERT(VARCHAR(10),DATEADD(dd,number,'$baseDate'),120) AS dt FROM AZW111_values
            WHERE type='S' AND DATEADD(dd,number,'$baseDate')<=DATEADD(dd,-1,CONVERT(VARCHAR(8),DATEADD(mm,1,'$baseDate'),120)+'01')";

    $result = sqlsrv_query($conn, $sql);
    $days = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $days[$index] = $row[0];
            $index = $index + 1;
        }
    }

    return $days;
}

/*
 * 週情報取得
 */
function getWeeksArray($nowDate, &$sd)
{
    $weeksArr = array();

    //今週
    $label = '今週';
    $baseDate = $nowDate;
    $wr1 = array('label' => $label, 'basedate' => $baseDate);

    //先週
    $baseDate = date('Y-m-d', strtotime('last sunday', strtotime($baseDate)));
    $label = date('n月j日', strtotime('-6 day', strtotime($baseDate))) . '-' . date('n月j日', strtotime($baseDate));
    $wr2 = array('label' => $label, 'basedate' => $baseDate);

    //前の３週
    $baseDate = date('Y-m-d', strtotime('last sunday', strtotime($baseDate)));
    $label = date('n月j日', strtotime('-6 day', strtotime($baseDate))) . '-' . date('n月j日', strtotime($baseDate));
    $wr3 = array('label' => $label, 'basedate' => $baseDate);

    //前の４週
    $baseDate = date('Y-m-d', strtotime('last sunday', strtotime($baseDate)));
    $label = date('n月j日', strtotime('-6 day', strtotime($baseDate))) . '-' . date('n月j日', strtotime($baseDate));
    $wr4 = array('label' => $label, 'basedate' => $baseDate);

    $sd = $baseDate;

    $weeksArr[0] = $wr4;
    $weeksArr[1] = $wr3;
    $weeksArr[2] = $wr2;
    $weeksArr[3] = $wr1;

    return $weeksArr;
}

/*
 * 週の日付配列を取得
 */
function getWeekDateArray($conn, $arrCnt, $baseDate, $subDays, $staffId, $customerId, $label)
{
    global $SCH;
    $weekValues = array(
        'label' => $label,
        'datestring' => $baseDate,
        'deviceinfo' => array()
    );
    $deviceInfoList = array();
    $edn = $subDays + 1;

    $sqlDeviceInfo = "SELECT zrm.roomcd,zrm.nodeid,zrm.deviceid,zrm.devicetype,zrm.devicename,zrm.unit,zrm.nodename,
                      zrm.displayname,zrm.norder nodeorder,zrm.dorder,zrm.startdate,zrm.enddate
                      FROM AZW001_frscview ut,AZW230_sensormstview zrm WHERE zrm.initflag=1 AND ut.staffid='$staffId' AND ut.custid='$customerId'
                      AND ut.roomcd=zrm.roomcd AND ut.floorno=zrm.floorno AND zrm.deviceclass='1'";

    $sqlBS = ",ISNULL(bs.bs,'0') bs";
    $sqlBSWhere = "LEFT OUTER JOIN (SELECT bzd.nodeid,CASE WHEN bzd.mv > 0 THEN '2' WHEN bzd.mv = 0 AND vzd.mv > 0 THEN '1'
                    WHEN bzd.mv = 0 AND vzd.mv = 0 THEN '3' ELSE '4' END bs FROM (SELECT zrm.nodeid,COUNT(zd.value) mv
                    FROM (SELECT nodeid,deviceid FROM AZW230_sensormstview WHERE initflag=1 AND startdate <= CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120)
                    AND enddate IS NULL AND devicetype = '6') zrm LEFT OUTER JOIN AZW133_zworksdata zd ON zrm.deviceid = zd.deviceid
                    AND zd.timestmp >= CAST(DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(hh,-1,'" . date('Y-m-d H:i:s') . "')) - 32400 AS BIGINT) * 1000 GROUP BY zrm.nodeid) bzd
                    LEFT OUTER JOIN (SELECT zrm.nodeid,COUNT(zd.value) mv FROM (SELECT nodeid,deviceid FROM AZW230_sensormstview
                    WHERE initflag=1 AND startdate <= CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120) AND enddate IS NULL AND devicetype != '6') zrm
                    LEFT OUTER JOIN AZW133_zworksdata zd ON zrm.deviceid = zd.deviceid
                    AND zd.timestmp >= CAST(DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(hh,-1,'" . date('Y-m-d H:i:s') . "')) - 32400 AS BIGINT) * 1000 GROUP BY zrm.nodeid) vzd
                    ON bzd.nodeid = vzd.nodeid) bs ON bs.nodeid = zdm.nodeid";

    $sql = "SELECT zdm.roomcd,zdm.nodeid,zdm.deviceid,zdm.devicetype,zdm.devicename,zdm.unit,zd.value,
          CONVERT(VARCHAR(19),DATEADD(SECOND,zd.timestmp / 1000 + 9 * 3600,'1970-01-01 00:00:00'),120) dt,
          zdm.nodename,zdm.displayname,da.dt $sqlBS
          FROM (SELECT CONVERT(VARCHAR(10),DATEADD(DAY,v.number*-1,CAST('$baseDate' AS DATE)),120) dt
          FROM AZW111_values v WHERE v.type='S' AND v.number < $edn) da LEFT OUTER JOIN ($sqlDeviceInfo) zdm
          ON zdm.startdate <= da.dt AND (zdm.enddate IS NULL OR zdm.enddate >= da.dt)
          LEFT OUTER JOIN (SELECT deviceid,value,timestmp,date dt,ROW_NUMBER() OVER(PARTITION BY deviceid ORDER BY timestmp DESC) ni
          FROM AZW138_zworkslastdata) zd ON zd.deviceid = zdm.deviceid AND zd.ni = 1
          $sqlBSWhere
          ORDER BY zdm.nodeorder,zdm.dorder,da.dt";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $index2 = 0;
        $dates = array();

        $nodeId = '';
        $nodeName = '';
        $displayName = '';
        $deviceId = '';
        $deviceName = '';
        $deviceUnit = '';
        $latestValue = '';
        $latestDate = '';
        $bs = '';

        while ($row = sqlsrv_fetch_array($result)) {
            if (!is_empty($nodeId) && !is_empty($displayName) && ($nodeId != $row[1] || $displayName != $row[9])) {
                $deviceInfoList[$index2] = array(array(
                    'nodeid' => $nodeId,
                    'nodename' => $nodeName,
                    'displayname' => $displayName,
                    'deviceid' => $deviceId,
                    'devicename' => $deviceName,
                    'deviceunit' => $deviceUnit,
                    'latestvalue' => $latestValue,
                    'latestdate' => $latestDate,
                    'batterystatus' => $bs,
                    'dd' => $dates,
                    'devicevalues' => array()
                ));
                $dates = array();
                $index = 0;
                $index2 = $index2 + 1;
            }

//            if (!is_empty($row[1])) {
            $dates[$index] = array('dt' => $row[10], 'deviceid' => $row[2]);
            $index = $index + 1;
//            }
            $nodeId = $row[1];
            $nodeName = $row[8];
            $displayName = $row[9];
            $deviceId = $row[2];
            $deviceName = $row[4];
            $deviceUnit = $row[5];
            $latestValue = $row[6];
            $latestDate = $row[7];
            $bs = $row[11];
        }
        $deviceInfoList[$index2] = array(array(
            'nodeid' => $nodeId,
            'nodename' => $nodeName,
            'displayname' => $displayName,
            'deviceid' => $deviceId,
            'devicename' => $deviceName,
            'deviceunit' => $deviceUnit,
            'latestvalue' => $latestValue,
            'latestdate' => $latestDate,
            'batterystatus' => $bs,
            'dd' => $dates,
            'devicevalues' => array()
        ));

        $idxDVL = 0;
        foreach ($deviceInfoList as $divs) {
            $deviceValues = array();
            $idxDV = 0;
            foreach ($divs[0]['dd'] as $div) {
                if ($div['deviceid']) {
                    if ($baseDate == $div['dt']) {
                        $deviceValues[$idxDV] = getMainValues($conn, $arrCnt, $div['dt'], $div['deviceid']);
                    } else {
                        $deviceValues[$idxDV] = getMainValues($conn, '', $div['dt'], $div['deviceid']);
                    }
                } else {
                    $deviceValues[$idxDV] = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
                }
                $idxDV = $idxDV + 1;
            }
            $deviceInfoList[$idxDVL][0]['devicevalues'] = $deviceValues;
            $idxDVL = $idxDVL + 1;
        }
        $weekValues['deviceinfo'] = $deviceInfoList;
    }

    return $weekValues;
}

if ($conn) {
////基準日時
//    $baseDate = '2017-05-18 10:35:55';
////ユーザID１
//    $staffId = 'sw00001';
////ユーザID０
//    $customerId = '00002';
    $y = substr($baseDate, 0, 4);
    $m = substr($baseDate, 5, 2);
    $d = substr($baseDate, 8, 2);
    $hour = intval(substr($baseDate, 11, 2));
    $min = intval(substr($baseDate, 14, 2));

    $nowDate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));

    $arrCnt = $hour + ($min > 0 ? 1 : 0);

    // ４週のデータを取得する。
    $sd = '';
    $ed = $nowDate;
    $weeksArr = getWeeksArray($nowDate, $sd);

    $index = 0;
    foreach ($weeksArr as $weekValue) {
        if ($weekValue['label'] == '今週') {
            $nowDate = $weekValue['basedate'];
            $x = date("w", strtotime($nowDate));
            if ($x == 0) {
                $subDays = 6;
            } else {
                $subDays = ($x % 7 - 1);
            }
            $deviceInfo[$index] = getWeekDateArray($conn, $arrCnt, $nowDate, $subDays, $staffId, $customerId, $weekValue['label']);
        } else {
            $deviceInfo[$index] = getWeekDateArray($conn, '', $weekValue['basedate'], 6, $staffId, $customerId, $weekValue['label']);
        }
        $index = $index + 1;
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['deviceinfo'] = $deviceInfo;
$arrReturn['type'] = '1';

sendResponse(json_encode($arrReturn));

?>
