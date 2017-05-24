<?php
/**可視化設定変更履歴件数情報取得処理。
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

$staffId = $_POST['staffid'];
$regDate = $_POST['registdate'];

$vzNoticeCount = 0;

if ($conn) {
//    $regDate = '2017-02-25 13:46:45';

    if (!is_empty($staffId) && !is_empty($regDate)) {
        $cSql = "SELECT COUNT(1) FROM AZW152_vznoticetbl nt INNER JOIN (SELECT DISTINCT sr.staffid FROM AZW001_frscview fv
                  INNER JOIN AZW007_staffrelation sr ON sr.facilitycd=fv.facilitycd WHERE fv.staffid='$staffId') fs
                  ON nt.receiveuser=fs.staffid WHERE nt.registdate > '$regDate'";

        if ($result = sqlsrv_query($conn, $cSql)) {
            if ($row = sqlsrv_fetch_array($result)) {
                $vzNoticeCount = $row[0];
            } else {
                $vzNoticeCount = 0;
            }
        } else {
            $code = '502';
            $errors = sqlsrv_errors();
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

$arrReturn['vznoticecount'] = $vzNoticeCount;

sendResponse(json_encode($arrReturn));

?>

