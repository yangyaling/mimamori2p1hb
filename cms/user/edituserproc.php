<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 15:37
 */

include '../cmslib.php';

//$loginuser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

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
$newPassword = $_POST['newpassword'];
$confirmPassword = $_POST['confirmpassword'];
@$editPwdFlg = $_POST['editpwdflg'];

if ($editPwdFlg != '1' || ($editPwdFlg == '1' && !is_empty($newPassword) && !is_empty($confirmPassword) && $newPassword == $confirmPassword)) {
    if ($conn) {
        $sql = "select password FROM AZW101_usermst WHERE userid='$userId'";
        $result = sqlsrv_query($conn, $sql);
        if ($result) {
            $row = sqlsrv_fetch_array($result);
            if ($editPwdFlg != '1' || ($editPwdFlg == '1' && $password == $row[0])) {
                $pwdSql = "";
                if ($editPwdFlg == '1') {
                    $pwdSql = ",password='$newPassword'";
                }
                $sql = "UPDATE AZW101_usermst SET groupid='$groupId',usertype='$userType',username='$username',
                        nickname='$nickname',contact='$contact'$pwdSql,email='$email',updatedate=GETDATE(),
                        yobi='$yobi',zworksemail='$zWorksEmail',zworkspassword='$zWorksPassword' WHERE userid='$userId'";

                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $arrReturn['code'] = '502';
                }
            } else {
                $arrReturn['code'] = '511';
            }
        } else {
            $arrReturn['code'] = '501';
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
    } else if ($code == '510' || $code == '511') {
        header("Location: edituser.php?errno=$code&userid=$userId");
    } else {
        header("Location: edituser.php?errno=1&userid=$userId");
    }
    exit();
}

?>