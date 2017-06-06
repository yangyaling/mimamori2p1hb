<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/9/22
 * Time: 9:45
 */

/*
 * デバイス情報取得
 */
function getDeviceInfo($conn, $staffId, $customerId, $deviceClass, $nodeId = '')
{
    global $SCH;
    $deviceInfo = array();
    $sqlWhere = '';

    if ($nodeId != '') {
        $sqlWhere = "AND zrm.nodeid='$nodeId'";
    }

    $sqlDeviceInfo = "SELECT zrm.roomcd,zrm.floorno,zrm.nodeid,zrm.deviceid,zrm.devicetype,zrm.devicename,zrm.unit,zrm.nodename,zrm.displayname,zrm.norder nodeorder,zrm.dorder
                      FROM AZW001_frscview ut,AZW230_sensormstview zrm WHERE zrm.initflag=1 AND zrm.startdate <= CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120) AND zrm.enddate IS NULL AND ut.staffid='$staffId' AND ut.custid='$customerId'
                      AND ut.roomcd=zrm.roomcd AND ut.floorno=zrm.floorno AND zrm.deviceclass='$deviceClass' $sqlWhere";

    $sql = "SELECT zdm.roomcd,zdm.nodeid,zdm.deviceid,zdm.devicetype,zdm.devicename,zdm.unit,zd.value,
            CONVERT(VARCHAR(19),DATEADD(SECOND,zd.timestmp / 1000 + 9 * 3600,'1970-01-01 00:00:00'),120) dt,zdm.nodename,zdm.displayname,zdm.floorno
            FROM ($sqlDeviceInfo) zdm LEFT OUTER JOIN (
            SELECT deviceid,value,timestmp,ROW_NUMBER() OVER(PARTITION BY deviceid ORDER BY timestmp DESC) ni
            FROM AZW133_zworksdata) zd ON zd.deviceid = zdm.deviceid AND zd.ni = 1 ORDER BY zdm.nodeorder,zdm.dorder";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $deviceInfo[$index] = array(
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
                'floorno' => $row[10]
            );
            $index = $index + 1;
        }
    } else {
        global $code;
        $code = '501';
    }

    return $deviceInfo;
}

/*
 * センサーバッテリー状態情報取得
 * 1:バッテリー正常
 * 2:バッテリー不足
 * 3:バッテリー切り
 * 4:異常
 */
function getBatteryStatus($conn, $nodeId, $nowDate)
{
    global $SCH;
    $batteryStatus = '0';

    $sql = "SELECT CASE WHEN bzd.mv > 0 THEN '2' WHEN bzd.mv = 0 AND vzd.mv > 0 THEN '1' WHEN bzd.mv = 0
            AND vzd.mv = 0 THEN '3' ELSE '4' END FROM (SELECT zrm.nodeid,COUNT(zd.value) mv FROM (
            SELECT nodeid,deviceid FROM AZW230_sensormstview WHERE initflag=1 AND startdate <= CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120) AND enddate IS NULL AND nodeid = '$nodeId' AND devicetype = '6') zrm
            LEFT OUTER JOIN AZW133_zworksdata zd ON zrm.deviceid = zd.deviceid AND zd.timestmp >= CAST(
            DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(hh,-1,'$nowDate')) - 32400 AS BIGINT) * 1000
            GROUP BY zrm.nodeid) bzd LEFT OUTER JOIN (SELECT zrm.nodeid,COUNT(zd.value) mv FROM (
            SELECT nodeid,deviceid FROM AZW230_sensormstview WHERE initflag=1 AND startdate <= CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120) AND enddate IS NULL AND nodeid = '$nodeId' AND devicetype != '6') zrm
            LEFT OUTER JOIN AZW133_zworksdata zd ON zrm.deviceid = zd.deviceid AND zd.timestmp >= CAST(
            DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(hh,-1,'$nowDate')) - 32400 AS BIGINT) * 1000
            GROUP BY zrm.nodeid) vzd ON bzd.nodeid = vzd.nodeid";
    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $row = sqlsrv_fetch_array($result);
        $batteryStatus = $row[0];
    }

    return $batteryStatus;
}

?>