<?php
/**
 * センサー情報をつける顧客情報またはセンサー情報をつけない顧客情報取得。
 * User: yuqy
 * Date: 2016/06/12
 * Time: 14:29
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';

$arrReturn['custlist'] = '';
//$arrReturn['sensormasterzworks'] = '';

$facilityCd = $_POST['facilitycd'];
$staffId = $_POST['staffid'];
$hasSensorData = $_POST['hassensordata'];

$customerList = array();
//$sensorMasterZWorks = array();

if ($conn) {
//    $facilityCd = '0001';
//    $staffId = 'sw00001';
//    $hasSensorData = '1';

    if ($hasSensorData == '1') {
        $sql = "SELECT t.custid,t.custname,t.dispname,t.resultname,t.roomcd,t.tvalue,t.tunit,t.bvalue,t.bunit,t.bd,t.picpath,t.picupdatedate,
                t.hvalue,t.hunit FROM (SELECT DISTINCT clv.floorno,clv.custid,clv.custname,clv.dispname,clv.resultname,clv.roomcd,clv.tvalue,
                clv.tunit,clv.bvalue,clv.bunit,clv.bd,clv.picpath,clv.picupdatedate,clv.hvalue,clv.hunit FROM AZW200_custstatuslistview clv
                INNER JOIN AZW230_sensormstview sv ON sv.custid=clv.custid AND sv.initflag=1 AND sv.startdate <= CONVERT(VARCHAR(10)," . SCH . ".GETJPDATE(),120)
                AND (sv.enddate IS NULL OR sv.enddate >= CONVERT(VARCHAR(10)," . SCH . ".GETJPDATE(),120)) WHERE clv.staffid='$staffId' AND clv.facilitycd='$facilityCd') t
                ORDER BY CASE WHEN t.resultname = '設定なし' THEN 1 ELSE 0 END, t.floorno,t.roomcd";
    } else {
        $sql = "SELECT ui.custid,ui.custname user0name,ut.mainnodename dispname,ut.roomcd,ut.picpath,ut.picupdatedate,sc.nodecnt,ut.floorno
                FROM AZW001_frscview ut LEFT OUTER JOIN AZW005_custmst ui ON ut.custid=ui.custid
                LEFT OUTER JOIN (SELECT roomcd,floorno,COUNT(DISTINCT nodeid) nodecnt FROM AZW230_sensormstview
                WHERE enddate IS NULL AND startdate <= CONVERT(VARCHAR(10)," . SCH . ".GETJPDATE(),120) AND nodetype IS NOT NULL
                GROUP BY roomcd,floorno) sc ON sc.roomcd=ut.roomcd AND sc.floorno=ut.floorno
                WHERE ut.staffid='$staffId' AND facilitycd='$facilityCd' ORDER BY ut.floorno,ut.roomcd";
    }

    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            if ($hasSensorData == '1') {
                $customerList[$index] = array(
                    'userid0' => $row[0],
                    'user0name' => $row[1],
                    'dispname' => $row[2],
                    'resultname' => $row[3],
                    'roomid' => $row[4],
                    'tvalue' => $row[5],
                    'tunit' => $row[6],
                    'bvalue' => $row[7],
                    'bunit' => $row[8],
                    'bd' => $row[9],
                    'picpath' => $row[10],
                    'picupdatedate' => $row[11],
                    'hvalue' => $row[12],
                    'hunit' => $row[13]
                );
            } else {
                $customerList[$index] = array(
                    'userid0' => $row[0],
                    'user0name' => $row[1],
                    'dispname' => $row[2],
                    'roomid' => $row[3],
                    'picpath' => $row[4],
                    'picupdatedate' => $row[5],
                    'sensorcount' => $row[6],
                    'floorno' => $row[7]
                );
            }
            $index = $index + 1;
        }
    }

//    if ($hasSensorData == '1' || $hasSensorData == '0') {
//        $sql = "SELECT ut.userid0,ut.userid1,zm.roomid,zm.deviceid,zm.devicename,zm.unit,zm.devicetype FROM AZW103_usertbl ut
//                LEFT OUTER JOIN AZW230_sensormstview zm ON zm.roomid=ut.roomid WHERE ut.userid1='$staffId'";
//        $result = sqlsrv_query($conn, $sql);
//
//        if ($result) {
//            $index = 0;
//            while ($row = sqlsrv_fetch_array($result)) {
//                $sensorMasterZWorks[$index] = array(
//                    'userid0' => $row[0],
//                    'userid1' => $row[1],
//                    'deviceid' => $row[3],
//                    'sensorname' => $row[4],
//                    'unit' => $row[5],
//                    'sensortype' => $row[6]
//                );
//                $index = $index + 1;
//            }
//        }
//    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['custlist'] = $customerList;
//$arrReturn['sensormasterzworks'] = $sensorMasterZWorks;

sendResponse(json_encode($arrReturn));

?>