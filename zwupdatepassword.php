<?php
/**
 * パスワード変更
 * User: RDX64
 * Date: 2017/5/22
 * Time: 13:02
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$staffId = $_POST['staffid'];
$oldPwd = $_POST['oldpwd'];
$newPwd = $_POST['newpwd'];
$confirmPwd = $_POST['confirmpwd'];

if ($conn) {
    if (is_empty($newPwd) || is_empty($confirmPwd) || $newPwd != $confirmPwd) {
        $code = '501';
        $errors = array('param is error.');
    } else {
        $sql = "SELECT password FROM AZW004_staffmst WHERE staffid='$staffId'";

        if ($result = sqlsrv_query($conn, $sql)) {
            if ($row = sqlsrv_fetch_array($result)) {
                if ($oldPwd == $row[0]) {
                    $sql = "UPDATE AZW004_staffmst SET password='$newPwd' WHERE staffid='$staffId'";

                    $result = sqlsrv_query($conn, $sql);
                    if (!$result) {
                        $code = '505';
                        $errors = sqlsrv_errors();
                    }
                } else {
                    $code = '504';
                    $errors = array('old password is error.');
                }
            } else {
                $code = '503';
                $errors = array('staff is not exists..');
            }
        } else {
            $code = '502';
            $errors = sqlsrv_errors();
        }
    }
} else {
    $code = '500';
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