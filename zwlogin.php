<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/08
 * Time: 17:05
 * ログイン
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$arrReturn['code'] = '200';

$arrReturn['staffname'] = '';
$arrReturn['email'] = '';

$hostCd = $_POST['hostcd'];
$staffId = $_POST['staffid'];
$password = $_POST['password'];

if ($conn) {
//    $hostCd = 'host01';
//    $staffId = 'sw00001';
//    $password = 'P@ssw0rd';

    $sql = "SELECT TOP 1 sm.[staffid],f.[hostcd],sm.[staffname],sm.[groupid],sm.[nickname],sm.[zworksemail],sm.[password],sm.[usertype]
            FROM [AZW004_staffmst] sm INNER JOIN [AZW007_staffrelation] sr ON sm.staffid = sr.staffid
            INNER JOIN [AZW003_facilitymst] f ON sr.facilitycd = f.facilitycd INNER JOIN [AZW002_hostmst] hm ON hm.hostcd = f.hostcd
            WHERE sm.staffid='$staffId' AND f.hostcd='$hostCd'";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $row = sqlsrv_fetch_array($result);
        if ($row) {
            if ($password == $row[6] && $hostCd == $row[1]) {
                $arrReturn['code'] = '200';
                $arrReturn['staffname'] = $row[2];
                $arrReturn['email'] = $row[5];
                $arrReturn['usertype'] = $row[7];
            } else {
                $arrReturn['code'] = '502';
                $arrReturn['errors'] = sqlsrv_errors();
            }
        } else {
            $arrReturn['code'] = '501';
            $arrReturn['errors'] = sqlsrv_errors();
        }
    } else {
        $arrReturn['code'] = '503';
        $arrReturn['errors'] = sqlsrv_errors();
    }
} else {
    $arrReturn['code'] = '500';
    $arrReturn['errors'] = sqlsrv_errors();
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>
