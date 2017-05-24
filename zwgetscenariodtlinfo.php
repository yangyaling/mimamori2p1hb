<?php
/**
 * シナリオ情報取得。
 * User: yuqy
 * Date: 2016/06/15
 * Time: 17:26
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';

$facilityCd = $_POST['facilitycd'];
$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];
$roomCd = $_POST['roomid'];
$scenarioId = $_POST['scenarioid'];

$scenarioDtlInfo = array();
$protoInfo = array();

function getScenarioDtlInfo($conn, $roomCd, $customerId, $scenarioId)
{
    $scenarioDtlInfo = array();

    $sql = "SELECT zrm.deviceid,zrm.devicename,zrm.devicetype,zrm.pattern,zrm.unit,ISNULL(CAST(sd.time AS VARCHAR(30)),'-') time,
            ISNULL(CAST(sd.value AS VARCHAR(30)),'-') value,ISNULL(sd.rpoint,CASE WHEN zrm.pattern = 1 THEN '反応なし'
            WHEN zrm.pattern = 2 THEN '使用なし' ELSE '-' END) rpoint,zrm.nodeid,zrm.nodename,ISNULL(sd.detailno,'') detailno,zrm.displayname,zrm.nodetype
            FROM (SELECT nodeid,nodename,nodetype,displayname,deviceid,devicename,devicetype,
            CASE WHEN deviceclass = 1 AND devicetype = 4 THEN 1 WHEN deviceclass = 1 AND devicetype = 5 THEN 2 ELSE 3 END pattern,
            unit,ROW_NUMBER() OVER(PARTITION BY nodeid ORDER BY deviceclass,devicetype) zi,norder nodeorder FROM AZW230_sensormstview
            WHERE roomcd='$roomCd' AND custid='$customerId' AND deviceclass != '9' AND deviceclass IS NOT NULL
            AND initflag=1 AND startdate <= CONVERT(VARCHAR(10),GETDATE(),120) AND enddate IS NULL) zrm LEFT OUTER JOIN
            (SELECT detailno,deviceid,pattern,time,value,rpoint FROM AZW142_scenariodtl WHERE scenarioid='$scenarioId' ) sd
            ON zrm.deviceid = sd.deviceid ORDER BY zrm.nodeorder,zrm.zi";

    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $index = 0;
        $index2 = 0;
        $di = array();
        $nodeId = '';
        while ($row = sqlsrv_fetch_array($result)) {
            if (!is_empty($nodeId) && $nodeId != $row[8]) {
                $scenarioDtlInfo[$index2] = $di;
                $di = array();
                $index = 0;
                $index2 = $index2 + 1;
            }
            $di[$index] = array(
                'detailno' => $row[10],
                'nodeid' => $row[8],
                'nodename' => $row[9],
                'nodetype' => $row[12],
                'displayname' => $row[11],
                'deviceid' => $row[0],
                'devicename' => $row[1],
                'devicetype' => $row[2],
                'pattern' => $row[3],
                'time' => $row[5],
                'timeunit' => 'H',
                'value' => $row[6],
                'valueunit' => $row[4],
                'rpoint' => $row[7]
            );
            $index = $index + 1;
            $nodeId = $row[8];
        }
        $scenarioDtlInfo[$index2] = $di;
    }
    return $scenarioDtlInfo;
}

function getProtoInfo($conn)
{
    $protoInfo = array();

    $sql = "SELECT protoid,protoname,scopecd,CONVERT(VARCHAR(5),starttime,108) starttime,CONVERT(VARCHAR(5),endtime,108) endtime,nodetype,devicetype,pattern,[time],[value],rpoint
            FROM AZW006_scenarioprotomst ORDER BY protoid,devicetype";

    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $index = 0;
        $index2 = 0;
        $pi = array();
        $protoId = '';
        while ($row = sqlsrv_fetch_array($result)) {
            if (!is_empty($protoId) && $protoId != $row[0]) {
                $protoInfo[$index2] = $pi;
                $pi = array();
                $index = 0;
                $index2 = $index2 + 1;
            }
            $pi[$index] = array(
                'protoid' => $row[0],
                'protoname' => $row[1],
                'scopecd' => $row[2],
                'starttime' => $row[3],
                'endtime' => $row[4],
                'nodetype' => $row[5],
                'devicetype' => $row[6],
                'pattern' => $row[7],
                'time' => $row[8],
                'value' => $row[9],
                'rpoint' => $row[10]
            );
            $index = $index + 1;
            $protoId = $row[0];
        }
        $protoInfo[$index2] = $pi;
    }
    return $protoInfo;
}

if ($conn) {
//    $roomCd = '201号室';
//    $customerId = '00002';
//    $scenarioId = '000100010001';

    $scenarioDtlInfo = getScenarioDtlInfo($conn, $roomCd, $customerId, $scenarioId);
    $protoInfo = getProtoInfo($conn);
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['scenariodtlinfo'] = $scenarioDtlInfo;
$arrReturn['protoinfo'] = $protoInfo;

sendResponse(json_encode($arrReturn));

?>