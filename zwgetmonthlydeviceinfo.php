<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/9/11
 * Time: 13:41
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

//include 'zwgetbatterystatus.php';

/*
 * 月情報取得
 */
function getMonthsArray($conn, $nowDate)
{
    $weeksArr = array();

    //今月
    $label = '今月';
    $baseDate = substr($nowDate, 0, 8) . '01';
    $mr1 = array('label' => $label, 'basedate' => $baseDate, 'days' => getDays($conn, $baseDate));

    //前月
    $baseDate = date('Y-m-d', strtotime('-1 month', strtotime($baseDate)));
    $label = date('n月', strtotime($baseDate));
    $mr2 = array('label' => $label, 'basedate' => $baseDate, 'days' => getDays($conn, $baseDate));

    //前ヶ月
    $baseDate = date('Y-m-d', strtotime('-1 month', strtotime($baseDate)));
    $label = date('n月', strtotime($baseDate));
    $mr3 = array('label' => $label, 'basedate' => $baseDate, 'days' => getDays($conn, $baseDate));

    $weeksArr[0] = $mr3;
    $weeksArr[1] = $mr2;
    $weeksArr[2] = $mr1;

    return $weeksArr;
}

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

function adjustValues($days, $deviceInfo, $arrCnt)
{
    $di = array();
    $index = 0;
    foreach ($days as $day) {
        if ($arrCnt == '' || $index <= $arrCnt) {
            if ($deviceInfo[$day]) {
                $di[$index] = $deviceInfo[$day];
            } else {
                $di[$index] = 0;
            }
        }
        $index = $index + 1;
    }
    return $di;
}

/*
 *月毎デバイスデータ取得
 */
function getMonthValuesP1($conn, $arrCnt, $conDate, $staffId, $customerId, $days, $label)
{
    global $SCH;
    $monthValues = array(
        'label' => $label,
        'datestring' => $conDate,
        'deviceinfo' => array()
    );

    $sqlBS = ",ISNULL(bs.bs,'0') bs";
    $sqlBSGroupBy = ",ISNULL(bs.bs,'0')";
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

    $sql = "SELECT zdt.dt,zdm.nodeid,zdm.deviceid,zdm.nodename,zdm.displayname,zdm.devicename,COUNT(zd.value) cnt,zld.value,
            MAX(CONVERT(VARCHAR(19),DATEADD(SECOND,zld.timestmp / 1000 + 9 * 3600,'1970-01-01 00:00:00'),120)) ldt,zdm.unit $sqlBS
            FROM (SELECT CONVERT(VARCHAR(10),DATEADD(dd,number,'$conDate'),120) AS dt FROM AZW111_values WHERE type='S'
            AND DATEADD(dd,number,'$conDate')<=DATEADD(dd,-1,CONVERT(VARCHAR(8),DATEADD(mm,1,'$conDate'),120)+'01')) zdt
            LEFT OUTER JOIN (SELECT zrm.roomcd,zrm.nodeid,zrm.deviceid,zrm.devicetype,zrm.devicename,zrm.unit,zrm.nodename,
            zrm.displayname,zrm.norder nodeorder,zrm.dorder,zrm.startdate,zrm.enddate FROM AZW001_frscview ut,AZW230_sensormstview zrm
            WHERE zrm.initflag=1 AND ut.staffid='$staffId' AND ut.custid='$customerId' AND ut.roomcd=zrm.roomcd AND ut.floorno=zrm.floorno AND zrm.deviceclass='1') zdm
            ON zdm.startdate <= zdt.dt AND (zdm.enddate IS NULL OR zdm.enddate >= zdt.dt) LEFT OUTER JOIN AZW133_zworksdata zd
            ON zd.deviceid = zdm.deviceid AND zd.value = '255' AND zd.date = zdt.dt LEFT OUTER JOIN (
            SELECT deviceid,value,timestmp,date dt,ROW_NUMBER() OVER(PARTITION BY deviceid ORDER BY timestmp DESC) ni
            FROM AZW138_zworkslastdata) zld ON zld.deviceid = zdm.deviceid AND zld.ni = 1
            $sqlBSWhere
            GROUP BY zdt.dt,zdm.nodeid,zdm.deviceid,zd.date,zdm.nodename,zdm.displayname,zdm.devicename,zld.value,zdm.unit,zdm.nodeorder $sqlBSGroupBy
            ORDER BY zdm.nodeorder,zdt.dt";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index2 = 0;
        $deviceInfo = array();
        $deviceInfoList = array();

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
            if (!is_empty($nodeId) && !is_empty($displayName) && ($nodeId != $row[1] || $displayName != $row[4])) {
                $deviceInfo = adjustValues($days, $deviceInfo, $arrCnt);
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
                    'deviceinfo' => $deviceInfo
                ));
                $deviceInfo = array();
                $index2 = $index2 + 1;
            }
            //dt	nodeid	deviceid	nodename	displayname	devicename	cnt	value	ldt
            $deviceInfo[$row[0]] = $row[6];

            $nodeId = $row[1];
            $nodeName = $row[3];
            $displayName = $row[4];
            $deviceId = $row[2];
            $deviceName = $row[5];
            $deviceUnit = $row[9];
            $latestValue = $row[7];
            $latestDate = $row[8];
            $bs = $row[10];
        }
        $deviceInfo = adjustValues($days, $deviceInfo, $arrCnt);
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
            'deviceinfo' => $deviceInfo
        ));
        $monthValues['deviceinfo'] = $deviceInfoList;
    } else {
        global $code;
        $code = '502';
    }

    return $monthValues;
}

if ($conn) {
////基準日時
//    $baseDate = '2017-05-22 11:05:55';
////ユーザID１
//    $staffId = 'sw00001';
////ユーザID０
//    $customerId = '00002';

    $y = substr($baseDate, 0, 4);
    $m = substr($baseDate, 5, 2);
    $d = substr($baseDate, 8, 2);

    $nowDate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));

    // 3月のデータを取得する。
    $monthsArr = getMonthsArray($conn, $nowDate);
    $arrCnt = intval($d);

    $index = 0;
    foreach ($monthsArr as $monthValue) {
        if ($monthValue['label'] == '今月') {
            $deviceInfo[$index] = getMonthValuesP1($conn, $arrCnt, $monthValue['basedate'], $staffId, $customerId, $monthValue['days'], $monthValue['label']);
        } else {
            $deviceInfo[$index] = getMonthValuesP1($conn, '', $monthValue['basedate'], $staffId, $customerId, $monthValue['days'], $monthValue['label']);
        }
        $index = $index + 1;
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['deviceinfo'] = $deviceInfo;
$arrReturn['type'] = '2';

sendResponse(json_encode($arrReturn));

?>
