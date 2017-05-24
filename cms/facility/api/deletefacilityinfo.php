<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/4/25
 * Time: 14:29
 * 施設情報を削除
 */

include '../../cmslib.php';

$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

$arrReturn = array();
$arrReturn['code'] = '200';

$facilityCd = $_POST['facilitycd'];

if ($conn) {

    //既に存在しているのかをチェック
    $chk1 = sqlsrv_query($conn, "SELECT * FROM AZW003_facilitymst WHERE facilitycd='$facilityCd'");
    if (sqlsrv_has_rows($chk1)) {
        //検索結果がある場合、レコードを削除
        $sql = "DELETE FROM AZW003_facilitymst WHERE facilitycd='$facilityCd'";
        $result = sqlsrv_query($conn, $sql);
        if ($result > 0) {
            $arrReturn['code'] = '201';
        } else {
            $arrReturn['code'] = '502';
            $arrReturn['errors'] = sqlsrv_errors();
        }
    } else {
        //検索結果がない場合
        $arrReturn['code'] = '501';
        $arrReturn['errors'] = sqlsrv_errors();
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

sendResponse(json_encode($arrReturn));

?>