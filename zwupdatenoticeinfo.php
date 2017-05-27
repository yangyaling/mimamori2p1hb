<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/07
 * Time: 15:21
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

$noticeId = $_POST['noticeid'];

$sendUser = $_POST['userid1'];
$noticeType = $_POST['noticetype'];
$registDate = $_POST['registdate'];

$receiveUser = $_POST['groupid'];
$title = $_POST['title'];
$content = $_POST['content'];

$confirmUser = $_POST['staupduser'];

if ($conn) {

    $noticeType = '1';// ver.P1. alert only.

    $sql = "SELECT status,noticetype FROM AZW121_noticetbl WHERE noticeid = '$noticeId'";
    $result = sqlsrv_query($conn, $sql);

    //検索結果がない場合、登録処理を行う。
    if (!sqlsrv_has_rows($result)) {
//        if (!is_empty($sendUser) && !is_empty($noticeType) && !is_empty($title) && !is_empty($registDate)) {
//            $sql = "INSERT INTO AZW121_noticetbl(title,senduser,receiveuser,noticetype,status,content,registdate)
//                    VALUES ('$title','$sendUser','$receiveUser','$noticeType','0','$content','$registDate')";
//            $result = sqlsrv_query($conn, $sql);
//            if (!$result) {
//                $code = '505';
//                $errors = sqlsrv_errors();
//            }
//        } else {
            $code = '503';
            $errors = sqlsrv_errors();
//        }
    } else {
        //検索結果がある場合、更新処理を行う。
        if (!sqlsrv_fetch($result)) {
            $code = '503';
            $errors = sqlsrv_errors();
        } else {
            $status = sqlsrv_get_field($result, 0);
            $noticeType = sqlsrv_get_field($result, 1);
            if ($status == '0') {
                if ($noticeType == '1') {
                    $sql = "UPDATE AZW121_noticetbl SET status='1',confirmdate=" . SCH . ".GETJPDATE() WHERE noticeid='$noticeId'";
                } else {
                    $sql = "UPDATE AZW121_noticetbl SET status='1',confirmuser='$confirmUser',confirmdate='$registDate'
                            WHERE noticeid='$noticeId'";
                }
                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $code = '504';
                    $errors = sqlsrv_errors();
                }
            }
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>