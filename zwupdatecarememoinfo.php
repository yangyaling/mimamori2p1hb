<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/08
 * Time: 11:08
 */
include 'lib.php';

//$conn=@mysql_connect(MYSQL_HOST_M,MYSQL_USER,MYSQL_PASS);
//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

//ユーザID１、ユーザID0、日時、本文
$userId1 = $_POST['userid1'];
$userId0 = $_POST['userid0'];
$content = $_POST['content'];
$registDate = $_POST['registdate'];

if ($conn) {
    //登録処理を行う。
    if (!is_empty($userId1)&&!is_empty($userId0)&&!is_empty($content)&&!is_empty($registDate)) {
        $sql = "INSERT INTO AZW122_carememotbl(userid1,userid0,content,registdate,createuser,createdate)
                          VALUES ('$userId1','$userId0','$content','$registDate','$userId1',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120))";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '505';
            $errors = sqlsrv_errors();
        }
    } else {
        $code = '503';
        $errors = sqlsrv_errors();
    }
} else {
    $code = '500';
    $errors = array('パラメータ不正');
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>