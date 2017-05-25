<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/9/11
 * Time: 13:46
 */

/*
 * メインデバイスの日付配列を取得
 */
function getMainDateArray($conn, $arrCnt, $baseDate, $deviceId)
{
    $dateArray = array();
    $index = 0;
    for ($x = 0; $x < 6; $x++) {
        $dateArray[$index] = array(array(
            'datestring' => $baseDate,
            'devicevalues' => getMainValues($conn, '', $baseDate, $deviceId)
        ));

        $y = substr($baseDate, 0, 4);
        $m = substr($baseDate, 5, 2);
        $d = substr($baseDate, 8, 2);

        $conDate = date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d', mktime(0, 0, 0, $m, $d, $y)))));
        $baseDate = $conDate;
        $index = $index + 1;
    }

    $dateArray[$index] = array(array(
        'datestring' => $baseDate,
        'devicevalues' => getMainValues($conn, $arrCnt, $baseDate, $deviceId)
    ));

    return $dateArray;
}

/*
 *メインデバイスデータ取得
 */
function getMainValues($conn, $arrCnt, $conDate, $deviceId)
{
    $mainValues = array();

    $sql = "SELECT COUNT(zd.value) value FROM (SELECT CAST(DATEDIFF(ss,'1970-01-01 00:00:00',
            DATEADD(hh,s.number*1,'$conDate 00:00:00')) - 32400 AS BIGINT) * 1000 st,
            CAST(DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(hh,s.number*1+1,'$conDate 00:00:00')) - 32400
            AS BIGINT) * 1000 et FROM AZW111_values s WHERE s.type = 'P' AND s.number <= 23) zdt
            LEFT OUTER JOIN AZW133_zworksdata zd ON zd.deviceid = '$deviceId' AND zd.value = '255'
            AND zd.timestmp >= zdt.st AND zd.timestmp < zdt.et GROUP BY zdt.st ORDER BY zdt.st";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            if ($arrCnt == '' || $index <= $arrCnt) {
                $mainValues[$index] = $row[0];
            }
            $index = $index + 1;
        }
    } else {
        global $code;
        $code = '502';
    }

    return $mainValues;
}

/*
 * サブデバイスの日付配列を取得
 */
function getSubDateArray($conn, $arrCnt, $baseDate, $deviceId)
{
//    $dateArray = array();
//    $index = 0;
//    for ($x = 0; $x < 6; $x++) {
//        $dateArray[$index] = array(array(
//            'datestring' => $baseDate,
//            'devicevalues' => getSubValues($conn, '', $baseDate, $deviceId)
//        ));
//
//        $y = substr($baseDate, 0, 4);
//        $m = substr($baseDate, 5, 2);
//        $d = substr($baseDate, 8, 2);
//
//        $conDate = date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d', mktime(0, 0, 0, $m, $d, $y)))));
//        $baseDate = $conDate;
//        $index = $index + 1;
//    }

    $dateArray = array(array(
        'datestring' => $baseDate,
        'devicevalues' => getSubValues($conn, $arrCnt, $baseDate, $deviceId)
    ));

    return $dateArray;
}

/*
 * サブデバイスデータ取得
 */
function getSubValues($conn, $arrCnt, $conDate, $deviceId)
{
    $subValues = array();

    $sql = "SELECT MAX(zd.value) value FROM (SELECT CAST(DATEDIFF(ss,'1970-01-01 00:00:00',
            DATEADD(mi,s.number*30,'$conDate 00:00:00')) - 32400 AS BIGINT) * 1000 st,
            CAST(DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(mi,s.number*30+30,'$conDate 00:00:00')) - 32400 AS BIGINT) * 1000 et
            FROM AZW111_values s WHERE s.type = 'P' AND s.number <= 47) zdt LEFT OUTER JOIN AZW133_zworksdata zd
            ON zd.deviceid = '$deviceId' AND zd.timestmp >= zdt.st AND zd.timestmp < zdt.et
            GROUP BY zdt.st,zdt.et,zd.deviceid ORDER BY zdt.st,zdt.et,zd.deviceid";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $index = 0;
        $lIdx = 0;
        $lValue = getPreValue($conn, $conDate, $deviceId, 0);
        while ($row = sqlsrv_fetch_array($result)) {
            if ($arrCnt == '' || $index <= $arrCnt) {
                $value = $row[0];
                if (is_empty($value)) {
                    $subValues[$index] = 0;
                    if ($lIdx == 0) {
                        $lIdx = $index;
                    }
                } else {
                    if ($lIdx != 0) {
                        for ($x = $lIdx; $x < $index; $x++) {
                            $avgValue = intval(($lValue + $value) / 2 * 100) / 100;
                            $subValues[$x] = $avgValue;
                        }
                        $lIdx = 0;
                    }
                    $subValues[$index] = $value;
                    $lValue = $value;
                }
            }
            $index = $index + 1;
        }
    } else {
        global $code;
        $code = '503';
    }

    return $subValues;
}

/*
 * データを連続表示するため、先日の最後の値を取得する処理
 */
function getPreValue($conn, $baseDate, $deviceId, $deepIndex)
{
    $y = substr($baseDate, 0, 4);
    $m = substr($baseDate, 5, 2);
    $d = substr($baseDate, 8, 2);

    $temp = date("Y-m-d", mktime(0, 0, 0, $m, $d, $y));
    $conDate = date('Y-m-d', strtotime('-1 day', strtotime($temp)));

    $sql = "SELECT TOP 1 pre.value FROM (SELECT zdt.st,zdt.et,MAX(zd.value) value FROM (SELECT CAST(DATEDIFF(ss,
            '1970-01-01 00:00:00',DATEADD(mi,s.number*30,'$conDate 00:00:00')) - 32400 AS BIGINT) * 1000 st,
            CAST(DATEDIFF(ss,'1970-01-01 00:00:00',DATEADD(mi,s.number*30+30,'$conDate 00:00:00')) - 32400 AS BIGINT) * 1000 et
            FROM AZW111_values s WHERE s.type = 'P' AND s.number <= 47) zdt LEFT OUTER JOIN AZW133_zworksdata zd
            ON zd.deviceid = '$deviceId' AND zd.timestmp >= zdt.st AND zd.timestmp < zdt.et GROUP BY zdt.st,zdt.et,zd.deviceid) pre
            WHERE pre.value IS NOT NULL ORDER BY st DESC";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        if (sqlsrv_has_rows($result)) {
            $row = sqlsrv_fetch_array($result);
            return $row[0];
        } else if ($deepIndex > 1) {
            return 0;
        } else {
            $deepIndex++;
            return getPreValue($conn, $conDate, $deviceId, $deepIndex);
        }
    } else {
        return '0';
    }
}
