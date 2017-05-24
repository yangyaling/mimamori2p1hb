<?php
/**
 * 生活リズム情報共通処理。
 * User: NITIOSRD
 * Date: 2017/01/19
 * Time: 14:29
 */
include '../lib.php';

define("SUMMARY_CLASS_DAY", "d");
define("SUMMARY_CLASS_WEEK", "w");
define("SUMMARY_CLASS_MONTH", "m");
define("SUMMARY_CLASS_YEAR", "y");

function getThisMonday($baseDate)
{
    $timestamp = strtotime($baseDate);
    return date('Y-m-d', ($timestamp - ((date('w', $timestamp) == 0 ? 7 : date('w', $timestamp)) - 1) * 86400));
}

function getThisSunday($baseDate)
{
    $timestamp = strtotime($baseDate);
    return date('Y-m-d', ($timestamp + (7 - (date('w', $timestamp) == 0 ? 7 : date('w', $timestamp))) * 86400));
}

//return date1>dateKey
function compareDateKey($date1, $dateKey)
{
    $date1s = explode('-', $date1);
    $dateKeys = explode('-', $dateKey);
    $result = true;
    $cnt = count($date1s);
    switch ($cnt) {
        case 1:
            $result = $date1 > $dateKey;
            break;
        case 2:
            $result = $date1s[0] >= $dateKeys[0] && $date1s[1] > $dateKeys[1];
            break;
        case 3:
            $result = strtotime($date1) > strtotime($dateKey);
            break;
    }
    return $result;
}

function getNowWeekNum($conn)
{
    $weekNum = date('Y') . '-' . intval(date('W'));

    $sql = "SET DATEFIRST 1;";
    $sql .= "SELECT CONVERT(CHAR(4),GETDATE(),120)+'-'+RIGHT('0'+CAST(DATEPART(WEEK, GETDATE()) AS VARCHAR(2)),2) dt";
    if ($result = sqlsrv_query($conn, $sql)) {
        if ($row = sqlsrv_fetch_array($result)) {
            $weekNum = $row[0];
        }
    }

    return $weekNum;
}

function getWeekNum($conn, $year, $sd, $weekNum)
{
    $weekNumArr = array();

    $sql = "SET DATEFIRST 1;";
    $sql .= "SELECT DATEPART(WEEK,CONVERT(VARCHAR(10),DATEADD(DAY,v.number*7,CAST('$sd' AS DATE)),120)) dt
            FROM AZW111_values v WHERE v.type='S' AND v.number < $weekNum";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $weekNumArr[$index] = $year . '-' . sprintf('%02s', $row[0]);
            $index++;
        }
        if ($weekNumArr[1] == $year . '-02') {
            $weekNumArr[0] = $year . '-01';
        }
    }

    return $weekNumArr;
}

function getDailyLrList($conn, $userId0, $wDay, $actionList)
{
//    $dailyLrList = array();
    $envList = array();
    $actList = array();

    $nDay = date('Y-m-d', strtotime('+1 day', strtotime($wDay)));

    $sql = "SELECT t.actionid + '|' + t.actionclass + '|' + t.vzstartdt actionid,t.actionclass,t.actionname,t.actionexplain,t.rn,t.value,t.color1/*,t.deviceid,t.dt*/
            FROM (SELECT v.actionid,v.vzstartdt,v.actionclass,v.actionname,v.actionorder,v.actionexplain,v.color1,
            ROW_NUMBER()OVER(PARTITION BY v.actionid,v.vzstartdt,v.actionclass ORDER BY a.dt) rn,CASE v.actionexplain
            WHEN '1' THEN CASE WHEN a.value = 255 THEN 1 ELSE NULL END WHEN '2' THEN CASE WHEN a.value = 0
            THEN 1 ELSE NULL END WHEN '3' THEN CASE WHEN a.value = 255 OR a.value = 0 THEN 1 ELSE NULL END
            WHEN '4' THEN CASE WHEN a.value = 100 THEN 1 ELSE 0 END WHEN '5' THEN a.value WHEN '6' THEN
            CASE WHEN a.value = 255 AND (SELECT a2.value FROM AZW151_actiondataview a2 WITH(NOLOCK) WHERE
            a2.deviceid = v.deviceid2 AND a2.dataexplain = v.dataexplain2 AND a2.dt = a.dt) = 100 THEN 1 ELSE NULL END END value/*,a.deviceid,a.dt*/
            FROM AZW150_vzconfig v WITH(NOLOCK) INNER JOIN AZW151_actiondataview a WITH(NOLOCK)
            ON a.deviceid = v.deviceid1 AND a.dataexplain = v.dataexplain1 AND a.dt >= '$wDay 00:00:00' AND a.dt < '$nDay 00:00:00'
            WHERE v.userid = '$userId0' AND v.vzstartdt <= '$wDay 00:00:00' AND (v.vzenddt IS NULL OR v.vzenddt >= '$wDay 00:00:00')) t
            WHERE t.value IS NOT NULL ORDER BY t.actionclass,t.actionorder,t.actionid,t.rn";

    if ($result = sqlsrv_query($conn, $sql)) {
        $indexK1 = 0;
        $index1 = 0;
        $envKey = '';
        $envDataList = array();

        $indexK2 = 0;
        $index2 = 0;
        $actKey = '';
        $actDataList = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $keyIN = $row[0] . ',' . $row[2] . ',' . $row[3] . ',' . $row[6];
            if ($row[1] == '1') {
                if (!is_empty($envKey) && $envKey != $keyIN) {
                    $key1 = explode(',', $envKey);
                    $envList[$indexK1++] = array(
                        'actionid' => $key1[0],
//                        'actionname' => $key1[1],
//                        'actionexplain' => $key1[2],
//                        'actioncolor' => $key1[3],
                        'data' => $envDataList
                    );
                    $envDataList = array();
                    $index1 = 0;
                }
                $envDataList[$index1++] = $row[5];
                $envKey = $keyIN;
            } else if ($row[1] == '2') {
                if (!is_empty($actKey) && $actKey != $keyIN) {
                    $key2 = explode(',', $actKey);
                    $actList[$indexK2++] = array(
                        'actionid' => $key2[0],
//                        'actionname' => $key2[1],
//                        'actionexplain' => $key2[2],
//                        'actioncolor' => $key2[3],
                        'data' => $actDataList
                    );
                    $actDataList = array();
                    $index2 = 0;
                }
                //活動解釈方法
                switch ($row[3]) {
                    //1:255ならば「活動の名称」とする。
                    //2:0ならば「活動の名称」とする。
                    //3:255または0ならば「活動の名称」とする。
                    //6:aのデータ＝255かつbのデータが100ならば、「活動の名称」とする。
                    case '1':
                    case '2':
                    case '3':
                    case '6':
                        $actDataList[$index2++] = $row[4];
                        break;
                    //4:100ならば「明」、100より小さいならば「暗」とする。
                    //5:値そのままとする。
                    case '4':
                    case '5':
                        $actDataList[$index2++] = $row[5];
                        break;
                }
                $actKey = $keyIN;
            }
        }

        if (!is_empty($envKey)) {
            $key1 = explode(',', $envKey);
            $envList[$indexK1] = array(
                'actionid' => $key1[0],
//                'actionname' => $key1[1],
//                'actionexplain' => $key1[2],
//                'actioncolor' => $key1[3],
                'data' => $envDataList
            );
        }
        if (!is_empty($actKey)) {
            $key2 = explode(',', $actKey);
            $actList[$indexK2] = array(
                'actionid' => $key2[0],
//                'actionname' => $key2[1],
//                'actionexplain' => $key2[2],
//                'actioncolor' => $key2[3],
                'data' => $actDataList
            );
        }
    }

    $cnt = count($actionList[0]);
    for ($x = 0; $x < $cnt; $x++) {
        foreach ($envList as $data2) {
            if ($actionList[0][$x]['actionid'] == $data2['actionid']) {
                $actionList[0][$x]['data'] = $data2['data'];
                break;
            }
        }
    }

    $cnt = count($actionList[1]);
    for ($x = 0; $x < $cnt; $x++) {
        foreach ($actList as $data2) {
            if ($actionList[1][$x]['actionid'] == $data2['actionid']) {
                $actionList[1][$x]['data'] = $data2['data'];
                break;
            }
        }
    }

//    $dailyLrList[0] = $envList;
//    $dailyLrList[1] = $actList;

    return $actionList;
}

function getActionIdList($conn, $userId0, $baseDate)
{
    $actionIdList = array();

    $today = $baseDate . ' 00:00:00';

    $sql = "SELECT actionid + '|' + actionclass + '|' + vzstartdt actionid,actionname,actionexplain,color1 actioncolor,actionclass FROM AZW150_vzconfig
            WHERE userid = '$userId0' AND vzstartdt <= '$today' AND (vzenddt IS NULL OR vzenddt >= '$today')
            ORDER BY actionclass,actionorder,actionid";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index1 = 0;
        $index2 = 0;
        $actionIdList[0] = array();
        $actionIdList[1] = array();
        while ($row = sqlsrv_fetch_array($result)) {
            if ($row[4] == '1') {
                $actionIdList[0][$index1] = array(
                    'actionid' => $row[0],
                    'actionname' => $row[1],
                    'actionexplain' => $row[2],
                    'actioncolor' => $row[3],
                    'data' => array()
                );
                $index1++;
            } else if ($row[4] == '2') {
                $actionIdList[1][$index2] = array(
                    'actionid' => $row[0],
                    'actionname' => $row[1],
                    'actionexplain' => $row[2],
                    'actioncolor' => $row[3],
                    'data' => array()
                );
                $index2++;
            }
        }
    }

    return $actionIdList;
}

function getVzConfig($conn, $userId0)
{
    $vzConfigList = array();

    $sql = "SELECT actionid,actionname,orderno,actionexplain,actionsummary,sensororderno,deviceid,dataexplain
            FROM AZW150_vzconfig WHERE userid = '$userId0' ORDER BY orderno,sensororderno";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $vzConfigList[$index] = array(
                'actionid' => $row[0],
                'actionname' => $row[1],
                'orderno' => $row[2],
                'actionexplain' => $row[3],
                'actionsummary' => $row[4],
                'sensororderno' => $row[5],
                'deviceid' => $row[6],
                'dataexplain' => $row[7]
            );
            $index = $index + 1;
        }
    }

    return $vzConfigList;
}

function getActionArray($dateKey, $dateArray, $sum1List)
{
    $resultArray = array();
    $index = 0;

    foreach ($dateArray as $date1) {
        $resultArray[$index] = 0;
        if (compareDateKey($date1, $dateKey)) {
            $resultArray[$index] = null;
        } else {
            foreach ($sum1List as $date2 => $value) {
                if ($date1 == $date2) {
                    $resultArray[$index] = $value;
                    break;
                }
            }
        }
        $index++;
    }

    return $resultArray;
}

function getDailyLrSumList($conn, $userId0, $sd, $ed, $dates)
{
    $lrSumList = array();

    $sql = "SELECT vz1.actionid + '|' + vz1.actionclass + '|' + vz1.vzstartdt actionid,vz1.actionsummary,d.sdate,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
            FROM AZW150_vzconfig vz1,dbo.zwgetdailyactionsummary('$userId0','$sd','$ed') d
            WHERE vz1.userid = '$userId0' AND vz1.actionid = d.actionid AND vz1.actionclass = d.actionclass AND vz1.vzstartdt = d.vzstartdt
            ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.sdate";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $sum1List = array();
        $sum2MaxList = array();
        $sum2AvgList = array();
        $sum2MinList = array();
        $oldActionId = '';
        $oldActionName = '';
        $oldActionSummary = '';
        $oldActionColor = '';
        $oldActionExplain = '';
        $dateKey = date('Y-m-d');
        while ($row = sqlsrv_fetch_array($result)) {
            $sDate = $row[2];
            if (!is_empty($oldActionId) && $oldActionId != $row[0]) {
                if ($oldActionSummary == 1) {
                    $lrSumList[$index++] = array(
                        'actionid' => $oldActionId,
                        'actionname' => $oldActionName,
                        'actionsummary' => $oldActionSummary,
                        'actioncolor' => $oldActionColor,
                        'actionexplain' => $oldActionExplain,
                        'data' => getActionArray($dateKey, $dates, $sum1List)
                    );
                    $sum1List = array();
                } else if ($oldActionSummary == 2) {
                    $lrSumList[$index++] = array(
                        'actionid' => $oldActionId,
                        'actionname' => $oldActionName,
                        'actionsummary' => $oldActionSummary,
                        'actioncolor' => $oldActionColor,
                        'actionexplain' => $oldActionExplain,
                        'max' => getActionArray($dateKey, $dates, $sum2MaxList),
                        'avg' => getActionArray($dateKey, $dates, $sum2AvgList),
                        'min' => getActionArray($dateKey, $dates, $sum2MinList)
                    );
                    $sum2MaxList = array();
                    $sum2AvgList = array();
                    $sum2MinList = array();
                }
            }
            if ($row[1] == 1) {
                $sum1List[$sDate] = $row[3];
            } else if ($row[1] == 2) {
                $sum2MaxList[$sDate] = $row[5];
                $sum2AvgList[$sDate] = $row[4];
                $sum2MinList[$sDate] = $row[6];
            }
            $oldActionId = $row[0];
            $oldActionSummary = $row[1];
            $oldActionColor = $row[7];
            $oldActionExplain = $row[8];
            $oldActionName = $row[9];
        }
        if ($oldActionSummary == 1) {
            $lrSumList[$index] = array(
                'actionid' => $oldActionId,
                'actionname' => $oldActionName,
                'actionsummary' => $oldActionSummary,
                'actioncolor' => $oldActionColor,
                'actionexplain' => $oldActionExplain,
                'data' => getActionArray($dateKey, $dates, $sum1List)
            );
        } else if ($oldActionSummary == 2) {
            $lrSumList[$index] = array(
                'actionid' => $oldActionId,
                'actionname' => $oldActionName,
                'actionsummary' => $oldActionSummary,
                'actioncolor' => $oldActionColor,
                'actionexplain' => $oldActionExplain,
                'max' => getActionArray($dateKey, $dates, $sum2MaxList),
                'avg' => getActionArray($dateKey, $dates, $sum2AvgList),
                'min' => getActionArray($dateKey, $dates, $sum2MinList)
            );
        }
    }

    return $lrSumList;
}

//$sd => '2016-01' '2016-45'
function getWeeklyLrSumList($conn, $userId0, $dates, $sd, $ed)
{
    $lrSumList = array();
//    $sd = $dates[0];
//    $ed = end($dates);

    $sql = "SET DATEFIRST 1;";
//    $sql .= "SELECT vz1.actionid,vz1.actionsummary,d.sdate,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
//            FROM AZW150_vzconfig vz1,dbo.zwgetweeklyactionsummary('$userId0','$sd 00:00:00','$ed 00:00:00') d
//            WHERE vz1.userid = '$userId0' AND vz1.actionid = d.actionid
//            ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.sdate";
//    $sql = "SELECT vz1.actionid,vz1.actionsummary,d.dt,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
//            FROM AZW150_vzconfig vz1 INNER JOIN AZW153_actionsumw d ON d.userid = vz1.userid AND d.actionid = vz1.actionid
//            AND d.dt >= '$sd' AND d.dt <= '$ed' WHERE vz1.userid = '$userId0' AND vz1.actionid = d.actionid AND vz1.actionclass = d.actionclass AND vz1.vzstartdt = d.vzstartdt
//            ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.dt";
    $sql .= "SELECT vz1.actionid + '|' + vz1.actionclass + '|' + vz1.vzstartdt actionid,vz1.actionsummary,d.sdate,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
              FROM AZW150_vzconfig vz1 INNER JOIN zwgetweeklyactionsummary('$userId0','$sd','$ed') d ON d.userid = vz1.userid
              AND vz1.actionid = d.actionid AND vz1.actionclass = d.actionclass AND vz1.vzstartdt = d.vzstartdt WHERE vz1.userid = '$userId0'
              ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.sdate";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $sum1List = array();
        $sum2MaxList = array();
        $sum2AvgList = array();
        $sum2MinList = array();
        $oldActionId = '';
        $oldActionName = '';
        $oldActionSummary = '';
        $oldActionColor = '';
        $oldActionExplain = '';
        $dateKey = getNowWeekNum($conn);
        while ($row = sqlsrv_fetch_array($result)) {
            $sDate = $row[2];
            if (!is_empty($oldActionId) && $oldActionId != $row[0]) {
                if ($oldActionSummary == 1) {
                    $lrSumList[$index++] = array(
                        'actionid' => $oldActionId,
                        'actionname' => $oldActionName,
                        'actionsummary' => $oldActionSummary,
                        'actioncolor' => $oldActionColor,
                        'actionexplain' => $oldActionExplain,
                        'data' => getActionArray($dateKey, $dates, $sum1List)
                    );
                    $sum1List = array();
                } else if ($oldActionSummary == 2) {
                    $lrSumList[$index++] = array(
                        'actionid' => $oldActionId,
                        'actionname' => $oldActionName,
                        'actionsummary' => $oldActionSummary,
                        'actioncolor' => $oldActionColor,
                        'actionexplain' => $oldActionExplain,
                        'max' => getActionArray($dateKey, $dates, $sum2MaxList),
                        'avg' => getActionArray($dateKey, $dates, $sum2AvgList),
                        'min' => getActionArray($dateKey, $dates, $sum2MinList)
                    );
                    $sum2MaxList = array();
                    $sum2AvgList = array();
                    $sum2MinList = array();
                }
            }
            if ($row[1] == 1) {
                $sum1List[$sDate] = $row[3];
            } else if ($row[1] == 2) {
                $sum2MaxList[$sDate] = $row[5];
                $sum2AvgList[$sDate] = $row[4];
                $sum2MinList[$sDate] = $row[6];
            }
            $oldActionId = $row[0];
            $oldActionSummary = $row[1];
            $oldActionColor = $row[7];
            $oldActionExplain = $row[8];
            $oldActionName = $row[9];
        }
        if ($oldActionSummary == 1) {
            $lrSumList[$index] = array(
                'actionid' => $oldActionId,
                'actionname' => $oldActionName,
                'actionsummary' => $oldActionSummary,
                'actioncolor' => $oldActionColor,
                'actionexplain' => $oldActionExplain,
                'data' => getActionArray($dateKey, $dates, $sum1List)
            );
        } else if ($oldActionSummary == 2) {
            $lrSumList[$index] = array(
                'actionid' => $oldActionId,
                'actionname' => $oldActionName,
                'actionsummary' => $oldActionSummary,
                'actioncolor' => $oldActionColor,
                'actionexplain' => $oldActionExplain,
                'max' => getActionArray($dateKey, $dates, $sum2MaxList),
                'avg' => getActionArray($dateKey, $dates, $sum2AvgList),
                'min' => getActionArray($dateKey, $dates, $sum2MinList)
            );
        }
    }

    return $lrSumList;
}

//$sd => '2017-03'
function getMonthlyLrSumList($conn, $userId0, $dates, $sd, $ed)
{
    $lrSumList = array();
//    $sd = $dates[0];
//    $ed = end($dates);

//    $sql = "SELECT vz1.actionid,vz1.actionsummary,d.sdate,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
//            FROM AZW150_vzconfig vz1,dbo.zwgetmonthlyactionsummary('$userId0','$sd 00:00:00','$ed 00:00:00') d
//            WHERE vz1.userid = '$userId0' AND vz1.actionid = d.actionid
//            ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.sdate";

//    $sql = "SELECT vz1.actionid,vz1.actionsummary,d.dt,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
//            FROM AZW150_vzconfig vz1 INNER JOIN AZW153_actionsumm d ON d.userid = vz1.userid AND d.actionid = vz1.actionid
//            AND d.dt >= '$sd' AND d.dt <= '$ed' WHERE vz1.userid = '$userId0' AND vz1.actionid = d.actionid AND vz1.actionclass = d.actionclass AND vz1.vzstartdt = d.vzstartdt
//            ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.dt";

    $sql = "SELECT vz1.actionid + '|' + vz1.actionclass + '|' + vz1.vzstartdt actionid,vz1.actionsummary,d.sdate,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
          FROM AZW150_vzconfig vz1 INNER JOIN zwgetmonthlyactionsummary('$userId0','$sd','$ed') d ON d.userid = vz1.userid
          AND vz1.actionid = d.actionid AND vz1.actionclass = d.actionclass AND vz1.vzstartdt = d.vzstartdt WHERE vz1.userid = '$userId0'
          ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.sdate";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $sum1List = array();
        $sum2MaxList = array();
        $sum2AvgList = array();
        $sum2MinList = array();
        $oldActionId = '';
        $oldActionName = '';
        $oldActionSummary = '';
        $oldActionColor = '';
        $oldActionExplain = '';
        $dateKey = date('Y-m');
        while ($row = sqlsrv_fetch_array($result)) {
            $sDate = $row[2];
            if (!is_empty($oldActionId) && $oldActionId != $row[0]) {
                if ($oldActionSummary == 1) {
                    $lrSumList[$index++] = array(
                        'actionid' => $oldActionId,
                        'actionname' => $oldActionName,
                        'actionsummary' => $oldActionSummary,
                        'actioncolor' => $oldActionColor,
                        'actionexplain' => $oldActionExplain,
                        'data' => getActionArray($dateKey, $dates, $sum1List)
                    );
                    $sum1List = array();
                } else if ($oldActionSummary == 2) {
                    $lrSumList[$index++] = array(
                        'actionid' => $oldActionId,
                        'actionname' => $oldActionName,
                        'actionsummary' => $oldActionSummary,
                        'actioncolor' => $oldActionColor,
                        'actionexplain' => $oldActionExplain,
                        'max' => getActionArray($dateKey, $dates, $sum2MaxList),
                        'avg' => getActionArray($dateKey, $dates, $sum2AvgList),
                        'min' => getActionArray($dateKey, $dates, $sum2MinList)
                    );
                    $sum2MaxList = array();
                    $sum2AvgList = array();
                    $sum2MinList = array();
                }
            }
            if ($row[1] == 1) {
                $sum1List[$sDate] = $row[3];
            } else if ($row[1] == 2) {
                $sum2MaxList[$sDate] = $row[5];
                $sum2AvgList[$sDate] = $row[4];
                $sum2MinList[$sDate] = $row[6];
            }
            $oldActionId = $row[0];
            $oldActionSummary = $row[1];
            $oldActionColor = $row[7];
            $oldActionExplain = $row[8];
            $oldActionName = $row[9];
        }
        if ($oldActionSummary == 1) {
            $lrSumList[$index] = array(
                'actionid' => $oldActionId,
                'actionname' => $oldActionName,
                'actionsummary' => $oldActionSummary,
                'actioncolor' => $oldActionColor,
                'actionexplain' => $oldActionExplain,
                'data' => getActionArray($dateKey, $dates, $sum1List)
            );
        } else if ($oldActionSummary == 2) {
            $lrSumList[$index] = array(
                'actionid' => $oldActionId,
                'actionname' => $oldActionName,
                'actionsummary' => $oldActionSummary,
                'actioncolor' => $oldActionColor,
                'actionexplain' => $oldActionExplain,
                'max' => getActionArray($dateKey, $dates, $sum2MaxList),
                'avg' => getActionArray($dateKey, $dates, $sum2AvgList),
                'min' => getActionArray($dateKey, $dates, $sum2MinList)
            );
        }
    }

    return $lrSumList;
}

function getYearlyLrSumList($conn, $userId0, $dates, $sd, $ed)
{
    $lrSumList = array();
//    $sd = $dates[0];
//    $ed = end($dates);

//    $sql = "SELECT vz1.actionid,vz1.actionsummary,d.sdate,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
//            FROM AZW150_vzconfig vz1,dbo.zwgetyearlyactionsummary('$userId0','$sd 00:00:00','$ed 00:00:00') d
//            WHERE vz1.userid = '$userId0' AND vz1.actionid = d.actionid
//            ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.sdate";

//    $sql = "SELECT vz1.actionid,vz1.actionsummary,d.dt,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
//            FROM AZW150_vzconfig vz1 INNER JOIN AZW153_actionsumy d ON d.userid = vz1.userid AND d.actionid = vz1.actionid
//            AND d.dt >= '$sd' AND d.dt <= '$ed' WHERE vz1.userid = '$userId0' AND vz1.actionid = d.actionid AND vz1.actionclass = d.actionclass AND vz1.vzstartdt = d.vzstartdt
//            ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.dt";

    $sql = "SELECT vz1.actionid + '|' + vz1.actionclass + '|' + vz1.vzstartdt actionid,vz1.actionsummary,d.sdate,d.cnt,d.vavg,d.vmax,d.vmin,vz1.color1,vz1.actionexplain,vz1.actionname
          FROM AZW150_vzconfig vz1 INNER JOIN zwgetyearlyactionsummary('$userId0','$sd','$ed') d ON d.userid = vz1.userid
          AND vz1.actionid = d.actionid AND vz1.actionclass = d.actionclass AND vz1.vzstartdt = d.vzstartdt WHERE vz1.userid = '$userId0'
          ORDER BY vz1.actionclass,vz1.actionorder,vz1.actionid,d.sdate";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $sum1List = array();
        $sum2MaxList = array();
        $sum2AvgList = array();
        $sum2MinList = array();
        $oldActionId = '';
        $oldActionName = '';
        $oldActionSummary = '';
        $oldActionColor = '';
        $oldActionExplain = '';
        $dateKey = date('Y');
        while ($row = sqlsrv_fetch_array($result)) {
            $sDate = $row[2];
            if (!is_empty($oldActionId) && $oldActionId != $row[0]) {
                if ($oldActionSummary == 1) {
                    $lrSumList[$index++] = array(
                        'actionid' => $oldActionId,
                        'actionname' => $oldActionName,
                        'actionsummary' => $oldActionSummary,
                        'actioncolor' => $oldActionColor,
                        'actionexplain' => $oldActionExplain,
                        'data' => getActionArray($dateKey, $dates, $sum1List)
                    );
                    $sum1List = array();
                } else if ($oldActionSummary == 2) {
                    $lrSumList[$index++] = array(
                        'actionid' => $oldActionId,
                        'actionname' => $oldActionName,
                        'actionsummary' => $oldActionSummary,
                        'actioncolor' => $oldActionColor,
                        'actionexplain' => $oldActionExplain,
                        'max' => getActionArray($dateKey, $dates, $sum2MaxList),
                        'avg' => getActionArray($dateKey, $dates, $sum2AvgList),
                        'min' => getActionArray($dateKey, $dates, $sum2MinList)
                    );
                    $sum2MaxList = array();
                    $sum2AvgList = array();
                    $sum2MinList = array();
                }
            }
            if ($row[1] == 1) {
                $sum1List[$sDate] = $row[3];
            } else if ($row[1] == 2) {
                $sum2MaxList[$sDate] = $row[5];
                $sum2AvgList[$sDate] = $row[4];
                $sum2MinList[$sDate] = $row[6];
            }
            $oldActionId = $row[0];
            $oldActionSummary = $row[1];
            $oldActionColor = $row[7];
            $oldActionExplain = $row[8];
            $oldActionName = $row[9];
        }
        if ($oldActionSummary == 1) {
            $lrSumList[$index] = array(
                'actionid' => $oldActionId,
                'actionname' => $oldActionName,
                'actionsummary' => $oldActionSummary,
                'actioncolor' => $oldActionColor,
                'actionexplain' => $oldActionExplain,
                'data' => getActionArray($dateKey, $dates, $sum1List)
            );
        } else if ($oldActionSummary == 2) {
            $lrSumList[$index] = array(
                'actionid' => $oldActionId,
                'actionname' => $oldActionName,
                'actionsummary' => $oldActionSummary,
                'actioncolor' => $oldActionColor,
                'actionexplain' => $oldActionExplain,
                'max' => getActionArray($dateKey, $dates, $sum2MaxList),
                'avg' => getActionArray($dateKey, $dates, $sum2AvgList),
                'min' => getActionArray($dateKey, $dates, $sum2MinList)
            );
        }
    }

    return $lrSumList;
}




























