<?php
/**
 * 一定期間の活動の集計データを表示する。
 * User: NITIOSRD
 * Date: 2017/02/13
 * Time: 13:29
 */
include 'zwlrcommon.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

$userId0 = $_POST['userid0'];
$baseDate = $_POST['basedate'];
$sumFlg = $_POST['sumflg'];

$lrSumList = array();
$dates = array();

if ($conn) {
//    $userId0 = '00002';
//    $baseDate = '2017-05-16';
//    $sumFlg = SUMMARY_CLASS_DAY;

    $index = 0;

    if ($sumFlg == SUMMARY_CLASS_DAY) {
        //日：各日のデータを、1週間単位で表示する
        $sd = getThisMonday($baseDate);
        $ed = getThisSunday($baseDate);
        $ed = date('Y-m-d', strtotime('+1 day', strtotime($ed)));
        $dt = $sd;
        while (strtotime($dt) < strtotime($ed)) {
            $dates[$index++] = $dt;
            $dt = date('Y-m-d', strtotime('+1 day', strtotime($dt)));
        }
        $lrSumList = getDailyLrSumList($conn, $userId0, $sd, $ed, $dates);
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
        $n = ceil((strtotime($ed) - strtotime($sd)) / 86400 / 7);
        $ed = date('Y-m-d', strtotime('+1 day', strtotime(getThisSunday($ed))));
        $dates = getWeekNum($conn, $year, $sd, $n);
        $lrSumList = getWeeklyLrSumList($conn, $userId0, $dates, $sd, $ed);
    } else if ($sumFlg == SUMMARY_CLASS_MONTH) {
        //月：各月のデータを、1年単位で表示する
        $sd = substr($baseDate, 0, 4) . '-01' . '-01';
        $ed = substr($baseDate, 0, 4) . '-12' . '-31';
        $ed = date('Y-m-d', strtotime('+1 day', strtotime($ed)));
        $dt = date('Y-m-d', strtotime($sd));
        while (strtotime($dt) < strtotime($ed)) {
            $dates[$index++] = substr($dt, 0, 7);
            $dt = date('Y-m-d', strtotime('+1 month', strtotime($dt)));
        }
        $lrSumList = getMonthlyLrSumList($conn, $userId0, $dates, $sd, $ed);
    } else if ($sumFlg == SUMMARY_CLASS_YEAR) {
        //年：各年のデータを、10年単位で表示する
        $sd = (substr($baseDate, 0, 4) - 9) . '-01' . '-01';
        $ed = substr($baseDate, 0, 4) . '-12' . '-31';
        $ed = date('Y-m-d', strtotime('+1 day', strtotime($ed)));
        $dt = date('Y-m-d', strtotime($sd));
        while (strtotime($dt) < strtotime($ed)) {
            $dates[$index++] = substr($dt, 0, 4);
            $dt = date('Y-m-d', strtotime('+1 year', strtotime($dt)));
        }
        $lrSumList = getYearlyLrSumList($conn, $userId0, $dates, $sd, $ed);
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

$arrReturn['lrsumlist'] = $lrSumList;
$arrReturn['dates'] = $dates;

sendResponse(json_encode($arrReturn));

?>