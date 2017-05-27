<?php
/**可視化設定変更履歴情報取得処理。
 * User: NITIOSRD
 * Date: 2017/2/17
 * Time: 12:54
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

$hostCd = $_POST['hostcd'];
$facilityCd = $_POST['facilitycd'];
$floorNo = $_POST['floorno'];
$staffId = $_POST['staffid'];
$regDate = $_POST['registdate'];

$vzNoticeInfo = array();

function getVzNoticeInfo($conn, $staffId, &$code, &$errors)
{
    $sql = "SELECT noticeid,title,CONVERT(VARCHAR(19),registdate,120),content FROM AZW152_vznoticetbl nt
            INNER JOIN (SELECT DISTINCT sr.staffid FROM AZW001_frscview fv INNER JOIN AZW007_staffrelation sr
            ON sr.facilitycd=fv.facilitycd WHERE fv.staffid='$staffId' ) fs ON nt.receiveuser=fs.staffid ORDER BY registdate DESC";
    $vzNoticeInfo = array();
    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $vzNoticeInfo[$index] = array(
                'noticeid' => $row[0],
                'title' => $row[1],
                'registdate' => $row[2],
                'content' => $row[3]
            );
            $index++;
        }
    } else {
        $code = '503';
        $errors = sqlsrv_errors();
    }

    return $vzNoticeInfo;
}

if ($conn) {
//    $staffId = 'sw00001';
//    $regDate = '2017-02-17 13:46:45';


    if (!is_empty($staffId)) {
        if (!is_empty($regDate)) {
            $cSql = "SELECT COUNT(1) FROM AZW152_vznoticetbl nt INNER JOIN (SELECT DISTINCT sr.staffid FROM AZW001_frscview fv
                      INNER JOIN AZW007_staffrelation sr ON sr.facilitycd=fv.facilitycd WHERE fv.staffid='$staffId') fs
                      ON nt.receiveuser=fs.staffid WHERE nt.registdate > '$regDate'";

            if ($result = sqlsrv_query($conn, $cSql)) {
                $row = sqlsrv_fetch_array($result);
                if ($row && $row[0] > 0) {
                    $vzNoticeInfo = getVzNoticeInfo($conn, $staffId, $code, $errors);
                }
            } else {
                $code = '502';
                $errors = sqlsrv_errors();
            }
        } else {
            $vzNoticeInfo = getVzNoticeInfo($conn, $staffId, $code, $errors);
        }
    } else {
        $code = '501';
        $errors = array('パラメータ不正');
    }

} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

$arrReturn['vznoticeinfo'] = $vzNoticeInfo;

sendResponse(json_encode($arrReturn));

?>

