<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/13
 * Time: 14:12
 */
include 'lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['custinfo'] = '';

$customerId = $_POST['custid'];

if ($conn) {
//    $customerId = '00002';

    $sql = "SELECT DISTINCT ui.custid,ui.custname,ui.sex,ui.birthday,ui.address,ui.kakaritsuke,ui.drug,ui.health,ui.other,
            ui.updateuser,ui.updatedate,cr.picpath,CONVERT(VARCHAR(19),cr.picupdatedate,120) picupdatedate,cr.roomcd,rm.floorno
            FROM AZW005_custmst ui INNER JOIN AZW001_frscview cr ON ui.custid = cr.custid
            INNER JOIN AZW134_roommst rm ON cr.roomcd = rm.roomcd AND cr.floorno = rm.floorno WHERE ui.custid='$customerId'";

    $result = sqlsrv_query($conn, $sql);
    $customerInfo = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $customerInfo[$index] = array(
                'custid' => $row[0],
                'user0name' => $row[1],
                'sex' => $row[2],
                'birthday' => $row[3],
                'address' => $row[4],
                'kakaritsuke' => $row[5],
                'drug' => $row[6],
                'health' => $row[7],
                'other' => $row[8],
                'updatename' => $row[9],
                'updatedate' => $row[10],
                'picpath' => $row[11],
                'picupdatedate' => $row[12],
                'roomcd' => $row[13],
                'floorno' => $row[14]
            );
            $index = $index + 1;
        }
    }

    $arrReturn['custinfo'] = $customerInfo;
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>