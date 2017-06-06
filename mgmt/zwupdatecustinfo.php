<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/5/3
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$customerList = json_decode($_POST['custlist'], true);

//if ($conn && sqlsrv_begin_transaction($conn) && !is_empty($customerList)) {
if ($conn && !is_empty($customerList)) {
    foreach ($customerList as $data) {
        $customerId = $data['custid'];
        $customerName = $data['custname'];
        $roomCd = $data['roomcd'];
        $floorNo = $data['floorno'];

        $sql = "SELECT 1 FROM AZW005_custmst WHERE custid='$customerId'";
        $result = sqlsrv_query($conn, $sql);

        if (sqlsrv_has_rows($result)) {
            $sql = "UPDATE AZW005_custmst SET custname='$customerName' WHERE custid='$customerId'";;

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $code = '501';
                $errors = sqlsrv_errors();
                break;
            }
        } else if (!is_empty($customerId)) {
            $sql = "INSERT INTO AZW005_custmst(custid,custname,updatedate)VALUES('$customerId','$customerName',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120))";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $code = '502';
                $errors = sqlsrv_errors();
                break;
            }
        } else {
            $code = '601';
            $errors = sqlsrv_errors();
            break;
        }

        if ($code == '200') {
            if (!is_empty($customerId) && !is_empty($roomCd)) {
                $sql = "DELETE FROM AZW008_custrelation WHERE custid='$customerId';
                       INSERT INTO AZW008_custrelation(custid,roomcd,floorno)VALUES('$customerId','$roomCd','$floorNo');";

                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $code = '503';
                    $errors = sqlsrv_errors();
                    break;
                }
            } else {
                $code = '602';
                $errors = array($customerId, $roomCd);
                break;
            }
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

//if ($code == '200') {
//    if (!sqlsrv_commit($conn)) {
//        sqlsrv_rollback($conn);
//    }
//} else {
//    sqlsrv_rollback($conn);
//}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>