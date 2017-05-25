<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/4/26
 * Time: 16:02
 * 利用施設一覧(施設切替えボタン用)
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['facilitylist'] = '';

$staffId = $_POST['staffid'];
$hostCd = $_POST['hostcd'];

if ($conn) {

    $sql = "SELECT fv.facilitycd,fv.facilityname2 FROM AZW001_frscview fv WHERE staffid = '$staffId' AND fv.hostcd = '$hostCd'
            GROUP BY fv.facilitycd,fv.facilityname2 ORDER BY fv.facilitycd";

    $result = sqlsrv_query($conn, $sql);
    $facilityList = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $facilityList[$index] = array(
                'facilitycd' => $row[0],
                'hostcd' => $hostCd,
                'facilityname2' => $row[2]
            );
            $index = $index + 1;
        }
    }

    $arrReturn['facilitylist'] = $facilityList;
} else {
    $arrReturn['code'] = '500';
    $arrReturn['errors'] = sqlsrv_errors();
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>