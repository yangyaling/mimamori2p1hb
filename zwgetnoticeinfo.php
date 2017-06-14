<?php
/**
 * 通知一覧/履歴
 * User: yuqy
 * Date: 2016/06/06
 * Time: 17:02
 */

include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//
//$code = '200';
//$errors = array();

$notices = array();

$facilityCd = $_POST['facilitycd'];
$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];
$subTitle = $_POST['subtitle'];
$startDate = $_POST['startdate'];//一覧：当日　履歴(直近1ヶ月):当日　履歴(カレンダー):なし
$selectDate = $_POST['selectdate'];//履歴(カレンダー)
$historyFlg = $_POST['historyflg'];//0:当日；1:履歴；
$noticeType = $_POST['noticetype'];//0:全部；1:アラート；2:支援要請；

function getNoticeInfo($conn, $startDate, $selectDate, $facilityCd, $sendUser, $historyFlg, $noticeType, &$code, &$errors)
{
    //履歴　直近1ヶ月
    if (is_empty($selectDate)) {
        if ($historyFlg == '1') {
            $endDate = date('Y-m-d', strtotime('-1 month', strtotime($startDate)));
            $conditionSql = " AND CONVERT(CHAR(10),nt.registdate,120)<='$startDate' AND CONVERT(CHAR(10),nt.registdate,120)>='$endDate' ";
        } //当日
        else {
            $conditionSql = " AND CONVERT(CHAR(10),nt.registdate,120)='$startDate' ";
        }
    } //履歴　カレンダー指定日
    else {
        $conditionSql = " AND CONVERT(CHAR(10),nt.registdate,120)='$selectDate' ";
    }
    // 直近1ヶ月
    if ($noticeType == '0') {
//        $sql = "
//            SELECT noticeid,title,senduser,username,groupid,groupname,noticetype,status,confirmuser,content,
//            CONVERT(CHAR(19),registdate,120) registdate FROM (
//            SELECT nt.noticeid,nt.title,nt.senduser,sm.username,nt.receiveuser groupid,'' groupname,nt.noticetype,
//            nt.status,nt.confirmuser,nt.content,CONVERT(CHAR(19),nt.registdate,120) registdate
//            FROM AZW121_noticetbl nt LEFT OUTER JOIN AZW004_staffmst sm ON nt.senduser=sm.userid AND sm.usertype='1'
//            WHERE nt.noticetype='2' $conditionSql
//            UNION
//            SELECT nt.noticeid,nt.title,nt.senduser,cm.username,nt.receiveuser groupid,sm.username groupname,nt.noticetype,
//            nt.status,nt.confirmuser,nt.content,CONVERT(CHAR(19),nt.registdate,120) registdate
//            FROM AZW121_noticetbl nt LEFT OUTER JOIN AZW004_staffmst sm ON sm.userid=nt.receiveuser AND sm.usertype='1'
//            LEFT OUTER JOIN AZW005_custmst cm ON cm.userid=nt.senduser
//            WHERE  nt.noticetype='1' AND nt.receiveuser='$sendUser' $conditionSql) s
//            ORDER BY s.noticetype ASC,s.status ASC,s.registdate DESC";
    } else if ($noticeType == '1') {
        $sql = "SELECT nt.noticeid,nt.title,nt.senduser,cm.custname,nt.receiveuser,sm.staffname groupname,nt.noticetype,
                nt.status,nt.confirmuser,nt.content,CONVERT(CHAR(19),nt.registdate,120) registdate,subtitle
                FROM AZW121_noticetbl nt INNER JOIN (SELECT DISTINCT sr.staffid,fv.roomcd,fv.custid,fv.custname
                FROM AZW001_frscview fv INNER JOIN AZW007_staffrelation sr ON sr.facilitycd=fv.facilitycd
                WHERE fv.facilitycd='$facilityCd') fs ON fs.staffid=nt.receiveuser AND fs.custid=nt.senduser
                LEFT OUTER JOIN AZW004_staffmst sm ON sm.staffid=nt.receiveuser
                LEFT OUTER JOIN AZW005_custmst cm ON cm.custid=nt.senduser
                WHERE  nt.noticetype='1' AND nt.receiveuser='$sendUser' $conditionSql
                ORDER BY nt.noticetype ASC,nt.status ASC,nt.registdate DESC";
    } else if ($noticeType == '2') {
//        $sql = "SELECT nt.noticeid,nt.title,nt.senduser,sm.username,nt.receiveuser groupid,'' groupname,nt.noticetype,
//                nt.status,nt.confirmuser,nt.content,CONVERT(CHAR(19),nt.registdate,120) registdate
//                FROM AZW121_noticetbl nt LEFT OUTER JOIN AZW004_staffmst sm ON nt.senduser=sm.userid AND sm.usertype='1'
//                WHERE nt.noticetype='2' $conditionSql
//                ORDER BY nt.noticetype ASC,nt.status ASC,nt.registdate DESC";
    }

    $result = sqlsrv_query($conn, $sql);
    $notices = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $notices[$index] = array(
                'noticeid' => $row[0],
                'title' => $row[1],
                'staffid' => $row[2],
                'username' => $row[3],
                'groupid' => $row[4],
                'groupname' => $row[5],
                'type' => $row[6],
                'status' => $row[7],
                'staupduser' => $row[8],
                'content' => $row[9],
                'registdate' => $row[10],
                'subtitle' => $row[11]
            );
            $index = $index + 1;
        }
    } else {
        $code = '502';
        $errors = sqlsrv_errors();
    }

    return $notices;
}

function getAlertInfo($conn, $sendUser, $subTitle, &$code, &$errors)
{
    $notices = array();

    if (!is_empty($subTitle)) {
        $subTitle = "AND nt.subtitle='$subTitle'";
    }

    $sql = "SELECT CONVERT(CHAR(19),nt.registdate,120) registdate,s.scenarioname,s.devicename,s.value,s.unit,s.time,s.rpoint,s.roomname,s.nodeid,s.scenarioid
            FROM AZW121_noticetbl nt LEFT OUTER JOIN AZW004_staffmst sm ON sm.staffid=nt.receiveuser
            LEFT OUTER JOIN AZW005_custmst cm ON cm.custid=nt.senduser
            INNER JOIN (SELECT zrm.roomname,zrm.nodeid,sd.scenarioid,sd.scenarioname,zrm.devicename,zrm.unit,ISNULL(CAST(sd.time AS VARCHAR(30)),'-') time,
            ISNULL(CAST(sd.value AS VARCHAR(30)),'-') value,ISNULL(sd.rpoint,CASE WHEN zrm.pattern = 1 THEN '反応なし'
            WHEN zrm.pattern = 2 THEN '使用なし' ELSE '-' END) rpoint,zrm.displayname,zrm.nodename,zrm.zi
            FROM (SELECT roomcd roomname,nodeid,nodename,displayname,deviceid,devicename,devicetype,CASE WHEN deviceclass = 1 AND devicetype = 4 THEN 1
            WHEN deviceclass = 1 AND devicetype = 5 THEN 2 ELSE 3 END pattern,unit,ROW_NUMBER() OVER(PARTITION BY nodeid
            ORDER BY deviceclass,devicetype) zi FROM AZW230_sensormstview WHERE deviceclass != '9'
            AND deviceclass IS NOT NULL AND custid='$sendUser') zrm INNER JOIN (SELECT si.scenarioid,si.scenarioname,sd.deviceid,sd.pattern,sd.time,sd.value,sd.rpoint
            FROM AZW141_scenarioinfo si,AZW142_scenariodtl sd WHERE si.scenarioid=sd.scenarioid) sd ON zrm.deviceid = sd.deviceid) s
            ON s.scenarioid=nt.subtitle WHERE nt.noticetype='1' AND nt.status='0' AND nt.senduser='$sendUser' $subTitle
            GROUP BY nt.noticetype,nt.status,s.nodename,nt.registdate,s.scenarioname,s.devicename,s.value,s.unit,s.time,s.rpoint,s.roomname,s.nodeid,s.scenarioid
            ORDER BY nt.noticetype ASC,nt.status ASC,nt.registdate DESC,s.nodename";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $index2 = 0;
        $di = array();
        $snId = '';
        while ($row = sqlsrv_fetch_array($result)) {
            if (!is_empty($snId) && $snId != $row[8] . $row[9]) {
                $notices[$index2] = $di;
                $di = array();
                $index = 0;
                $index2 = $index2 + 1;
            }
            $di[$index] = array(array(
                'registdate' => $row[0],
                'scenarioname' => $row[1],
                'devicename' => $row[2],
                'value' => $row[3],
                'unit' => $row[4],
                'time' => $row[5],
                'rpoint' => $row[6],
                'roomname' => $row[7]
            ));
            $index = $index + 1;
            $snId = $row[8] . $row[9];
        }
        $notices[$index2] = $di;
    } else {
        $code = '502';
        $errors = sqlsrv_errors();
    }

    return $notices;
}

function getCurFacilityCd($conn, $sendUser, &$code, &$errors)
{
    $sql = "SELECT TOP 1 facilitycd FROM AZW001_frscview WHERE custid = '$sendUser'";

    $result = sqlsrv_query($conn, $sql);
    $facilityCd = '';

    if ($result && $row = sqlsrv_fetch_array($result)) {
        $facilityCd = $row[0];
    } else {
        $code = '502';
        $errors = sqlsrv_errors();
    }

    return $facilityCd;
}

if ($conn) {
//    $staffId = 'sw00001';
//    $customerId = '00001';
//    $subTitle = 'sg00005000070002';
//    $startDate = '2017-04-28';//一覧：当日　履歴(直近1ヶ月):当日　履歴(カレンダー):なし
//    $selectDate = '';//履歴(カレンダー)
//    $historyFlg = '0';//0:当日；1:履歴；
//    $noticeType = '1';//0:全部；1:アラート；2:支援要請；

//    if (!is_empty($customerId) && $noticeType == '1') {
    if (!is_empty($customerId)) {
        $notices = getAlertInfo($conn, $customerId, $subTitle, $code, $errors);
        $facilityCd = getCurFacilityCd($conn, $customerId, $code, $errors);
    } else if (!is_empty($staffId)) {
//        $notices = getNoticeInfo($conn, $startDate, $selectDate, $staffId, $historyFlg, $noticeType, $code, $errors);
        $notices = getNoticeInfo($conn, $startDate, $selectDate, $facilityCd, $staffId, $historyFlg, '1', $code, $errors);
    }
//    else if ($noticeType == '0' || $noticeType == '1' || $noticeType == '2') {
//        //当日は全部
//        if (is_empty($selectDate) && $historyFlg != '1') {
//            $noticeType = '0';
//        }
//        $notices = getNoticeInfo($conn, $startDate, $selectDate, $staffId, $historyFlg, $noticeType, $code, $errors);
//    }
    else {
        $code = '501';
        $errors = array('');
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;
$arrReturn['notices'] = $notices;
$arrReturn['facilitycd'] = $facilityCd;

sendResponse(json_encode($arrReturn));

?>
