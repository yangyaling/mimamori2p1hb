<?php
/**
 * Created by PhpStorm.
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

$companyList = json_decode($_POST['companylist'], true);

if ($conn && sqlsrv_begin_transaction($conn) && !is_empty($companyList)) {
    foreach ($companyList as $data) {
        $cd = $data['cd'];
        $name = $data['name'];
        $kana = $data['kana'];
        $initial = $data['initial'];

        $sql = "SELECT 1 FROM AZW001_companymst WHERE companycd='$cd'";
        $result = sqlsrv_query($conn, $sql);

        if (sqlsrv_has_rows($result)) {
            $sql = "UPDATE AZW001_companymst SET companyname='$name',companynamekana='$kana',initial='$initial' WHERE companycd='$cd'";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $code = '501';
                $errors = sqlsrv_errors();
                break;
            }
        } else {
            if (!is_empty($cd) && !is_empty($initial)) {
                $sql = "INSERT INTO AZW001_companymst(companycd,companyname,companynamekana,initial)VALUES('$cd','$name','$kana','$initial')";

                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $code = '502';
                    $errors = sqlsrv_errors();
                    break;
                }
            } else {
                $code = '503';
                $errors = array('param error.');
            }
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