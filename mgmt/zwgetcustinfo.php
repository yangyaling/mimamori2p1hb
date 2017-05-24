<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/5/3
 * Time: 13:02
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$baseInfo = array();
$customerList = array();
$floorList = array();
//$roomList = array();
$maxCustomerId = '';

$facilityCd = $_POST['facilitycd'];

function getMaxCustomerId($conn)
{
    $maxCustomerId = '00001';

    $sql = "SELECT RIGHT('00000' + CAST(MAX(cm.custid) + 1 AS VARCHAR), 5) maxcustid FROM AZW005_custmst cm";
    $result = sqlsrv_query($conn, $sql);

    if ($result && $row = sqlsrv_fetch_array($result)) {
        if (!is_empty($row[0])) {
            $maxCustomerId = $row[0];
        }
    }
    return $maxCustomerId;
}

function getFloorList($conn, $facilityCd)
{
    $floorList = array();

    $sql = "SELECT floorno FROM AZW134_roommst WHERE facilitycd = '$facilityCd' GROUP BY facilitycd,floorno";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $floorList[$index] = array(
                'floorno' => $row[0],
                'roomlist' => getRoomList($conn, $facilityCd, $row[0])
            );
            $index = $index + 1;
        }
    }
    return $floorList;
}

function getRoomList($conn, $facilityCd, $floorNo)
{
    $roomList = array();

    $sql = "SELECT roomcd FROM AZW134_roommst WHERE facilitycd = '$facilityCd' AND floorno = '$floorNo' GROUP BY facilitycd,floorno,roomcd";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $roomList[$index] = array(
                'roomcd' => $row[0]
            );
            $index = $index + 1;
        }
    }
    return $roomList;
}

if ($conn) {
//    $facilityCd = '0001';

    $sql = "SELECT DISTINCT cr.custid,cm.custname,cr.roomcd,fv.floorno FROM AZW008_custrelation cr
            INNER JOIN AZW005_custmst cm ON cr.custid=cm.custid
            INNER JOIN AZW001_frscview fv ON cr.custid=fv.custid AND cr.roomcd=fv.roomcd AND cr.floorno=fv.floorno
            WHERE fv.facilitycd='$facilityCd'";

    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $customerList[$index] = array(
                'custid' => $row[0],
                'oldcustid' => $row[0],
                'custname' => $row[1],
                'oldcustname' => $row[1],
                'roomcd' => $row[2],
                'oldroomcd' => $row[2],
                'floorno' => $row[3],
                'oldfloorno' => $row[3]
            );
            $index = $index + 1;
        }
    }

    $baseInfo = getBaseInfo($conn, $facilityCd);
    $maxCustomerId = getMaxCustomerId($conn);
    $floorList = getFloorList($conn, $facilityCd);
//    $roomList = getRoomList($conn, $facilityCd);
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['baseinfo'] = $baseInfo;
$arrReturn['custlist'] = $customerList;
$arrReturn['maxcustid'] = $maxCustomerId;
$arrReturn['floorlist'] = $floorList;
//$arrReturn['roomlist'] = $roomList;

sendResponse(json_encode($arrReturn));

?>