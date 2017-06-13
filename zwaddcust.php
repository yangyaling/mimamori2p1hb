<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/13
 * Time: 9:50
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$arrReturn['code'] = '200';

$facilityCd = $_POST['facilitycd'];
$floorNo = $_POST['floorno'];
$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];
$roomCd = $_POST['roomid'];
$nickname = $_POST['nickname'];
$updateDate = $_POST['updatedate'];
$image = $_POST['image'];

function checkRestrict($conn, $staffId, $customerId, $roomCd)
{
    $resultFlg = true;

//    $sql = "SELECT DISTINCT crm.staffid,ISNULL(crm1.custid,'') custid,ISNULL(crm1.roomid,'') roomid
//            FROM AZW104_custrestrictmst crm LEFT OUTER JOIN AZW104_custrestrictmst crm1 ON crm1.staffid = crm.staffid
//            AND crm1.custid = '$customerId' WHERE crm.staffid='$staffId'";
////    AND crm1.roomid = '$roomCd'
//    $chk = sqlsrv_query($conn, $sql);
//    if ($chk && $row = sqlsrv_fetch_array($chk)) {
//        if (is_empty($row[1])) {
//            $resultFlg = false;
//        }
//    }

    return $resultFlg;
}

if ($conn) {
//    $staffId = '0001';
//    $customerId ='0003';
//    $roomCd = '2';
//    $nickname = 'abcd';
//    $updatedate = '2016-09-23 15:52:22.000';
//    $image = 'item-0';

    if (!checkRestrict($conn, $staffId, $customerId, $roomCd)) {
        $arrReturn['code'] = '403';
    } else {
        //ユーザマスタ存在チェック
        $chk1 = sqlsrv_query($conn, "SELECT 1 FROM AZW005_custmst WHERE custid='$customerId'");

        //Zワークスルームマスタ存在チェック
        $chk2 = sqlsrv_query($conn, "SELECT 1 FROM AZW134_roommst WHERE roomcd='$roomCd' AND facilitycd='$facilityCd' AND floorno='$floorNo'");

        if (!sqlsrv_has_rows($chk1) || !sqlsrv_has_rows($chk2)) {
            $arrReturn['code'] = '501';
        } else {
            $sql = "SELECT 1 FROM AZW008_custrelation WHERE custid='$customerId' AND roomcd='$roomCd' AND floorno='$floorNo'";
            $result = sqlsrv_query($conn, $sql);
            if (!sqlsrv_has_rows($result)) {
                $sql = "INSERT INTO AZW008_custrelation(custid,roomcd,floorno,picpath,picupdatedate,createuser,createdate)
                        VALUES ('$customerId','$roomCd','$floorNo',null,null,'$staffId',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120))";
                $result = sqlsrv_query($conn, $sql);
                if ($result) {
                    $arrReturn['code'] = '201';
                } else {
                    $arrReturn['code'] = '502';
                }
            } else {
                $arrReturn['code'] = '503';
            }
        }
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>