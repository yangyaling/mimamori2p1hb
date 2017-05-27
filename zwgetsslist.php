<?php
/**
 * センサー置き場所と一覧とシナリオ一覧取得。.
 * User: yuqy
 * Date: 2016/06/15
 * Time: 16:26
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';

$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];

$scenarioList = array();
$sensorPlaceList = array();
$displayList = array();

function getScenarioList($conn, $staffId, $customerId, &$code)
{
    $sql = "SELECT scenarioid,scenarioname,CONVERT(VARCHAR(19),updatedate,120) updatedate,scopecd,CONVERT(VARCHAR(5),starttime,108) starttime,CONVERT(VARCHAR(5),endtime,108) endtime
            FROM AZW141_scenarioinfo WHERE staffid='$staffId' AND custid='$customerId'";

    $result = sqlsrv_query($conn, $sql);
    $scenarioList = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $scenarioList[$index] = array(
                'scenarioid' => $row[0],
                'scenarioname' => $row[1],
                'updatedate' => $row[2],
                'scopecd' => $row[3],
                'starttime' => $row[4],
                'endtime' => $row[5]
            );
            $index = $index + 1;
        }
    } else {
        $code = 501;
    }

    return $scenarioList;
}

function getSensorPlaceList($conn, $staffId, $customerId, &$code)
{
    $sql = "SELECT DISTINCT smv.roomcd,smv.gatewayid,smv.nodeid,smv.nodename,smv.displayname,smv.place,smv.placename,
            ISNULL(smv.nodetype,'') nodetype,ut.mainnodeid,smv.norder,smv.displaycd,smv.memo,smv.serial,smv.startdate,smv.initflag
            FROM AZW230_sensormstview smv INNER JOIN AZW001_frscview ut ON ut.staffid='$staffId' AND ut.custid='$customerId'
            AND ut.roomcd=smv.roomcd AND ut.floorno=smv.floorno AND smv.nodetype IS NOT NULL
            AND smv.startdate<=CONVERT(VARCHAR(10)," . SCH . ".GETJPDATE(),120) AND smv.enddate IS NULL ORDER BY smv.norder";

    $result = sqlsrv_query($conn, $sql);
    $sensorPlaceList = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $sensorPlaceList[$index] = array(
                'roomid' => $row[0],
                'gatewayid' => $row[1],
                'nodeid' => $row[2],
                'nodename' => $row[3],
                'olddisplayname' => $row[4],
                'displayname' => $row[4],
                'oldplace' => $row[5],
                'place' => $row[5],
                'oldplacename' => $row[6],
                'placename' => $row[6],
                'nodetype' => $row[7],
                'mainnodeid' => $row[8],
                'olddisplaycd' => $row[10],
                'displaycd' => $row[10],
                'memo' => $row[11],
                'oldmemo' => $row[11],
                'serial' => $row[12],
                'startdate' => $row[13],
                'initflag' => $row[14]
            );
            $index = $index + 1;
        }
    } else {
        $code = 501;
    }

    return $sensorPlaceList;
}

if ($conn) {
//    $staffId = 'sw00001';
//    $customerId = '00002';
    $scenarioList = getScenarioList($conn, $staffId, $customerId, $code);
    $sensorPlaceList = getSensorPlaceList($conn, $staffId, $customerId, $code);
    $displayList = getCVList($conn, CLASS_NODE_LOCATION);
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['scenariolist'] = $scenarioList;
$arrReturn['sensorplacelist'] = $sensorPlaceList;
$arrReturn['displaylist'] = $displayList;

sendResponse(json_encode($arrReturn));
?>