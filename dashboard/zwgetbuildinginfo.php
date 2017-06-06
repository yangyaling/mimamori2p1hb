<?php
/**
 * 施設階層情報取得処理。
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
$staffId = $_POST['staffid'];

$buildingInfo = array();

if ($conn) {
//    $staffId = 'sw00001';
//    $hostCd = 'host01';
    $sql = "SELECT fv.facilitycd,fv.facilityname2,fv.floorno FROM AZW001_frscview fv WHERE staffid = '$staffId' AND fv.hostcd = '$hostCd'
            GROUP BY fv.facilitycd,fv.facilityname2,fv.floorno ORDER BY fv.facilitycd,fv.floorno";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $facilityCd = '';
        $facilityName2 = '';
        $floorInfo = array();

        $allFacility = array(
            'facilitycd' => DEFAULT_FACILITY_CD,
            'facilityname2' => DEFAULT_FACILITY_NAME,
            'floornoinfo' => array()
        );
        $buildingInfo[] = $allFacility;

        while ($row = sqlsrv_fetch_array($result)) {
            if (!is_empty($facilityCd) && $facilityCd != $row[0]) {
                $buildingInfo[] = array(
                    'facilitycd' => $facilityCd,
                    'facilityname2' => $facilityName2,
                    'floornoinfo' => $floorInfo
                );
                $floorInfo = array();
                $index = 0;
            }
            $floorInfo[$index] = array(
                'floorno' => $row[2]
            );
            $index = $index + 1;
            $facilityCd = $row[0];
            $facilityName2 = $row[1];
        }
        $buildingInfo[] = array(
            'facilitycd' => $facilityCd,
            'facilityname2' => $facilityName2,
            'floornoinfo' => $floorInfo
        );
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

$arrReturn['buildingInfo'] = $buildingInfo;

sendResponse(json_encode($arrReturn));

?>
