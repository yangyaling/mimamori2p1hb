<?php
/**
 * Created by PhpStorm.
 * User: RDX64
 * Date: 2017/4/25
 * Time: 13:50
 * 施設情報を追加/更新
 */

include '../../cmslib.php';

$connectionoptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionoptions);

$arrReturn = array();
$arrReturn['code'] = '200';

$facilityCd = $_POST['facilitycd'];
$hostCd = $_POST['hostcd'];
$areaCd = $_POST['areacd'];
$facilityName1 = $_POST['facilityname1'];
$facilityName1Kana = $_POST['facilityname1kana'];
$facilityName2 = $_POST['facilityname2'];
$facilityName2Kana = $_POST['facilityname2kana'];
$floorCount = $_POST['floorcount'];
$roomCount = $_POST['roomcount'];
$bikou = $_POST['bikou'];
$updateUser = $_POST['updateuser'];
$updateDate = $_POST['updatedate'];

if ($conn) {

    //既に存在しているのかをチェック
    $chk1 = sqlsrv_query($conn, "SELECT * FROM AZW003_facilitymst WHERE facilitycd='$facilityCd'");
    if (sqlsrv_has_rows($chk1)) {
        //検索結果がある場合、レコードを更新
        $sql = "UPDATE AZW003_facilitymst SET hostcd='$hostCd',areacd='$areaCd',facilityname1='$facilityName1',facilityname1kana='$facilityName1Kana',facilityname2='$facilityName2',facilityname2kana='$facilityName2Kana',floorcount='$floorCount',roomcount='$roomCount',bikou='$bikou',updateuser='$updateUser',updatedate=" . SYS_DATE_YYYYMMDDHHMMSS . " WHERE facilitycd='$facilityCd'";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $arrReturn['code'] = '503';
            $arrReturn['errors'] = sqlsrv_errors();
        }
    } else {
        //検索結果がない場合、レコードを追加
        if (!is_empty($facilityCd)) {
            $sql = "INSERT INTO AZW003_facilitymst(facilitycd, hostcd,areacd,facilityname1,facilityname1kana,facilityname2,facilityname2kana,floorcount,roomcount,bikou,updateuser,updatedate)
                            VALUES ( '$facilityCd','$hostCd','$areaCd','$facilityName1','$facilityName1Kana','$facilityName2','$facilityName2Kana','$floorCount','$roomCount','$bikou','$updateUser'," . SYS_DATE_YYYYMMDDHHMMSS . ")";
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