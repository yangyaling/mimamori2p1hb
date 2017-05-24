<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/4/25
 * Time: 15:01
 * 従業員情報を追加/更新
 */

include '../../cmslib.php';

$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

$arrReturn = array();
$arrReturn['code'] = '200';

$staffId = $_POST['staffid'];
$staffName = $_POST['staffname'];
$facilityCd = $_POST['facilitycd'];
$roomCd = $_POST['roomcd'];
$mainNodeId = $_POST['mainnodeid'];
$mainNodeName = $_POST['mainnodename'];
$groupId = $_POST['groupid'];
$nickName = $_POST['nickname'];
$email = $_POST['email'];
$password = $_POST['password'];
$zworksEmail = $_POST['zworksemail'];
$zworksPassword = $_POST['zworkspassword'];
$updateDate = $_POST['updatedate'];

if ($conn) {

    //既に存在しているのかをチェック
    $chk1 = sqlsrv_query($conn, "SELECT * FROM AZW004_staffmst WHERE staffid='$staffId'");
    if (sqlsrv_has_rows($chk1)) {
        //検索結果がある場合、レコードを更新
        $sql = "UPDATE AZW001_companymst SET companyname='$companyName',companynamekana='$companyNameKana',initial='$initial',updateuser='$updateUser',updatedate=" . SYS_DATE_YYYYMMDDHHMMSS . " WHERE companycd='$companyCd'";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $arrReturn['code'] = '503';
            $arrReturn['errors'] = sqlsrv_errors();
        }
    } else {
        //検索結果がない場合、レコードを追加
        if (!is_empty($staffId)&&!is_empty($facilityCd)&&!is_empty($roomCd)&&!is_empty()) {
            $sql = "INSERT INTO AZW001_companymst(companycd,companyname,companynamekana,initial,updateuser,updatedate)
                            VALUES ('$companyCd','$companyName','$companyNameKana','$initial','$updateUser'," . SYS_DATE_YYYYMMDDHHMMSS . ")";
            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $arrReturn['code'] = '504';
                $arrReturn['errors'] = sqlsrv_errors();
            }
        } else {
            $arrReturn['code'] = '505';
            $arrReturn['errors'] = sqlsrv_errors();
        }

    }

} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>