<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include 'cmslib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '';
$arrReturn['username'] = '';
$arrReturn['groupid'] = '';
$arrReturn['usertype'] = '';
$arrReturn['zworksemail'] = '';
$arrReturn['zworkspassword'] = '';

@$isApi = $_POST['isapi'];
$hostCd = $_POST['hostcd'];
$userId = $_POST['userid'];
$password = $_POST['password'];

if ($conn) {
//    // TODO:TEST
//    $hostCd = 'host01';
//    $userId = 'sw00001';
//    $password = 'P@ssw0rd';

    $sql = "SELECT TOP 1 f.[hostcd],sm.[staffname],sm.[zworksemail],sm.[zworkspassword],sm.[password],sm.[usertype],sm.[groupid],sm.[nickname]
            FROM [AZW004_staffmst] sm INNER JOIN [AZW007_staffrelation] sr ON sm.staffid = sr.staffid
            INNER JOIN [AZW003_facilitymst] f ON sr.facilitycd = f.facilitycd INNER JOIN [AZW002_hostmst] hm ON hm.hostcd = f.hostcd
            WHERE sm.staffid='$userId' AND f.hostcd='$hostCd'";

    if ($result = sqlsrv_query($conn, $sql)) {
        if ($row = sqlsrv_fetch_array($result)) {
            if ($password == $row[4] && ($row[5] == '1' || $row[5] == '2')) {
                $arrReturn['code'] = '200';
                $arrReturn['hostcd'] = $row[0];
                $arrReturn['username'] = $row[1];
                $arrReturn['zworksemail'] = $row[2];
                $arrReturn['zworkspassword'] = $row[3];
                $arrReturn['usertype'] = $row[5];
                $arrReturn['groupid'] = $row[6];
            } else {
                $arrReturn['code'] = '502';
            }
        } else {
            $arrReturn['code'] = '501';
        }
    } else {
        $arrReturn['code'] = '503';
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    sendResponse(json_encode($arrReturn));
} else {
    if ($arrReturn['code'] == '200') {
//        $lifeTime = 24 * 3600;
//        session_set_cookie_params($lifeTime);
        if (!session_id()) {
            session_start();
        }

        $loginUser = new loginuser($userId, $password);
        $loginUser->setValue(
            $arrReturn['username'],
            $arrReturn['usertype'],
            $arrReturn['groupid'],
            $arrReturn['zworksemail'],
            $arrReturn['zworkspassword']
        );

        $_SESSION[HASLOGINED] = true;
        $_SESSION[LOGINUSER] = serialize($loginUser);

        header("Location: home.php");
    } else {
        header("Location: login.php?errno=1&hostcd=$hostCd&userid=$userId");
    }
    exit();
}

?>