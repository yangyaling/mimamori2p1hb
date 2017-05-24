<?php
/**
 * Created by PhpStorm.
 * User: youarei
 * Date: 17/4/24
 * Time: 下午3:27
 * 企業情報を追加／更新
 */
include '../../cmslib.php';

$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

$arrReturn = array();
$arrReturn['code'] = '200';

$companyCd = $_POST['companycd'];
$companyName = $_POST['companyname'];
$companyNameKana = $_POST['companynamekana'];
$initial = $_POST['initial'];
$updateUser = $_POST['updateuser'];
$updateDate = $_POST['updatedate'];

if ($conn) {

    //既に存在しているのかをチェック
    $chk1 = sqlsrv_query($conn, "SELECT * FROM AZW001_companymst WHERE companycd='$companyCd'");
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
        if (!is_empty($companyCd)) {
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