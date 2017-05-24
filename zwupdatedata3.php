<?php
/**
 * Created by PhpStorm.
 * User: yuqy
 * Date: 2016/06/28
 * Time: 15:15
 */
include 'lib.php';

$opt = array('Database' => 'mimamoriDB', 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $opt);

$arrReturn = array();
$arrReturn['code'] = '200';
$arrReturn['res'] = '';
$sessionStatus = 1;

if ($conn) {

    //ディバイス一覧を取得
    $sql = "SELECT r.roomid,r.deviceid,r.adminemail,r.timestamp,u.sessionid FROM AZW132_zworksroommst r
            LEFT JOIN AZW131_zworksusermst u ON r.adminemail = u.email";

    $deviceList = sqlsrv_query($conn, $sql);
    if ($deviceList) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($deviceList)) {
            $roomId = $row[0];
            $deviceId = $row[1];
            $userId = $row[2];
            $timestamp = $row[3];
            $sessionId = $row[4];
            $sessionStatus = 1;

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
                if ($handle == true) {
                    $res = json_decode(curl_multi_getcontent($ch));
                    $lastTimestamp = 0;

                    if (!is_null($res)) {
                        foreach ($res as $key => $node) {
                            $dict = std_class_object_to_array($node);
                            //data insert
                            $timeValue = floatval($dict['timestamp']);
                            $strDate = date('Y-m-d', $timeValue / 1000);
                            $strHour = intval(date('H', $timeValue / 1000));
                            $strMin = intval(date('i', $timeValue / 1000));
                            $value = floatval($dict['value']);
                            $unit = $dict['unit'];

                            $sql = "MERGE INTO AZW133_zworksdata AS s
                USING (SELECT '$deviceId' AS deviceid,$timeValue as timestmp,$value as value) AS u
                ON s.deviceid = u.deviceid and s.timestmp =u.timestmp and s.value =u.value
                WHEN MATCHED THEN
                    UPDATE
                    SET value=$value
                WHEN NOT MATCHED THEN
                    INSERT(deviceid,value,unit,timestmp,date,hour,minute)
                    VALUES ('$deviceId',$value,'$unit',$timeValue,'$strDate',$strHour,$strMin);";

                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $arrReturn['code'] = '211';
                            }

                            $lastTimestamp = $timeValue;
                        }

                        if ($lastTimestamp != 0) {
                            $sql = "UPDATE AZW132_zworksroommst set timestamp = $lastTimestamp where deviceid ='$deviceId'";
                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $arrReturn['code'] = '221';
                            }
                        }
                    } else {
                        $sessionStatus = 0;
                    }
                }
                curl_close($ch);
            } else {
                $startTime = floatval($timestamp) + 1000;
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
                if ($handle == true) {
                    $res = json_decode(curl_multi_getcontent($ch));
                    $lastTimestamp = 0;
                    if (!is_null($res)) {
                        foreach ($res as $key => $node) {
                            $dict = std_class_object_to_array($node);

                            $timeValue = floatval($dict['timestamp']);
                            $strDate = date('Y-m-d', $timeValue / 1000);
                            $strHour = intval(date('H', $timeValue / 1000));
                            $strMin = intval(date('i', $timeValue / 1000));
                            $value = floatval($dict['value']);
                            $unit = $dict['unit'];

                            $sql = "MERGE INTO AZW133_zworksdata AS s
                USING (SELECT '$deviceId' AS deviceid,$timeValue as timestmp,$value as value) AS u
                ON s.deviceid = u.deviceid and s.timestmp =u.timestmp and s.value =u.value
                WHEN MATCHED THEN
                    UPDATE
                    SET value=$value
                WHEN NOT MATCHED THEN
                    INSERT(deviceid,value,unit,timestmp,date,hour,minute)
                    VALUES ('$deviceId',$value,'$unit',$timeValue,'$strDate',$strHour,$strMin);";

                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $arrReturn['code'] = '231';
                            }

                            $lastTimestamp = $timeValue;
                        }
                        if ($lastTimestamp != 0) {
                            $sql = "UPDATE AZW132_zworksroommst set timestamp = $lastTimestamp where deviceid ='$deviceId'";
                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $arrReturn['code'] = '241';
                            }
                        }
                    } else {
                        $sessionStatus = 0;
                    }
                }
                curl_close($ch);
            }

            if ($sessionStatus) {
                $sql = "update AZW131_zworksusermst set updatetime='" . date('Y-m-d H:i:s') . "',sessionstatus ='有効' where email='$userId'";
            } else {
                $sql = "update AZW131_zworksusermst set updatetime='" . date('Y-m-d H:i:s') . "',sessionstatus ='無効' where email='$userId'";
            }

            $arrReturn['userid'] = $userId;
            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $arrReturn['code'] = '511';
            }
        }
    }
} else {
    $arrReturn['code'] = '500';
}

sqlsrv_close($conn);

$arrReturn['updatedate'] = date('Y-m-d H:i:s');
sendResponse(json_encode($arrReturn));

?>