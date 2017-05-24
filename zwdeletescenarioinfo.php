<?php
/**
 * シナリオ情報削除。
 * User: NITIOSRD
 * Date: 2016/9/19
 * Time: 8:37
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';

$scenarioId = $_POST['scenarioid'];

if ($conn) {
    if (!is_empty($scenarioId)) {
        $sql = "DELETE FROM AZW142_scenariodtl WHERE scenarioid = '$scenarioId'";
        $result = sqlsrv_query($conn, $sql);

        if (!$result) {
            $code = '501';
        }

        $sql = "DELETE FROM AZW141_scenarioinfo WHERE scenarioid = '$scenarioId'";
        $result = sqlsrv_query($conn, $sql);

        if (!$result) {
            $code = '502';
        }
    } else {
        $code = '503';
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;

sendResponse(json_encode($arrReturn));

?>