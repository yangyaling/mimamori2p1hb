<?php
/**
 * 機器情報一覧
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';

$baseInfo = array();
$ssList = array();
$customerList = array();

$facilityCd = $_POST['facilitycd'];

function getCustomerList($conn, $facilityCd)
{
    $customerList = array();

    $sql = "SELECT fv.custid,fv.custname FROM AZW001_frscview fv WHERE fv.facilitycd='$facilityCd' GROUP BY fv.custid,fv.custname";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $customerList[$index] = array(
                'custid' => $row[0],
                'custname' => $row[1]
            );
            $index = $index + 1;
        }
    }
    return $customerList;
}

if ($conn) {
//    INNER JOIN AZW136_zworksnodemst znm ON sr.serial = SUBSTRING(znm.nodename2,15,255)
//    $facilityCd = '0001';

    $sql = "SELECT sr.serial,sr.sensorid,ISNULL(sr.custid,'') custid,ISNULL(cm.custname,'') custname,sr.startdate
            ,ROW_NUMBER()OVER(ORDER BY CASE WHEN ISNULL(sr.custid,'') = '' THEN 1 ELSE 0 END,ISNULL(sr.custid,''),sr.sensorid) ri
            FROM AZW009_serialrelation sr LEFT OUTER JOIN AZW005_custmst cm ON sr.custid=cm.custid
            WHERE sr.facilitycd='$facilityCd' AND sr.enddate IS NULL";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $ssList[$index] = array(
                'serial' => hex2bin($row[0]),
                'oldserial' => hex2bin($row[0]),
                'sensorid' => $row[1],
                'oldsensorid' => $row[1],
                'custid' => $row[2],
                'oldcustid' => $row[2],
                'custname' => $row[3],
                'startdate' => $row[4]
            );
            $index = $index + 1;
        }
    }

    $baseInfo = getBaseInfo($conn, $facilityCd);
    $customerList = getCustomerList($conn, $facilityCd);
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['baseinfo'] = $baseInfo;
$arrReturn['sslist'] = $ssList;
$arrReturn['custlist'] = $customerList;

sendResponse(json_encode($arrReturn));

?>