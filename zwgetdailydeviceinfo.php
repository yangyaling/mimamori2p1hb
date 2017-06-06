<?php
/**
 * 日別センサー情報取得。
 * User: NITIOSRD
 * Date: 2016/9/10
 * Time: 12:56
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';

$deviceInfo = array();
$picPath = '';
$picUpdateDate = '';

//基準日時
$baseDate = $_POST['nowdate'];
//ユーザID１
$staffId = $_POST['staffid'];
//ユーザID０
$customerId = $_POST['custid'];
//デバイスクラス：[1:メインデバイス　2:サブデバイス]
$deviceClass = $_POST['deviceclass'];
//ノードID
$nodeId = $_POST['nodeid'];

include 'zwgetbatterystatus.php';
include 'zwgetdailydeviceinfocommon.php';

//function getSubDeviceInfo($conn, $staffId, $customerId, $nodeId)
//{
//    $deviceIdArr = array();
//
//    $sql = "SELECT zrm.deviceid FROM AZW001_frscview ut,AZW230_sensormstview zrm WHERE ut.staffid='$staffId'
//            AND ut.custid='$customerId' AND ut.roomcd=zrm.roomcd AND zrm.deviceclass='1' AND zrm.nodeid='$nodeId'
//            ORDER BY zrm.devicetype";
//    $result = sqlsrv_query($conn, $sql);
//
//    if ($result) {
//        $index = 0;
//        while ($row = sqlsrv_fetch_array($result)) {
//            $deviceIdArr[$index] = $row[0];
//            $index = $index + 1;
//        }
//    }
//
//    return $deviceIdArr;
//}

//function getDateArray($sDay, $max)
//{
//    $dates = array();
//    $index = 0;
//    while ($index < $max) {
//        $dates[$index] = $sDay;
//        $sDay = date('Y-m-d', strtotime('+1 day', strtotime($sDay)));
//        $index = $index + 1;
//    }
//    return $dates;
//}

function getDeviceInfo2($conn, &$code, $arrCnt, $baseDate, $staffId, $customerId, $deviceClass, $nodeId = '')
{
    global $SCH;
    $deviceInfoList = array();
    $sqlWhere = '';
    $y = substr($baseDate, 0, 4);
    $m = substr($baseDate, 5, 2);
    $d = substr($baseDate, 8, 2);
    $dateYMD = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));

    $sqlBS = '';
    $sqlBSWhere = '';

    if (!is_empty($nodeId)) {
        $sqlWhere = "AND zrm.nodeid='$nodeId'";
    } else {
        $sqlBS = ",ISNULL(bs.bs,'0') bs";
        $sqlBSWhere = "LEFT OUTER JOIN (SELECT bzd.nodeid,CASE WHEN bzd.mv > 0 THEN '2' WHEN bzd.mv = 0 AND vzd.mv > 0 THEN '1'
                    WHEN bzd.mv = 0 AND vzd.mv = 0 THEN '3' ELSE '4' END bs FROM (SELECT zrm.nodeid,COUNT(zd.value) mv
                    FROM (SELECT nodeid,deviceid FROM AZW230_sensormstview WHERE initflag=1 AND startdate <= CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120)
                    AND enddate IS NULL AND devicetype = '6') zrm LEFT OUTER JOIN AZW133_zworksdata zd ON zrm.deviceid = zd.deviceid
                    AND zd.timestmp >= CAST(DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(hh,-1,'$baseDate')) - 32400 AS BIGINT) * 1000 GROUP BY zrm.nodeid) bzd
                    LEFT OUTER JOIN (SELECT zrm.nodeid,COUNT(zd.value) mv FROM (SELECT nodeid,deviceid FROM AZW230_sensormstview
                    WHERE initflag=1 AND startdate <= CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120) AND enddate IS NULL AND devicetype != '6') zrm
                    LEFT OUTER JOIN AZW133_zworksdata zd ON zrm.deviceid = zd.deviceid
                    AND zd.timestmp >= CAST(DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(hh,-1,'$baseDate')) - 32400 AS BIGINT) * 1000 GROUP BY zrm.nodeid) vzd
                    ON bzd.nodeid = vzd.nodeid) bs ON bs.nodeid = zdm.nodeid";
    }

    $sqlDeviceInfo = "SELECT zrm.roomcd,zrm.nodeid,zrm.deviceid,zrm.devicetype,zrm.devicename,zrm.unit,zrm.nodename,
                      zrm.displayname,zrm.norder nodeorder,zrm.dorder,zrm.startdate,zrm.enddate
                      FROM AZW001_frscview ut,AZW230_sensormstview zrm WHERE zrm.initflag=1 AND ut.staffid='$staffId' AND ut.custid='$customerId'
                      AND ut.roomcd=zrm.roomcd AND ut.floorno=zrm.floorno AND zrm.deviceclass='$deviceClass' $sqlWhere";

    $sql = "SELECT zdm.roomcd,zdm.nodeid,zdm.deviceid,zdm.devicetype,zdm.devicename,zdm.unit,zd.value,
          CONVERT(VARCHAR(19),DATEADD(SECOND,zd.timestmp / 1000 + 9 * 3600,'1970-01-01 00:00:00'),120) dt,
          zdm.nodename,zdm.displayname,da.dt $sqlBS
          FROM (SELECT CONVERT(VARCHAR(10),DATEADD(DAY,v.number*-1,CAST('$dateYMD' AS DATE)),120) dt
          FROM AZW111_values v WHERE v.type='S' AND v.number < 7) da LEFT OUTER JOIN ($sqlDeviceInfo) zdm
          ON zdm.startdate <= da.dt AND (zdm.enddate IS NULL OR zdm.enddate >= da.dt)
          LEFT OUTER JOIN (SELECT deviceid,value,timestmp,date dt,ROW_NUMBER() OVER(PARTITION BY deviceid ORDER BY timestmp DESC) ni
          FROM AZW138_zworkslastdata) zd ON zd.deviceid = zdm.deviceid AND zd.ni = 1
          $sqlBSWhere
          ORDER BY da.dt,zdm.nodeorder,zdm.dorder";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $index2 = 0;
        $dateString = '';
        $deviceInfo = array();
        while ($row = sqlsrv_fetch_array($result)) {
            if (!is_empty($dateString) && $dateString != $row[10]) {
                $deviceInfoList[$index2] = array(
                    'datestring' => $dateString,
                    'deviceinfo' => $deviceInfo
                );
                $deviceInfo = array();
                $index = 0;
                $index2 = $index2 + 1;
            }
            //roomcd	nodeid	deviceid	devicetype	devicename	unit	value	dt	nodename	displayname	dt
            if (!is_empty($row[1])) {
                if (!is_empty($nodeId)) {
//                'deviceid' => $value['deviceid'],
//                'devicename' => $value['devicename'],
//                'deviceunit' => $value['deviceunit'],
//                'latestvalue' => $value['latestvalue'],
//                'devicevalues' => getSubDateArray($conn, $arrCnt, $conDate, $value['deviceid'])
                    if ($dateYMD == $row[10]) {
                        $deviceValues = getSubValues($conn, $arrCnt, $row[10], $row[2]);
                    } else {
                        $deviceValues = getSubValues($conn, '', $row[10], $row[2]);
                    }
                    $deviceInfo[$index] = array(array(
//                        'roomid' => $row[0],
//                        'nodeid' => $row[1],
                        'deviceid' => $row[2],
//                        'devicetype' => $row[3],
                        'devicename' => $row[4],
                        'deviceunit' => $row[5],
                        'latestvalue' => $row[6],
//                        'latestdate' => $row[7],
//                        'nodename' => $row[8],
//                        'displayname' => $row[9],
                        'devicevalues' => $deviceValues
                    ));
                } else {
                    if ($dateYMD == $row[10]) {
                        $deviceValues = getMainValues($conn, $arrCnt, $row[10], $row[2]);
                    } else {
                        $deviceValues = getMainValues($conn, '', $row[10], $row[2]);
                    }
                    $deviceInfo[$index] = array(array(
                        'roomid' => $row[0],
                        'nodeid' => $row[1],
                        'deviceid' => $row[2],
                        'devicetype' => $row[3],
                        'devicename' => $row[4],
                        'deviceunit' => $row[5],
                        'latestvalue' => $row[6],
                        'latestdate' => $row[7],
                        'nodename' => $row[8],
                        'displayname' => $row[9],
                        'batterystatus' => $row[11],
                        'devicevalues' => $deviceValues
                    ));
                }
                $index = $index + 1;
            }
            $dateString = $row[10];
        }
        $deviceInfoList[$index2] = array(
            'datestring' => $dateString,
            'deviceinfo' => $deviceInfo
        );

//        if ($deviceInfo) {
//            foreach ($deviceInfoList as $values) {
//                foreach ($values as $value) {
//                }
//            }
//        }
    } else {
        $code = '501';
    }

    return $deviceInfoList;
}

if ($conn) {
////基準日時
//    $baseDate = '2017-05-26 14:25:55';
////ユーザID１
//    $staffId = 'sw00001';
////ユーザID０
//    $customerId = '00002';
////デバイスクラス：[1:メインデバイス　2:サブデバイス]
//    $deviceClass = '1';
////ノードID
//    $nodeId = '1077';
    $y = substr($baseDate, 0, 4);
    $m = substr($baseDate, 5, 2);
    $d = substr($baseDate, 8, 2);
    $hour = intval(substr($baseDate, 11, 2));
    $min = intval(substr($baseDate, 14, 2));
    $sec = intval(substr($baseDate, 17, 2));

    if ($deviceClass == '1') {
        $arrCnt = $hour + ($min > 0 ? 1 : 0);
        $deviceInfo = getDeviceInfo2($conn, $code, $arrCnt, $baseDate, $staffId, $customerId, $deviceClass);
    } else if ($deviceClass == '2') {
        $conDate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
        if (strtotime($conDate) < strtotime(date("Y-m-d"))) {
            $arrCnt = '';
        } else {
            $arrCnt = $hour * 2 + (($min > 30 || ($min == 30 && $sec > 0)) ? 2 : 1);
        }
        $deviceInfo = getDeviceInfo2($conn, $code, $arrCnt, $baseDate, $staffId, $customerId, $deviceClass, $nodeId);
    }

    if ($code == "200") {
        $sql = "SELECT ut.picpath,CONVERT(VARCHAR(19),ut.picupdatedate,120) picupdatedate FROM AZW001_frscview ut WHERE ut.staffid='$staffId' AND ut.custid='$customerId'";
        $result = sqlsrv_query($conn, $sql);
        if ($result && $row = sqlsrv_fetch_array($result)) {
            $picPath = $row[0];
            $picUpdateDate = $row[1];
        }
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['picpath'] = $picPath;
$arrReturn['picupdatedate'] = $picUpdateDate;
$arrReturn['deviceinfo'] = $deviceInfo;

sendResponse(json_encode($arrReturn));

?>