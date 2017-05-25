<?php
/**
 * 居室情報
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

$facilityCd = $_POST['facilitycd'];
$roomList = json_decode($_POST['roomlist'], true);

if ($conn && sqlsrv_begin_transaction($conn)) {
    if (!is_empty($facilityCd) && !is_empty($roomList)) {
        foreach ($roomList as $data) {
            $roomCd = $data['roomcd'];
            $floorNo = $data['floorno'];
            $oldRoomCd = $data['oldroomcd'];
            $oldFloorNo = $data['oldfloorno'];

            if (!is_empty($roomCd) && !is_empty($floorNo)) {
                $sql = "SELECT 1 FROM AZW134_roommst WHERE roomcd='$oldRoomCd' AND facilitycd='$facilityCd' AND floorno='$oldFloorNo'";
                if (sqlsrv_has_rows(sqlsrv_query($conn, $sql))) {
                    if ($roomCd != $oldRoomCd || $floorNo != $oldFloorNo) {
                        $sql = "SELECT TOP 1 rm.roomcd,rm.floorno,cr.roomcd FROM AZW134_roommst rm
                        LEFT OUTER JOIN AZW008_custrelation cr ON rm.roomcd=cr.roomcd AND rm.floorno=cr.floorno
                        WHERE rm.roomcd='$oldRoomCd' AND rm.facilitycd='$facilityCd' AND rm.floorno='$oldFloorNo' AND cr.roomcd IS NOT NULL";

                        if (!sqlsrv_has_rows(sqlsrv_query($conn, $sql))) {
                            $sql = "UPDATE AZW134_roommst SET roomcd='$roomCd',floorno='$floorNo' WHERE roomcd='$oldRoomCd' AND facilitycd='$facilityCd' AND floorno='$oldFloorNo'";
                            if (!$result = sqlsrv_query($conn, $sql)) {
                                $code = '501';
                                $errors = sqlsrv_errors();
                                break;
                            }
                        } else {
                            $code = '502';
                            $errors = array('居室情報（他機能）にて使用されている場合、変更は不可');
                            break;
                        }
                    }
                } else {
                    $sql = "INSERT INTO AZW134_roommst(roomcd,floorno,facilitycd,updateuser,updatedate)
                            VALUES('$roomCd','$floorNo','$facilityCd','admin',GETDATE())";
                    if (!$result = sqlsrv_query($conn, $sql)) {
                        $code = '503';
                        $errors = sqlsrv_errors();
                        break;
                    }
                }
            } else {
                $code = '502';
                $errors = array('paraments error.');
                break;
            }
        }
    } else {
        $code = '600';
        $errors = array('paraments error.');
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

if ($code == '200') {
    if (!sqlsrv_commit($conn)) {
        sqlsrv_rollback($conn);
    }
} else {
    sqlsrv_rollback($conn);
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>