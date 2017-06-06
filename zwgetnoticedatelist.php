<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/14
 * Time: 13:50
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$arrReturn['code'] = '200';

$arrReturn['datelist0'] = array();
$arrReturn['datelist1'] = array();
$arrReturn['datelist2'] = array();

$facilityCd = $_POST['facilitycd'];
$staffId = $_POST['staffid'];

if ($conn) {
//    $sql = "SELECT REPLACE(CONVERT(VARCHAR(10),s.registdate,120),'-0','-') noticedate,s.noticetype FROM (
//            SELECT nt.registdate,nt.noticetype FROM AZW121_noticetbl nt,AZW101_usermst um
//            WHERE nt.noticetype='1' AND nt.senduser='$staffId' AND um.userid=nt.senduser AND um.usertype='1'
//            UNION
//            SELECT nt.registdate,nt.noticetype FROM AZW121_noticetbl nt,AZW101_usermst um
//            WHERE nt.noticetype='2' AND nt.senduser=um.userid AND um.usertype='1' ) s
//            GROUP BY convert(varchar(10),s.registdate,120),s.noticetype";
    $sql = "SELECT REPLACE(CONVERT(VARCHAR(10),nt.registdate,120),'-0','-'),nt.noticetype
            FROM AZW121_noticetbl nt,AZW004_staffmst um,(SELECT DISTINCT sr.staffid,fv.roomcd,fv.custid,fv.custname
            FROM AZW001_frscview fv INNER JOIN AZW007_staffrelation sr ON sr.facilitycd=fv.facilitycd
            WHERE fv.facilitycd='$facilityCd') fs WHERE nt.noticetype='1' AND nt.receiveuser='$staffId'
            AND um.staffid=nt.receiveuser AND fs.staffid=nt.receiveuser AND fs.custid=nt.senduser
            GROUP BY CONVERT(VARCHAR(10),nt.registdate,120),nt.noticetype";

    $result = sqlsrv_query($conn, $sql);
    $dateList0 = array();
    $dateList1 = array();
    $dateList2 = array();

    if ($result) {
        $index0 = 0;
        $index1 = 0;
        $index2 = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $dateList0[$index0] = $row[0];
            if ($row[1] == '1') {
                $dateList1[$index1] = $row[0];
            }
            if ($row[1] == '2') {
                $dateList2[$index2] = $row[0];
            }
            $index0 = $index0 + 1;
            $index1 = $index1 + 1;
            $index2 = $index2 + 1;
        }
    }
    $arrReturn['datelist0'] = $dateList0;
    $arrReturn['datelist1'] = $dateList1;
    $arrReturn['datelist2'] = $dateList2;
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>