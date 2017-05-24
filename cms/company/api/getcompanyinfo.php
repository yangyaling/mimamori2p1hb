<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/4/25
 * Time: 12:12
 * 企業情報を取得
 */
include '../../cmslib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['companyinfo'] = '';

if ($conn) {

    $sql = "SELECT companycd,companyname,companynamekana,initial,updateuser,updatedate FROM AZW001_companymst";

    $result = sqlsrv_query($conn, $sql);
    $companyInfo = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $companyInfo[$index] = array(
                'companycd' => $row[0],
                'companyname' => $row[1],
                'companynamekana' => $row[2],
                'initial' => $row[3],
                'updateuser' => $row[4],
                'updatedate' => $row[5]
            );
            $index = $index + 1;
        }
    }
    $arrReturn['companyinfo'] = $companyInfo;
} else {
    $arrReturn['code'] = '500';
    $arrReturn['errors'] = sqlsrv_errors();
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>