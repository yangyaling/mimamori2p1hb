<?php
/**
 * 使用者情報（見守る人）一覧
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';

$baseInfo = array();
$staffList = array();
$userTypeList = array();
$maxStaffId = '';

$facilityCd = $_POST['facilitycd'];
$userType = $_POST['usertype'];

function getMaxStaffId($conn, $facilityCd)
{
    $sql = "SELECT TOP 1 cp.initial + ISNULL(cm.maxstaffid,'00001') FROM AZW003_facilitymst fm
            INNER JOIN AZW002_hostmst hm ON fm.hostcd=hm.hostcd INNER JOIN AZW001_companymst cp ON hm.companycd=cp.companycd
            LEFT OUTER JOIN (SELECT RIGHT('00000' + CAST(MAX(RIGHT(sm.staffid,5)) + 1 AS VARCHAR), 5) maxstaffid
            FROM AZW004_staffmst sm INNER JOIN AZW001_companymst cp ON SUBSTRING(sm.staffid,1,2)=cp.initial) cm ON 1=1
            WHERE fm.facilitycd='$facilityCd'";
    $result = sqlsrv_query($conn, $sql);

    if ($result && $row = sqlsrv_fetch_array($result)) {
        return $row[0];
    }
    return null;
}

function getUserTypeList($conn, $class)
{
    $sql = "SELECT [code],[value] FROM AZW110_classmst WHERE classcd='$class' AND code !='x' ORDER BY [order]";

    $result = sqlsrv_query($conn, $sql);
    $cvs = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $cvs[$index] = array(
                'cd' => $row[0],
                'name' => $row[1]
            );
            $index = $index + 1;

        }
    }

    return $cvs;
}

if ($conn) {
//    $facilityCd = '0001';
//    $userType = '1';
    $sql2 = '';
    if ($userType == 'x' || $userType == '1' || $userType == '2') {
        // 1,2,3表示
        $sql2 = "AND (sm.usertype='1' OR sm.usertype='2' OR sm.usertype='3')";
    } else if ($userType == '3') {
        // 2,3表示
//        $sql2 = "AND (sm.usertype='2' OR sm.usertype='3')";
        $sql2 = "1=2";
    }

    $sql = "SELECT sr.staffid,sm.staffname,sm.usertype,clm.value usertypename FROM AZW003_facilitymst fm
            INNER JOIN AZW007_staffrelation sr ON fm.facilitycd=sr.facilitycd INNER JOIN AZW004_staffmst sm ON sr.staffid=sm.staffid
            LEFT OUTER JOIN AZW110_classmst clm ON clm.classcd='" . CLASS_USER_TYPE . "' AND sm.usertype=clm.code
            WHERE fm.facilitycd='$facilityCd'";

    $sql3 = $sql . $sql2;

    $result = sqlsrv_query($conn, $sql3);

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $staffList[$index] = array(
                'staffid' => $row[0],
                'nickname' => $row[1],
                'oldnickname' => $row[1],
                'usertype' => $row[2],
                'oldusertype' => $row[2],
                'usertypename' => $row[3]
            );
            $index = $index + 1;
        }
    }

    $baseInfo = getBaseInfo($conn, $facilityCd);
    $maxStaffId = getMaxStaffId($conn, $facilityCd);
    $userTypeList = getUserTypeList($conn, CLASS_USER_TYPE);
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['baseinfo'] = $baseInfo;
$arrReturn['stafflist'] = $staffList;
$arrReturn['usertypelist'] = $userTypeList;
$arrReturn['maxstaffid'] = $maxStaffId;

sendResponse(json_encode($arrReturn));

?>