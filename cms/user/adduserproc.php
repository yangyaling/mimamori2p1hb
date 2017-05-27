<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 15:37
 */

include '../cmslib.php';

//$loginuser = checkSession();

$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

$arrReturn = array();
$arrReturn['code'] = '200';

@$isApi = $_POST['isapi'];
$userId = $_POST['userid'];
$groupId = $_POST['groupid'];
$userType = $_POST['usertype'];
$username = $_POST['username'];
$nickname = $_POST['nickname'];
$contact = $_POST['contact'];
$email = $_POST['email'];
$yobi = $_POST['yobi'];
$zWorksEmail = $_POST['zworksemail'];
$zWorksPassword = $_POST['zworkspassword'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmpassword'];

if (!is_empty($password) && !is_empty($confirmPassword) && $password == $confirmPassword) {
    if ($conn) {
        $sql = "select password FROM AZW101_usermst WHERE userid='$userId'";
        $result = sqlsrv_query($conn, $sql);
        if ($userType == '0') {
            $groupId = '0';
            $zWorksEmail = '';
            $zWorksPassword = '';
        }
        if (!sqlsrv_has_rows($result)) {
            $sql = "INSERT INTO AZW101_usermst (userid,groupid,usertype,username,nickname,contact,password,email,
                    updatedate,yobi,zworksemail,zworkspassword) VALUES ('$userId','$groupId','$userType','$username',
                    '$nickname','$contact','$password','$email'," . SCH . ".GETJPDATE(),'$yobi','$zWorksEmail','$zWorksPassword')";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $arrReturn['code'] = '501';
            }
        } else {
            $arrReturn['code'] = '511';
        }
    } else {
        $arrReturn['code'] = '500';
    }
} else {
    $arrReturn['code'] = '510';
}

sqlsrv_close($conn);

if ($isApi && !is_empty($isApi)) {
    sendResponse(json_encode($arrReturn));
} else {
    $code = $arrReturn['code'];
    if ($code == '200') {
        header("Location: user.php");
    } else {
        $userInfo = implode('-', array(
            $userId,
            $groupId,
            $userType,
            $username,
            $nickname,
            $contact,
            $email,
            $yobi,
            $zWorksEmail,
            $zWorksPassword
        ));
        header("Location: adduser.php?errno=$code&userinfo=$userInfo");
    }
    exit();
}

?>