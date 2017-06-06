<?php
/**
 * アラート情報取得。
 * User: NITIOSRD
 * Date: 2017/2/16
 * Time: 11:04
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$hostCd = $_POST['hostcd'];
$facilityCd = $_POST['facilitycd'];
$floorNo = $_POST['floorno'];
$staffId = $_POST['staffid'];

$alertInfo = array();
$historyInfo = array();

//$facilityCd = '0001';
//$floorNo = 'A棟2';
//$staffId = 'sw00001';

if ($conn) {
    //alert
    if ($facilityCd == DEFAULT_FACILITY_CD) {
        $sql = "SELECT fs.roomcd,fs.custid,fs.custname,CONVERT(VARCHAR(19),nt.registdate,120) registdate,fs.staffid,sm.staffname,fs.facilityname2,fs.floorno
                FROM AZW121_noticetbl nt INNER JOIN (SELECT DISTINCT sr.staffid,fv.roomcd,fv.custid,fv.custname,fv.facilityname2,fv.floorno FROM AZW001_frscview fv
                INNER JOIN AZW007_staffrelation sr ON sr.facilitycd=fv.facilitycd WHERE fv.hostcd='$hostCd') fs
                ON fs.staffid=nt.receiveuser AND fs.custid=nt.senduser LEFT OUTER JOIN AZW004_staffmst sm ON sm.staffid=fs.staffid
                WHERE nt.noticetype='1' AND nt.status='0' ORDER BY fs.roomcd,nt.registdate DESC";
    } else {
        $sql = "SELECT fs.roomcd,fs.custid,fs.custname,CONVERT(VARCHAR(19),nt.registdate,120) registdate,fs.staffid,sm.staffname,fs.facilityname2,fs.floorno
                FROM AZW121_noticetbl nt INNER JOIN (SELECT DISTINCT sr.staffid,fv.roomcd,fv.custid,fv.custname,fv.facilityname2,fv.floorno FROM AZW001_frscview fv
                INNER JOIN AZW007_staffrelation sr ON sr.facilitycd=fv.facilitycd WHERE fv.facilitycd='$facilityCd') fs
                ON fs.staffid=nt.receiveuser AND fs.custid=nt.senduser LEFT OUTER JOIN AZW004_staffmst sm ON sm.staffid=fs.staffid
                WHERE nt.noticetype='1' AND nt.status='0' ORDER BY fs.roomcd,nt.registdate DESC";
    }

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $alertInfo[$index] = array(
                'roomid' => $row[0],
                'roomname' => $row[0],
                'userid0' => $row[1],
                'username0' => $row[2],
                'registdate' => $row[3],
                'userid1' => $row[4],
                'username1' => $row[5],
                'facilityname2' => $row[6],
                'floorno' => $row[7]
            );
            $index++;
        }
    } else {
        $code = '501';
        $errors = sqlsrv_errors();
    }

    //history
//    $sql = "SELECT fs.roomcd,nt.senduser,fs.custname,CONVERT(VARCHAR(19),nt.registdate,120) registdate FROM AZW152_vznoticetbl nt
//            INNER JOIN (SELECT DISTINCT sr.staffid,fv.roomcd,fv.custid,fv.custname FROM AZW001_frscview fv
//            INNER JOIN AZW007_staffrelation sr ON sr.facilitycd=fv.facilitycd WHERE fv.staffid='$staffId') fs
//            ON fs.staffid=nt.receiveuser AND fs.custid=nt.senduser WHERE nt.noticetype='S' AND nt.[status]='0'
//            GROUP BY fs.roomcd,nt.senduser,fs.custname,nt.registdate ORDER BY fs.roomcd,nt.registdate DESC";
    $sql = "SELECT fs.roomcd,nt.senduser,fs.custname,nt.registdate,fs.staffid,sm.staffname FROM (SELECT nt.senduser,
            MAX(CONVERT(VARCHAR(16),nt.registdate,120)) registdate,nt.[status],nt.receiveuser FROM AZW152_vznoticetbl nt
            WHERE nt.noticetype='S' AND nt.[status]='0' GROUP BY nt.senduser,nt.[status],nt.receiveuser) nt
            INNER JOIN (SELECT DISTINCT sr.staffid,fv.roomcd,fv.custid,fv.custname FROM AZW001_frscview fv
            INNER JOIN AZW007_staffrelation sr ON sr.facilitycd=fv.facilitycd WHERE fv.staffid='$staffId') fs
            ON fs.staffid=nt.receiveuser AND fs.custid=nt.senduser LEFT OUTER JOIN AZW004_staffmst sm ON sm.staffid=fs.staffid
            GROUP BY fs.roomcd,nt.senduser,fs.custname,nt.registdate,fs.staffid,sm.staffname ORDER BY fs.roomcd,nt.registdate DESC";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $historyInfo[$index] = array(
                'roomid' => $row[0],
                'roomname' => $row[0],
                'userid0' => $row[1],
                'username0' => $row[2],
                'registdate' => $row[3],
                'userid1' => $row[4],
                'username1' => $row[5]
            );
            $index++;
        }
    } else {
        $code = '501';
        $errors = sqlsrv_errors();
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

$arrReturn['alertinfo'] = $alertInfo;
$arrReturn['historyinfo'] = $historyInfo;

sendResponse(json_encode($arrReturn));

?>