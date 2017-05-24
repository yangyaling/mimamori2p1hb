<?php
/**
 * 施設情報.
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

$facilityInfo = json_decode($_POST['facilityinfo'], true);
//$staffId = ['staffid'];

if ($conn && !is_empty($facilityInfo)) {
    $companyCd = $facilityInfo['companycd'];
    $companyName = $facilityInfo['companyname'];
    $hostCd = $facilityInfo['hostcd'];
    $facilityCd = $facilityInfo['facilitycd'];
    $facilityName1 = $facilityInfo['facilityname1'];
    $facilityName1Kana = $facilityInfo['facilityname1kana'];
    $facilityName2 = $facilityInfo['facilityname2'];
    $facilityName2Kana = $facilityInfo['facilityname2kana'];
    $floorCount = $facilityInfo['floorcount'];
    $roomCount = $facilityInfo['roomcount'];
    $memo = $facilityInfo['memo'];

    $sql = "SELECT 1 FROM AZW003_facilitymst WHERE facilitycd='$facilityCd'";
    $result = sqlsrv_query($conn, $sql);

    if (sqlsrv_has_rows($result)) {
        $sql = "UPDATE AZW003_facilitymst
                SET hostcd='$hostCd',facilityname1='$facilityName1',facilityname1kana='$facilityName1Kana',
                facilityname2='$facilityName2',facilityname2kana='$facilityName2Kana',floorcount='$floorCount',
                roomcount='$roomCount',memo='$memo',updateuser='admin',updatedate=GETDATE()
                WHERE facilitycd='$facilityCd'";

        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '501';
            $errors = sqlsrv_errors();
        }
    } else {
        $sql = "INSERT INTO AZW003_facilitymst(facilitycd,hostcd,facilityname1,facilityname1kana,facilityname2,
                facilityname2kana,floorcount,roomcount,memo,updateuser,updatedate)
                VALUES('$facilityCd','$hostCd','$facilityName1','$facilityName1Kana','$facilityName2',
                '$facilityName2Kana','$floorCount','$roomCount','$memo','admin',GETDATE())";

        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '502';
            $errors = sqlsrv_errors();
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>