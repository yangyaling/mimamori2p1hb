<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/24
 * Time: 11:23
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$arrReturn['code'] = '200';

$arrReturn['zsessioninfo'] = '';

$email = $_POST['email'];

if ($conn) {

    $sql = '';

    if (is_null($email) || $email == null || $email == '') {
        $sql = "SELECT email,password,sessionid,updatetime,sessionstatus FROM AZW131_zworksusermst";
    } else {
        $sql = "SELECT email,password,sessionid,updatetime,sessionstatus FROM AZW131_zworksusermst WHERE email='$email'";
    }

    $result = sqlsrv_query($conn, $sql);

    $zSessionInfo = array();
    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            //メールでデバイスID情報を取得
            $sql2 = "SELECT deviceid FROM AZW230_sensormstview WHERE adminemail='$row[0]' AND initflag=1 AND startdate <= CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120) AND enddate IS NULL";
            $result2 = sqlsrv_query($conn, $sql2);
            $devices = array();
            if ($result2) {
                $index2 = 0;
                while ($row2 = sqlsrv_fetch_array($result2)) {
                    $devices[$index2] = $row2[0];
                    $index2 = $index2 + 1;
                }
            }

            $zSessionInfo[$index] = array(
                'email' => $row[0],
                'password' => $row[1],
                'sessionid' => $row[2],
                'updatetime' => $row[3],
                'sessionstatus' => $row[4],
                'devices' => $devices
            );
            $index = $index + 1;
        }
    }

    $arrReturn['zsessioninfo'] = $zSessionInfo;
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>