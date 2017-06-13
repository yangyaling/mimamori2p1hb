<?php
/**
 * 居室一覧
 * User: RDX64
 * Date: 2017/5/5
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';

$baseInfo = array();
$roomList = array();

$facilityCd = $_POST['facilitycd'];

if ($conn) {
//    $facilityCd = '0001';

    $sql = "SELECT roomcd,floorno FROM AZW134_roommst WHERE facilitycd='$facilityCd' ORDER BY facilitycd,floorno,roomcd";
    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $roomList[$index] = array(
                'roomcd' => $row[0],
                'oldroomcd' => $row[0],
                'floorno' => $row[1],
                'oldfloorno' => $row[1]
            );
            $index = $index + 1;
        }
    }

    $baseInfo = getBaseInfo($conn, $facilityCd);
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['baseinfo'] = $baseInfo;
$arrReturn['roomlist'] = $roomList;

sendResponse(json_encode($arrReturn));

?>