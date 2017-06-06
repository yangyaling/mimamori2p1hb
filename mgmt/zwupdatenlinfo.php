<?php
/**
 * Created by PhpStorm.
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

$nlList = json_decode($_POST['nllist'], true);

if ($conn && sqlsrv_begin_transaction($conn) && !is_empty($nlList)) {
    foreach ($nlList as $data) {
        $cd = $data['cd'];
        $name = $data['name'];

        $sql = "SELECT 1 FROM AZW110_classmst WHERE classcd='" . CLASS_NODE_LOCATION . "' AND code='$cd'";
        $result = sqlsrv_query($conn, $sql);

        if (sqlsrv_has_rows($result)) {
            $sql = "UPDATE AZW110_classmst SET value='$name' WHERE classcd='" . CLASS_NODE_LOCATION . "' AND code='$cd'";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $code = '501';
                $errors = sqlsrv_errors();
                break;
            }
        } else {
            if (!is_empty($cd)) {
                $sql = "INSERT INTO AZW110_classmst(classcd,classname,code,[value],[order]) VALUES('" . CLASS_NODE_LOCATION . "','ノード置き場所１','$cd','$name','$cd')";

                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $code = '503';
                    $errors = sqlsrv_errors();
                    break;
                }
            } else {
                $code = '502';
                $errors = array('param is error.');
                break;
            }
        }
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