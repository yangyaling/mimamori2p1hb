<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/4/25
 * Time: 14:35
 * 施設情報を取得
 */

include '../../cmslib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['facilityinfo'] = '';

if ($conn) {

    $sql = "SELECT facilitycd, hostcd,areacd,facilityname1,facilityname1kana,facilityname2,facilityname2kana,floorcount,roomcount,bikou,updateuser,updatedate FROM AZW003_facilitymst";

    $result = sqlsrv_query($conn, $sql);
    $facilityInfo = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $facilityInfo[$index] = array(
                'facilitycd' => $row[0],
                'hostcd' => $row[1],
                'areacd' => $row[2],
                'facilityname1' => $row[3],
                'facilityname1kana' => $row[4],
                'facilityname2' => $row[5],
                'facilityname2kana' => $row[6],
                'floorcount' => $row[7],
                'roomcount' => $row[8],
                'bikou' => $row[9],
                'updateuser' => $row[10],
                'updatedate' => $row[11]
            );
            $index = $index + 1;
        }
    }
    $arrReturn['facilityinfo'] = $facilityInfo;
} else {
    $arrReturn['code'] = '500';
    $arrReturn['errors'] = sqlsrv_errors();
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>