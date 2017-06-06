<?php
/**
 * 週単位毎生活リズム情報取得処理。
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
$baseDate = $_POST['basedate'];

$lrList = array();

if ($conn) {
//    $userId0 = '00002';
//    $baseDate = '2017-05-18';

    $sDay = getThisMonday($baseDate);
    $eDay = getThisSunday($baseDate);
    $newDate = date("Y-m-d");
    if (strtotime($newDate) < strtotime($eDay)) {
        $eDay = $newDate;
    }
    $index = 0;
    while (strtotime($sDay) <= strtotime($eDay)) {
        $actionIdList = getActionIdList($conn, $userId0, $sDay);
        $lrList[$index++] = array($sDay => getDailyLrList($conn, $userId0, $sDay, $actionIdList));
        $sDay = date('Y-m-d', strtotime('+1 day', strtotime($sDay)));
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

$arrReturn['lrlist'] = $lrList;

sendResponse(json_encode($arrReturn));

?>