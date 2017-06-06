<?php
/**
 * 居室情報
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
//$errors = array();

$roomCd = $_POST['roomcd'];
$facilityCd = $_POST['facilitycd'];
$floorNo = $_POST['floorno'];

//$roomCd = '311号室';
if ($conn) {
    if (!is_empty($roomCd) && !is_empty($facilityCd) && !is_empty($floorNo)) {
        $sql = "SELECT TOP 1 rm.roomcd,rm.floorno,cr.roomcd FROM AZW134_roommst rm
                LEFT OUTER JOIN AZW008_custrelation cr ON rm.roomcd=cr.roomcd AND rm.floorno=cr.floorno
                WHERE rm.roomcd='$roomCd' AND rm.facilitycd='$facilityCd' AND rm.floorno='$floorNo' AND cr.roomcd IS NOT NULL";

        if (!sqlsrv_has_rows(sqlsrv_query($conn, $sql))) {
            $sql = "DELETE FROM AZW134_roommst WHERE roomcd='$roomCd' AND facilitycd='$facilityCd' AND floorno='$floorNo'";
            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $code = '503';
                $errors = sqlsrv_errors();
            }
        } else {
            $code = '502';
            $errors = array('居室情報（他機能）にて使用されている場合、削除は不可');
        }
    } else {
        $code = '501';
        $errors = array('paramenters were error.');
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