<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/08
 * Time: 10:54
 */
include 'lib.php';

//$conn=@mysql_connect(MYSQL_HOST_M,MYSQL_USER,MYSQL_PASS);
//$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);
//
//$arrReturn = array();
//$arrReturn['code'] = '200';

$arrReturn['carememos'] = '';

$userid0 = $_POST['userid0'];
$startdate = $_POST['startdate'];
$selectdate = $_POST['selectdate'];

if ($conn) {

    $conditionsql = '';
    if ($selectdate == null || $selectdate == '') {
        $conditionsql = "AND CONVERT(char(10),ct.registdate,120)<=CONVERT(char(10),'$startdate',120) AND CONVERT(char(10),ct.registdate,120) > dateadd(day,-2,CONVERT(char(10),'$startdate',120))";
    } else {
        $conditionsql = "AND CONVERT(char(10),ct.registdate,120)=CONVERT(char(10),'$selectdate',120)";
    }

    $sql = "SELECT ct.memoid,ct.userid1,u1m.username user1name,convert(varchar(10),ct.registdate,120) memodate,
            convert(varchar(5),ct.registdate,114) memotime,ct.content FROM AZW122_carememotbl ct,AZW101_usermst u1m,
            AZW101_usermst u0m,AZW103_usertbl ut WHERE ct.userid0='$userid0' $conditionsql AND ct.userid1=u1m.userid
            AND ct.userid0=u0m.userid AND ct.userid1=ut.userid1 AND ct.userid0=ut.userid0 ORDER BY ct.registdate ASC";

    $result = sqlsrv_query($conn, $sql);
    $carememos = array();

    if ($result) {
        $memodate = '';
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $mdn = $row[3];
            if ($memodate != $mdn) {
                $memodate = $mdn;
                $carememos[$memodate] = array();
                $index = 0;
            }
            $carememos[$memodate][$index] = array(
                'memoid' => $row[0],
                'userid1' => $row[1],
                'user1name' => $row[2],
                'memodate' => $row[3],
                'memotime' => $row[4],
                'content' => $row[5]
            );
            $index = $index + 1;
        }
    }

    $arrReturn['carememos'] = $carememos;
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>