<?php
/**
 * 階層単位で顧客情報取得。
 * User: NITIOSRD
 * Date: 2017/2/16
 * Time: 11:04
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$hostCd = $_POST['hostcd'];
$facilityCd = $_POST['facilitycd'];
$floorNo = $_POST['floorno'];
$staffId = $_POST['staffid'];

$customerList = array();

if ($conn) {
//    $hostCd='host01';
//    $facilityCd = 'xxxx';
//    $floorNo = 'A棟2';
//    $staffId = 'sw00001';

    if ($facilityCd == DEFAULT_FACILITY_CD) {
        $sql = "SELECT fv.roomcd,fv.custid,fv.custname,cv.picpath,DATEDIFF(DAY,CAST(cv.birthday AS DATE)," . $SCH . ".GETJPDATE()) / 365 old,
                cv.sex,cv.tvalue,cv.tunit,cv.bd,fv.facilityname2,fv.floorno FROM AZW001_frscview fv INNER JOIN AZW134_roommst zwm
                ON zwm.roomcd=fv.roomcd AND zwm.facilitycd=fv.facilitycd AND zwm.floorno=fv.floorno
                LEFT OUTER JOIN AZW200_custstatuslistview cv ON fv.custid=cv.custid AND fv.staffid=cv.staffid
                WHERE fv.hostcd='$hostCd' GROUP BY fv.facilitycd,fv.facilityname2,fv.floorno,fv.roomcd,fv.custid,fv.custname,cv.picpath,
                cv.birthday,cv.sex,cv.tvalue,cv.tunit,cv.bd ORDER BY fv.facilitycd,fv.floorno,fv.roomcd";
    } else {
        $sql = "SELECT fv.roomcd,fv.custid,fv.custname,cv.picpath,
            DATEDIFF(DAY,CAST(cv.birthday AS DATE)," . $SCH . ".GETJPDATE()) / 365 old,cv.sex,cv.tvalue,cv.tunit,cv.bd,fv.facilityname2,fv.floorno
            FROM AZW001_frscview fv INNER JOIN (SELECT DISTINCT roomcd,floorno,facilitycd FROM AZW134_roommst
            WHERE floorno='$floorNo' AND facilitycd='$facilityCd') zwm ON zwm.roomcd=fv.roomcd AND zwm.facilitycd=fv.facilitycd AND zwm.floorno=fv.floorno
            LEFT OUTER JOIN AZW200_custstatuslistview cv ON fv.custid=cv.custid AND fv.staffid=cv.staffid
            WHERE fv.facilitycd='$facilityCd' AND fv.staffid='$staffId' GROUP BY fv.roomcd,fv.facilityname2,fv.floorno,fv.custid,fv.custname,
            cv.picpath,cv.birthday,cv.sex,cv.tvalue,cv.tunit,cv.bd ORDER BY fv.roomcd";
    }

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $customerList[$index] = array(
                'roomid' => $row[0],
                'userid0' => $row[1],
                'username0' => $row[2],
                'picpath' => $row[3],
                'userold' => $row[4],
                'usersex' => $row[5],
                'tvalue' => $row[6],
                'tunit' => $row[7],
                'bd' => $row[8],
                'facilityname2' => $row[9],
                'floorno' => $row[10]
            );
            $index++;
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

$arrReturn['custlist'] = $customerList;

sendResponse(json_encode($arrReturn));

?>