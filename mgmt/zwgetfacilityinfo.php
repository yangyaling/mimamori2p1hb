<?php
/**
 * 施設情報
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$facilityInfo = array();

$facilityCd = $_POST['facilitycd'];

if ($conn) {
    $sql = "SELECT cm.companycd,cm.companyname,fm.hostcd,fm.facilitycd,fm.facilityname1,fm.facilityname1kana,
            fm.facilityname2,fm.facilityname2kana,fm.floorcount,fm.roomcount,fm.memo FROM AZW003_facilitymst fm
            INNER JOIN AZW002_hostmst hm ON fm.hostcd=hm.hostcd INNER JOIN AZW001_companymst cm ON hm.companycd=cm.companycd
            WHERE fm.facilitycd='$facilityCd'";
    $result = sqlsrv_query($conn, $sql);

    if ($result && $row = sqlsrv_fetch_array($result)) {
        $facilityInfo = array(
            'companycd' => $row[0],
            'companyname' => $row[1],
            'hostcd' => $row[2],
            'facilitycd' => $row[3],
            'facilityname1' => $row[4],
            'facilityname1kana' => $row[5],
            'facilityname2' => $row[6],
            'facilityname2kana' => $row[7],
            'floorcount' => $row[8],
            'roomcount' => $row[9],
            'memo' => $row[10],
        );
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['facilityinfo'] = $facilityInfo;

sendResponse(json_encode($arrReturn));

?>