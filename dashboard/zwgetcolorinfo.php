<?php
/**
 * color取得処理。
 * User: NITIOSRD
 * Date: 2017/01/19
 * Time: 14:29
 */
include 'zwlrcommon.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$userId0 = $_POST['userid0'];
$forWeekly = $_POST['forweekly'];
$baseDate = $_POST['basedate'];
$sumFlg = $_POST['sumflg'];

$colorList = array();

function getSumColorList($conn, $userId0, $sd, $ed)
{
    $actionIdList = array();

    $sql = "SELECT t.actionid,t.actionname,t.actionexplain,t.actioncolor,t.actionclass,t.vzstartdt,t.vzenddt FROM (
            SELECT DISTINCT vz.actionid + '|' + vz.actionclass + '|' + vz.vzstartdt actionid,vz.actionorder,vz.actionname,vz.actionexplain,
            vz.color1 actioncolor,vz.actionclass,vz.vzstartdt vzorder,SUBSTRING(vz.vzstartdt,6,5) vzstartdt,SUBSTRING(vz.vzenddt,6,5) vzenddt
            FROM (SELECT CONVERT(VARCHAR(10),DATEADD(DAY,v.number*1,CAST('$sd 00:00:00' AS DATE)),120) dst
            FROM AZW111_values v WHERE v.type='S' AND v.number < DATEDIFF(DD,'$sd 00:00:00','$ed 00:00:00')) ds
            LEFT OUTER JOIN AZW150_vzconfig vz WITH(NOLOCK) ON vz.userid = '$userId0' AND vz.vzstartdt <= ds.dst + ' 00:00:00'
            AND (vz.vzenddt IS NULL OR vz.vzenddt >= ds.dst + ' 00:00:00') WHERE userid IS NOT NULL) t
            ORDER BY t.actionclass,t.actionorder,t.vzorder,t.actionid";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index1 = 0;
        $index2 = 0;
        $actionIdList[0] = array();
        $actionIdList[1] = array();
        while ($row = sqlsrv_fetch_array($result)) {
            if ($row[4] == '1') {
                $actionIdList[0][$index1] = array(
                    'actionid' => $row[0],
                    'actionname' => (!is_empty($row[6])) ? $row[1] . '(' . $row[5] . ' - ' . $row[6] . ')' : $row[1] . '(' . $row[5] . ')',
                    'actionexplain' => $row[2],
                    'actioncolor' => $row[3],
                    'data' => array()
                );
                $index1++;
            } else if ($row[4] == '2') {
                $actionIdList[1][$index2] = array(
                    'actionid' => $row[0],
                    'actionname' => (!is_empty($row[6])) ? $row[1] . '(' . $row[5] . ' - ' . $row[6] . ')' : $row[1] . '(' . $row[5] . ')',
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

function getColorList($conn, $userId0, $baseDate, $forWeekly, $subDays)
{
    $actionIdList = array();

    $today = $baseDate . ' 00:00:00';

    if ($forWeekly == '1') {
        $sql = "SELECT actionid + '|' + actionclass + '|' + vzstartdt actionid,actionname,actionexplain,color1 actioncolor,actionclass,SUBSTRING(vzstartdt,6,5) vzstartdt,SUBSTRING(vzenddt,6,5) vzenddt,vzstartdt vzorder
                FROM AZW150_vzconfig
                WHERE userid = '$userId0' AND vzstartdt <= '$today' AND (vzenddt IS NULL OR DATEDIFF(DAY,vzenddt,'$today') < $subDays)
                ORDER BY actionclass,actionorder,vzorder,actionid";
    } else {
        $sql = "SELECT actionid + '|' + actionclass + '|' + vzstartdt actionid,actionname,actionexplain,color1 actioncolor,actionclass,SUBSTRING(vzstartdt,6,5) vzstartdt,SUBSTRING(vzenddt,6,5) vzenddt,vzstartdt vzorder
                FROM AZW150_vzconfig
                WHERE userid = '$userId0' AND vzstartdt <= '$today' AND (vzenddt IS NULL OR vzenddt >= '$today')
                ORDER BY actionclass,actionorder,vzorder,actionid";
    }

    if ($result = sqlsrv_query($conn, $sql)) {
        $index1 = 0;
        $index2 = 0;
        $actionIdList[0] = array();
        $actionIdList[1] = array();
        while ($row = sqlsrv_fetch_array($result)) {
            if ($row[4] == '1') {
                $actionIdList[0][$index1] = array(
                    'actionid' => $row[0],
                    'actionname' => (!is_empty($row[6])) ? $row[1] . '(' . $row[5] . ' - ' . $row[6] . ')' : $row[1] . '(' . $row[5] . ')',
                    'actionexplain' => $row[2],
                    'actioncolor' => $row[3],
                    'data' => array()
                );
                $index1++;
            } else if ($row[4] == '2') {
                $actionIdList[1][$index2] = array(
                    'actionid' => $row[0],
                    'actionname' => (!is_empty($row[6])) ? $row[1] . '(' . $row[5] . ' - ' . $row[6] . ')' : $row[1] . '(' . $row[5] . ')',
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

if ($conn) {
//    $userId0 = '00008';
//    $baseDate = '2017-05-20';
//    $forWeekly = '1';
//    $sumFlg = SUMMARY_CLASS_DAY;

    if (is_empty($sumFlg)) {
        $subDays = 7;
        if ($forWeekly == '1' && $baseDate < date('Y-m-d')) {
            $baseDate = getThisSunday($baseDate);
        }
        $x = date("w", strtotime($baseDate));
        if ($x == 0) {
            $subDays = 7;
        } else {
            $subDays = $x;
        }
        $colorList = getColorList($conn, $userId0, $baseDate, $forWeekly, $subDays);
    } else {
        if ($sumFlg == SUMMARY_CLASS_DAY) {
            //日：各日のデータを、1週間単位で表示する
            $sd = getThisMonday($baseDate);
            $ed = getThisSunday($baseDate);
            $ed = date('Y-m-d', strtotime('+1 day', strtotime($ed)));
            $colorList = getSumColorList($conn, $userId0, $sd, $ed);
        } else if ($sumFlg == SUMMARY_CLASS_WEEK) {
            //週：各週のデータを、3ヶ月単位で表示する
            $month = substr($baseDate, 5, 2);
            $year = substr($baseDate, 0, 4);
            if ($month < 4) {
                $sd = $year . '-01' . '-01';
            } else if ($month < 7) {
                $sd = $year . '-04' . '-01';
            } else if ($month < 10) {
                $sd = $year . '-07' . '-01';
            } else {
                $sd = $year . '-10' . '-01';
            }
            $ed = date('Y-m-d', strtotime("$sd +3 month -1 day"));
            $sd = getThisMonday($sd);
            $ed = date('Y-m-d', strtotime('+1 day', strtotime(getThisSunday($ed))));
            $colorList = getSumColorList($conn, $userId0, $sd, $ed);
        } else if ($sumFlg == SUMMARY_CLASS_MONTH) {
            //月：各月のデータを、1年単位で表示する
            $sd = substr($baseDate, 0, 4) . '-01' . '-01';
            $ed = substr($baseDate, 0, 4) . '-12' . '-31';
            $ed = date('Y-m-d', strtotime('+1 day', strtotime($ed)));
            $colorList = getSumColorList($conn, $userId0, $sd, $ed);
        } else if ($sumFlg == SUMMARY_CLASS_YEAR) {
            //年：各年のデータを、10年単位で表示する
            $sd = (substr($baseDate, 0, 4) - 9) . '-01' . '-01';
            $ed = substr($baseDate, 0, 4) . '-12' . '-31';
            $ed = date('Y-m-d', strtotime('+1 day', strtotime($ed)));
            $colorList = getSumColorList($conn, $userId0, $sd, $ed);
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

$arrReturn['colorlist'] = $colorList;

sendResponse(json_encode($arrReturn));

?>