<?php
/**
 * ログイン処理
 * User: NITIOSRD
 * Date: 2017/01/19
 * Time: 17:05
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();
$staffName = '';
$eMail = '';
$userType = '';

$hostCd = $_POST['hostcd'];
$staffId = $_POST['staffid'];
$password = $_POST['password'];

//$hostCd = 'host01';
//$staffId = 'sw00001';
//$password = 'P@ssw0rd';

if ($conn) {
    $sql = "SELECT TOP 1 sm.[staffid],f.[hostcd],sm.[staffname],sm.[groupid],sm.[nickname],sm.[zworksemail],sm.[password],sm.[usertype]
            FROM AZW004_staffmst sm INNER JOIN [AZW007_staffrelation] sr ON sm.staffid = sr.staffid
            INNER JOIN AZW003_facilitymst f ON sr.facilitycd = f.facilitycd INNER JOIN AZW002_hostmst hm ON hm.hostcd = f.hostcd
            WHERE sm.staffid='$staffId' AND f.hostcd='$hostCd'";

    if ($result = sqlsrv_query($conn, $sql)) {
        if ($row = sqlsrv_fetch_array($result)) {
            if ($password == $row[6] && $hostCd == $row[1]) {
                $staffName = $row[2];
                $eMail = $row[5];
                $userType = $row[7];
            } else {
                $code = '502';
                $errors = array('Password error.');
            }
        } else {
            $code = '501';
            $errors = sqlsrv_errors();
        }
    } else {
        $code = '503';
        $errors = sqlsrv_errors();
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;
$arrReturn['staffname'] = $staffName;
$arrReturn['email'] = $eMail;
$arrReturn['usertype'] = $userType;

sendResponse(json_encode($arrReturn));


?>
