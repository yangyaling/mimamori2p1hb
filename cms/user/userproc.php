<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 10:54
 */

include '../cmslib.php';

//$loginuser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['list'] = array();

@$isApi = $_POST['isapi'];

$list = array();

if ($isApi && $conn) {
    $sql = "SELECT userid,ISNULL(G.[value],'') groupname,U.[value] usertypename,username,email,updatedate,yobi FROM (
            SELECT userid,groupid,usertype,username,email,CONVERT(VARCHAR(10),updatedate,120) updatedate,yobi FROM AZW101_usermst) A
            LEFT OUTER JOIN AZW110_classmst G ON A.groupid = G.code AND G.classcd='" . CLASS_GROUP . "'
            LEFT OUTER JOIN AZW110_classmst U ON A.usertype = U.code AND U.classcd='" . CLASS_USER_TYPE . "'";

    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $list[$index] = array(
                'userid' => $row[0],
                'groupname' => $row[1],
                'usertypename' => $row[2],
                'username' => $row[3],
                'email' => $row[4],
                'updatedate' => $row[5],
                'yobi' => $row[6]
            );
            $index = $index + 1;
        }
    } else {
        $arrReturn['code'] = '501';
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

$arrReturn['list'] = $list;

sendResponse(json_encode($arrReturn));
?>
