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

$companyList = array();

if ($conn) {
    $sql = "SELECT companycd,companyname,companynamekana,initial FROM AZW001_companymst";
    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $companyList[$index] = array(
                'cd' => $row[0],
                'name' => $row[1],
                'kana' => $row[2],
                'initial' => $row[3],
            );
            $index = $index + 1;
        }
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['companylist'] = $companyList;

sendResponse(json_encode($arrReturn));

?>