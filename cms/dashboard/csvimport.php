<?php
/**
 * 可視化の設定ファイルインポート処理。
 * User: NITIOSRD
 * Date: 2017/2/20
 * Time: 10:18
 */

include '../cmslib.php';

$loginUser = checkSession();

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();
$csvTitle = array();
$userList = array();

$csvFile = $_FILES['vz_config_csv'];

/**
 * Array (
 * [vz_config_csv] => Array (
 * [name] => aaa.csv [type] => application/vnd.ms-excel
 * [tmp_name] => C:\Users\NITIOSRD\AppData\Local\Temp\php9E84.tmp
 * [error] => 0
 * [size] => 389
 * )
 * )
 */
function checkUploadFile($csvFile, &$code, &$errors)
{
    if (is_empty($csvFile)) {
        $code = '601';
        $errors = array('無効なファイル');
        return false;
    }
    if ($csvFile["error"] > 0) {
        $code = '602';
        $errors = array($csvFile["error"]);
        return false;
    }
    if (substr($csvFile['name'], strrpos($csvFile['name'], '.') + 1) != 'csv') {
        $code = '603';
        $errors = array('ファイルタイプ不正');
        return false;
    }
    return true;
}

function inputCSVFile($handle, &$code, &$errors)
{
    $userList = array();
    $i = 0;
    $idxUser = 0;
    $idxVZConfig = 0;
    $vzc = array();
    $userId = '';
    $actionId = '';
    $actionClass = '';
    $newDate = date('Y-m-d 00:00:00');
    while ($row = fgetcsv($handle)) {
        if (count($row) != 14) {
            $code = '401';
            $errors = array('ファイルフォーマット不正');
            break;
        }
        if ($i == 0) {
            global $csvTitle;
            $csvTitle = $row;
            $i = 1;
        } else {
            if (!is_empty($userId) && $userId != $row[0]) {
                $userList[$idxUser] = array(
                    'userid' => $userId,
                    'actionid' => $actionId,
                    'actionclass' => $actionClass,
                    'vzc' => $vzc
                );
                $vzc = array();
                $idxVZConfig = 0;
                $idxUser = $idxUser + 1;
            }
            $vzc[$idxVZConfig] = array(
                'userid' => $row[0],
                'actionid' => $row[1],
                'vzstartdt' => $newDate,
                'actionclass' => $row[2],
                'actionname' => $row[3],
                'actionorder' => $row[4],
                'actionexplain' => $row[5],
                'actionsummary' => $row[6],
                'displaycd1' => $row[7],
                'devicetype1' => $row[8],
                'dataexplain1' => $row[9],
                'color1' => $row[10],
                'displaycd2' => $row[11],
                'devicetype2' => $row[12],
                'dataexplain2' => $row[13]
            );
            $idxVZConfig = $idxVZConfig + 1;
            $userId = $row[0];
            $actionId = $row[1];
            $actionClass = $row[2];
        }
    }
    $userList[$idxUser] = array(
        'userid' => $userId,
        'actionid' => $actionId,
        'actionclass' => $actionClass,
        'vzc' => $vzc
    );

    return $userList;
}

function getDeviceIdByDisplayCdWithDeviceType($conn, $customerId, $displayCd, $deviceType, &$code, &$errors)
{
    $deviceId = '';

    $sql = "SELECT TOP 1 sv.deviceid,sv.displayname,sv.devicetypename FROM AZW230_sensormstview sv
            WHERE sv.custid='$customerId' AND sv.displaycd='$displayCd' AND sv.devicetype='$deviceType' AND sv.enddate IS NULL";

    if ($result = sqlsrv_query($conn, $sql)) {
        $row = sqlsrv_fetch_array($result);
        if ($row && !is_empty($row[0])) {
            $deviceId = $row[0];
        } else {
            $code = '602';
            $errors = array('入居者：' . $customerId . '設置情報１：' . $displayCd . '：' . $row[1] . 'デバイスタイプ：' . $deviceType . '：' . $row[2] . 'デバイスが確定できません。');
        }
    } else {
        $code = '601';
        $errors = sqlsrv_errors();
    }
    return $deviceId;
}

function createVZConfig($conn, $customerId, $data, $today, $hasHistory, &$vzHistory, &$code, &$errors)
{
    $actionId = $data['actionid'];
    $actionName = $data['actionname'];
    $actionClass = $data['actionclass'];
    $vzStartDt = $data['vzstartdt'];
    $actionOrder = $data['actionorder'];
    $actionExplain = $data['actionexplain'];
    $actionSummary = $data['actionsummary'];
    $actionColor = $data['color1'];
    $displayCd1 = $data['displaycd1'];
    $deviceType1 = $data['devicetype1'];
    $dataExplain1 = $data['dataexplain1'];
    $displayCd2 = $data['displaycd2'];
    $deviceType2 = $data['devicetype2'];
    $dataExplain2 = $data['dataexplain2'];

    $sql = "SELECT 1 FROM AZW150_vzconfig  WHERE userid='$customerId' AND actionid='$actionId'
            AND actionclass='$actionClass' AND vzstartdt='$vzStartDt'";

    if ($result = sqlsrv_query($conn, $sql)) {
        if (!sqlsrv_has_rows($result) || !$hasHistory) {
            if ($hasHistory) {
//                $newActionId = getActionId($conn, $customerId, $actionClass);
                $newActionId = $actionId;
                $today = "'" . $today . "'";
            } else {
                $newActionId = $actionId;
                $today = "CONVERT(VARCHAR(10)," . SCH . ".GETJPDATE()+1,120)+' 00:00:00'";
            }
            if (is_empty($deviceId1 = getDeviceIdByDisplayCdWithDeviceType($conn, $customerId, $displayCd1, $deviceType1, $code, $errors))) {
                return false;
            }

            if ($actionExplain == '6') {
                if (is_empty($deviceId2 = getDeviceIdByDisplayCdWithDeviceType($conn, $customerId, $displayCd2, $deviceType2, $code, $errors))) {
                    return false;
                }
                $sql = "INSERT INTO AZW150_vzconfig
                    (userid,actionid,vzstartdt,actionclass,actionname,actionorder,actionexplain,actionsummary,
                    displaycd1,deviceid1,devicetype1,dataexplain1,color1,
                    displaycd2,deviceid2,devicetype2,dataexplain2) VALUES
                    ('$customerId','$newActionId',$today,'$actionClass','$actionName','$actionOrder','$actionExplain','$actionSummary',
                    '$displayCd1','$deviceId1','$deviceType1','$dataExplain1','$actionColor',
                    '$displayCd2','$deviceId2','$deviceType2','$dataExplain2')";
            } else {
                $sql = "INSERT INTO AZW150_vzconfig
                    (userid,actionid,vzstartdt,actionclass,actionname,actionorder,actionexplain,actionsummary,
                    displaycd1,deviceid1,devicetype1,dataexplain1,color1) VALUES
                    ('$customerId','$newActionId',$today,'$actionClass','$actionName','$actionOrder','$actionExplain','$actionSummary',
                    '$displayCd1','$deviceId1','$deviceType1','$dataExplain1','$actionColor')";
            }

            if (!$result = sqlsrv_query($conn, $sql)) {
                $code = '507';
                $errors = array('sqlsrv_errors' => sqlsrv_errors(), 'sql' => $sql);
                return false;
            }
        } else {
            $code = '506';
            $errors = array("ID：「" . $actionId . "」、区分：「" . $actionClass . "」、開始日時：「" . $vzStartDt . "」が既に存在しました。");
            return false;
        }
    } else {
        $code = '505';
        $errors = sqlsrv_errors();
        return false;
    }

    if ($hasHistory) {
        $vzHistory .= $actionName . "を追加しました。\n";
    }
    return true;
}

function checkCustomer($conn, $customerId)
{
    $sql = "SELECT 1 FROM AZW005_custmst WHERE custid='$customerId'";

    if ($result = sqlsrv_query($conn, $sql)) {
        if ($row = sqlsrv_fetch_array($result)) {
            return true;
        }
    }
    return false;
}

//データ配列作成
//$handle = @fopen('bbb.csv', "rb");
if (checkUploadFile($csvFile, $code, $errors)) {
    $handle = @fopen($csvFile['tmp_name'], "rb");
    if ($handle) {
        $userList = inputCSVFile($handle, $code, $errors);
    }
    fclose($handle);
}

$staffId = $loginUser->getUserId();

//DB更新
if ($code == '200') {
    if ($conn && sqlsrv_begin_transaction($conn)) {
        //入居者	活動/環境	区分	名称	優先順位	活動解釈方法	活動集計方法
        //0         1           2       3       4           5               6
        //設置場所1	項目種類1	データ解釈方法1	色1	設置場所2	項目種類2	データ解釈方法2
        //7         8       9               10  11          12      13
        $today = date('Y-m-d 00:00:00');
        foreach ($userList as $data) {
            $ivSql = '';
            $vzHistory = '';
            $customerId = $data['userid'];

            if (!is_empty($customerId)) {
                if (!checkCustomer($conn, $customerId)) {
                    $code = '502';
                    $errors = array('入居者：' . $customerId . 'が存在しません');
                    break;
                }

                foreach ($data['vzc'] as $value) {
                    if (!createVZConfig($conn, $customerId, $value, $today, true, $vzHistory, $code, $errors)) {
                        break;
                    }
                }
            } else {
                $code = '503';
                $errors = array('入居者');
            }

            // 設定変更履歴を作成する
            if ($code == '200' && !is_empty($vzHistory)) {
                $vzHistory = "さんの可視化の設定の変更が完了しました。\n" . $vzHistory;
                $insertSql = "INSERT INTO AZW152_vznoticetbl(receiveuser,senduser,noticetype,title,registdate,content)
                          SELECT TOP 1 '$staffId','$customerId','K','可視化設定の変更が完了しました。',CONVERT(VARCHAR(19)," . SCH . ".GETJPDATE(),120),
                          '【'+ut.roomcd+'】　'+ut.custname+'$vzHistory' FROM AZW001_frscview ut WHERE ut.custid='$customerId'";

                if (!sqlsrv_query($conn, $insertSql)) {
                    $code = '504';
                    $errors = sqlsrv_errors();
                }
            }
        }
    } else {
        $code = '500';
        $errors = sqlsrv_errors();
    }
}

if ($code == '200') {
    if (!sqlsrv_commit($conn)) {
        sqlsrv_rollback($conn);
    }
} else {
    sqlsrv_rollback($conn);
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;
//$arrReturn['loginuser'] = $loginUser;
//$arrReturn['staffid'] = $staffId;

//$arrReturn['vzconfiglisttitle'] = $csvTitle;
//$arrReturn['vzconfiglist'] = $userList;

if ($code == '200') {
    echo "設定ファイルのインポートが正常に終了しました。<br/>";
//    var_dump($arrReturn);
} else {
    echo "設定ファイルのインポートが失敗しました。<br/>";
    var_dump($arrReturn);
}

//var_dump($arrReturn);
//sendResponse(json_encode($arrReturn));

