<?php
/**
 * 使用者情報（見守る人）IU
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
//$errors = array();

$facilityCd = $_POST['facilitycd'];
$staffList = json_decode($_POST['stafflist'], true);

//$facilityCd = '0001';
//$staffList = array(
//    array(
//        "nickname" => "hzl 1",
//        "staffid" => 'sw00001',
//        "usertype" => 1,
//        "usertypename" => "8005",
//    ), array(
//        "nickname" => "hzl2",
//        "staffid" => "sw00002",
//        "usertype" => 2,
//        "usertypename" => "8006",
//    )
//);
//$arrReturn['facilitycdresult'] = $facilityCd;
//$arrReturn['stafflistresult'] = $staffList;

//if ($conn && sqlsrv_begin_transaction($conn) && !is_empty($staffList)) {
if ($conn && !is_empty($staffList)) {
    foreach ($staffList as $data) {
        $staffId = $data['staffid'];
        $nickname = $data['nickname'];
        $oldNickname = $data['oldnickname'];
        $userType = $data['usertype'];
        $oldUserType = $data['oldusertype'];

        $sql = "SELECT 1 FROM AZW004_staffmst WHERE staffid='$staffId'";

        if (sqlsrv_has_rows(sqlsrv_query($conn, $sql))) {
            if ($userType != $oldUserType || $nickname != $oldNickname) {
                $sql = "UPDATE AZW004_staffmst SET staffname='$nickname',usertype='$userType',updatedate=" . $SCH . ".GETJPDATE() WHERE staffid='$staffId'";

                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $code = '501';
                    $errors = sqlsrv_errors();
                    break;
                }
            }
        } else if (!is_empty($staffId)) {
            $sql = "INSERT INTO AZW004_staffmst
                    (staffid,staffname,usertype,groupid,nickname,email,password,zworksemail,zworkspassword,updatedate,createuser,createdate)
                    VALUES('$staffId','$nickname','$userType','1','$nickname',null,'P@ssw0rd','aimi.f507@gmail.com','a620507'," . $SCH . ".GETJPDATE(),'$staffId',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120))";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $code = '502';
                $errors = sqlsrv_errors();
                break;
            }
        }

        if ($code == '200') {
            if (!is_empty($staffId) && !is_empty($facilityCd)) {
                $sql = "MERGE INTO AZW007_staffrelation s USING(SELECT '$staffId' staffid,facilitycd
                    FROM AZW003_facilitymst fm WHERE fm.hostcd=(SELECT hostcd FROM AZW003_facilitymst
                    WHERE facilitycd='$facilityCd')) u ON u.staffid=s.staffid AND u.facilitycd=s.facilitycd
                    WHEN NOT MATCHED THEN INSERT (staffid,facilitycd,createuser,createdate) VALUES (u.staffid,u.facilitycd,'$staffId',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120));";

                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $code = '503';
                    $errors = sqlsrv_errors();
                    break;
                }
            } else {
                $code = '504';
                $errors = sqlsrv_errors();
                break;
            }
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

//if ($code == '200') {
//    if (!sqlsrv_commit($conn)) {
//        sqlsrv_rollback($conn);
//    }
//} else {
//    sqlsrv_rollback($conn);
//}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

sendResponse(json_encode($arrReturn));

?>