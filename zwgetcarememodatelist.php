<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/14
 * Time: 13:50
 */
include 'lib.php';

//$conn=@mysql_connect(MYSQL_HOST_M,MYSQL_USER,MYSQL_PASS);
$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['datelist'] = '';

$userid0 = $_POST['userid0'];

if ($conn) {

    $sql = "SELECT replace(convert(varchar(10),ct.registdate,120),'-0','-') memodate FROM AZW122_carememotbl ct
            WHERE ct.userid0='$userid0' GROUP BY convert(varchar(10),ct.registdate,120)";

    $result = sqlsrv_query($conn, $sql);
    $datelist = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $datelist[$index] = $row[0];
            $index = $index + 1;
        }
    }

    $arrReturn['datelist'] = $datelist;
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>