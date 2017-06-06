<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/24
 * Time: 12:47
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$arrReturn['code'] = '200';

$email = $_POST['email'];
$password = $_POST['password'];
$sessionId = $_POST['sessionid'];
$updateTime = $_POST['updatetime'];
$sessionStatus = $_POST['sessionstatus'];
$type = $_POST['type'];

function updateMimamorugawa($email, $sessionId, $updateTime, $sessionStatus, $password, $type)
{
    $code = '200';
    $connGawa = sqlsrv_connect(SERVERNAME, array('Database' => 'mimamorugawaDB', 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8'));

    if ($connGawa) {
        $tname = '';
        if ($type == '1') {
            $tname = 'AZW131_zworksusermst';
        } elseif ($type == '2') {
            $tname = 'S401_zworksuser';
        }

        if ($tname == '') {
            $code = '513';
        } else {
            $sql = "SELECT email,password,sessionid,updatetime,sessionstatus FROM $tname WHERE email='$email'";
            $result = sqlsrv_query($connGawa, $sql);

            //検索結果がある場合、更新処理を行う。
            if (sqlsrv_has_rows($result)) {
                $sql = "UPDATE $tname SET sessionid='$sessionId',updatetime='$updateTime',sessionstatus='$sessionStatus'
                WHERE email='$email'";

                $result = sqlsrv_query($connGawa, $sql);
                if (!$result) {
                    $code = '511';
                }
            } else {
                $sql = "INSERT INTO $tname(email,password,sessionid,updatetime,sessionstatus)
                            VALUES ('$email','$password','$sessionId','$updateTime','$sessionStatus')";

                $result = sqlsrv_query($connGawa, $sql);
                if (!$result) {
                    $code = '512';
                }
            }
        }
    } else {
        $code = '510';
    }

    sqlsrv_close($connGawa);

    return $code;
}

if ($conn) {

    $tName = '';
    if ($type == '1') {
        $tName = 'AZW131_zworksusermst';
    } elseif ($type == '2') {
        $tName = 'S401_zworksuser';
    }

    if ($tName == '') {
        $arrReturn['code'] = '503';
    } else {
        $sql = "SELECT email,password,sessionid,updatetime,sessionstatus FROM $tName WHERE email='$email'";
        $result = sqlsrv_query($conn, $sql);

        //検索結果がある場合、更新処理を行う。
        if (sqlsrv_has_rows($result)) {
            $sql = "UPDATE $tName SET sessionid='$sessionId',updatetime='$updateTime',sessionstatus='$sessionStatus'
                WHERE email='$email'";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $arrReturn['code'] = '501';
            }
        } else {
            $sql = "INSERT INTO $tName(email,password,sessionid,updatetime,sessionstatus)
                            VALUES ('$email','$password','$sessionId','$updateTime','$sessionStatus')";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $arrReturn['code'] = '502';
            }
        }
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);
//if ($arrReturn['code'] == '200') {
//    $arrReturn['code'] = updateMimamorugawa($email, $sessionId, $updateTime, $sessionStatus, $password, $type);
//}

sendResponse(json_encode($arrReturn));

?>