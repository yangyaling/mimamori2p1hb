<?php
/**
 * デバイスデータ取得処理。
 * User: NITIOSRD
 * Date: 2016/12/21
 * Time: 13:26
 */
include 'lib.php';

$connectionOptions = array('Database' => INPUT_DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$code = '200';
$errors = array();

//@$ak = $_GET['udefg'];
//$ak=':fdsa';

function hasError($value)
{
    if (strpos($value, 'error') > -1 || strpos($value, 'セッションIDが無効です') > -1) {
        return true;
    } else {
        return false;
    }
}

function updateSessionId($conn, &$code)
{
    $sql = "SELECT email,password,sessionstatus FROM AZW131_zworksusermst";
    $users = sqlsrv_query($conn, $sql);

    if ($users) {
        while ($row = sqlsrv_fetch_array($users)) {
            if ($row[2] == '無効') {
                $email = $row[0];
                $password = $row[1];
                $url = "https://api.liveconnect.io/api/v3/login";
                $post_data = array("email" => $email, "password" => $password);
                sleep(3);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $handle = curl_exec($ch);
                if ($handle) {
                    $res = json_decode(curl_multi_getcontent($ch));
                    if ($res) {
                        foreach ($res as $key => $node) {
                            $dict = std_class_object_to_array($node);
                            $sessionId = $dict['Set-Cookie'];
                            $sessionId = str_replace('SESSID=', '', $sessionId);
                            $sessionId = substr($sessionId, 0, strpos($sessionId, ";"));
                            $sql = "UPDATE AZW131_zworksusermst
                                    SET updatetime=GETDATE(),sessionid='$sessionId',sessionstatus='有効'
                                    WHERE email='$email'";
                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $code = '302';
                                return;
                            }
                        }
                    }
                }
                curl_close($ch);
            }
        }
    } else {
        $code = '301';
        return;
    }
}

function postExec($code, $errors)
{
    $arrReturn = array();
    $arrReturn['updatedate'] = date('Y-m-d H:i:s');
    $arrReturn['code'] = $code;
    $arrReturn['errors'] = $errors;

    sendResponse(json_encode($arrReturn));
    exit(0);
}

//if (!$ak || $ak != ':fdsa') {
//    $code = '602';
//    $errors = array("権限がない。");
//    postExec($code, $errors);
//}

if ($conn && sqlsrv_begin_transaction($conn)) {
    //セッションIDを一旦保障する
//    updateSessionId($conn, $code);

    if ($code == '200') {
        //ディバイス一覧を取得
        $sql = "SELECT email,sessionid,deviceid,[timestamp] FROM AZW231_deviceupdateview";
        $deviceList = sqlsrv_query($conn, $sql);

        if ($deviceList) {
            $iSql = "INSERT INTO AZW133_zworksdata (deviceid,[value],unit,timestmp,[date],[hour],[minute]) VALUES ";

            while ($row = sqlsrv_fetch_array($deviceList)) {
                $email = $row[0];
                $sessionId = $row[1];
                $deviceId = $row[2];
                $timestamp = $row[3];

                //初回、最近のデータのみを取得
                if (is_null($timestamp)) {
                    $url = 'https://api.liveconnect.io/api/v3/devices/' . $deviceId . '/values?latest';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie:SESSID=' . $sessionId . '; path=/; httponly'));
                    $handle = curl_exec($ch);
                    if ($handle && !hasError($handle)) {
                        $res = json_decode(curl_multi_getcontent($ch));
                        $lastTimestamp = 0;
                        if ($res) {
                            foreach ($res as $key => $node) {
                                $dict = std_class_object_to_array($node);
                                //data insert
                                $value = floatval($dict['value']);
                                $unit = $dict['unit'];
                                $timeValue = floatval($dict['timestamp']);
                                $strDate = date('Y-m-d', $timeValue / 1000);
                                $strHour = intval(date('H', $timeValue / 1000));
                                $strMin = intval(date('i', $timeValue / 1000));

                                $sql = $iSql . "('$deviceId',$value,'$unit',$timeValue,'$strDate',$strHour,$strMin)";
                                $result = sqlsrv_query($conn, $sql);
                                if (!$result) {
                                    $errors = sqlsrv_errors();
                                    $code = '501';
                                }
                                $lastTimestamp = $timeValue;
                            }
                            if ($lastTimestamp != 0) {
                                $sql = "UPDATE AZW137_zworksdevicemst SET [timestamp]=$lastTimestamp WHERE deviceid='$deviceId'";
                                $result = sqlsrv_query($conn, $sql);
                                if (!$result) {
                                    $errors = sqlsrv_errors();
                                    $code = '502';
                                }
                            }
                        }
                    } else {
                        if (strpos($handle, 'セッションIDが無効です') > -1) {
                            $code = '401';
                            $errors = array("セッションIDが無効です。");
                            break;
                        }
                    }
                    curl_close($ch);
                } else {
                    // get sensor id
                    $startTime = floatval($timestamp) + 1;
                    $url = "https://api.liveconnect.io/api/v3/devices/$deviceId/values?start=$startTime";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie:SESSID=' . $sessionId . '; path=/; httponly'));
                    $handle = curl_exec($ch);
                    if ($handle && !hasError($handle)) {
                        $res = json_decode(curl_multi_getcontent($ch));
                        $lastTimestamp = 0;
                        if ($res) {
                            $i1 = 0;
                            $sql = "DELETE FROM AZW133_zworksdata WHERE deviceid = '$deviceId' AND timestmp >= $startTime";
                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $errors = sqlsrv_errors();
                                $code = '503';
                            }

                            $vSql = '';
                            foreach ($res as $key => $node) {
                                $dict = std_class_object_to_array($node);
                                //data insert
                                $timeValue = floatval($dict['timestamp']);
                                $strDate = date('Y-m-d', $timeValue / 1000);
                                $strHour = intval(date('H', $timeValue / 1000));
                                $strMin = intval(date('i', $timeValue / 1000));
                                $value = floatval($dict['value']);
                                $unit = $dict['unit'];

                                $vSql .= "('$deviceId',$value,'$unit',$timeValue,'$strDate',$strHour,$strMin),";

                                $lastTimestamp = $timeValue;
                            }
                            if (is_empty($vSql)) {
                                $sql = $iSql . substr($vSql, 0, strlen($vSql) - 1);
                                $result = sqlsrv_query($conn, $sql);
                                if (!$result) {
                                    $errors = sqlsrv_errors();
                                    $code = '504';
                                }
                            }
                            if ($lastTimestamp != 0) {
                                $sql = "UPDATE AZW137_zworksdevicemst SET [timestamp]=$lastTimestamp WHERE deviceid='$deviceId'";
                                $result = sqlsrv_query($conn, $sql);
                                if (!$result) {
                                    $errors = sqlsrv_errors();
                                    $code = '505';
                                }
                            }
                        }
                    } else {
                        if (strpos($handle, 'セッションIDが無効です') > -1) {
                            $code = '401';
                            $errors = array("セッションIDが無効です。");
                            break;
                        }
                    }
                    curl_close($ch);
                }
            }
            if ($code == '401') {
                $st = '無効';
            } else {
                $st = '有効';
            }

            $sql = "UPDATE AZW131_zworksusermst SET updatetime=GETDATE(),sessionstatus='$st' WHERE email='$email'";
            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $errors = sqlsrv_errors();
                $code = '506';
            }
        }
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
    postExec($code, $errors);
}

if ($code == '200') {
//    if (!sqlsrv_commit($conn)) {
    $code = '602';
    sqlsrv_rollback($conn);
//    }
} else {
    $code = '601';
    sqlsrv_rollback($conn);
}

sqlsrv_close($conn);

postExec($code, $errors);
?>