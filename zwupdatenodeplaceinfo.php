<?php
/**
 * センサー置き場所情報更新。
 * User: yuqy
 * Date: 2016/06/14
 * Time: 9:21
 */
include 'lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$facilityCd = $_POST['facilitycd'];
$staffId = $_POST['staffid'];
$customerId = $_POST['custid'];
$floorNo = $_POST['floorno'];
$placeList = json_decode($_POST['place'], true);
$mainNodeId = $_POST['mainnodeid'];
$mainNodeName = $_POST['mainnodename'];

//$staffId = 'sw00001';
//$facilityCd = '0001';
//$customerId = '00014';
//$floorNo = 'B棟1';
//$placeList = json_decode('[{"displayname" : "クローゼット6","startdate" : "2017-06-06","roomid" : "601号室","nodeid" : "2172","oldmemo" : "トイレ内","nodetype" : "1","memo" : "クロ","serial" : "32303136303632323030303030343033","oldplace" : "2","olddisplaycd" : "05","gatewayid" : "368","placename" : "外","olddisplayname" : "トイレ5","nodename" : "0902","oldplacename" : "内","mainnodeid" : "0901","initflag" : 1,"place" : "1","displaycd" : "06"},{"displayname" : "寝室3","startdate" : "2017-06-06","roomid" : "601号室","nodeid" : "2173","oldmemo" : "寝室内","nodetype" : "1","memo" : "寝室内","serial" : "32303136303632323030303030343032","oldplace" : "2","olddisplaycd" : "03","gatewayid" : "368","placename" : "内","olddisplayname" : "寝室3","nodename" : "0901","oldplacename" : "内","mainnodeid" : "0901","initflag" : 1,"place" : "2","displaycd" : "03"}]', true);

//if ($conn && sqlsrv_begin_transaction($conn)) {
if ($conn) {
    if (!is_empty($placeList)) {
        $insertItemMsg = '';
        $updateItemMsg = '';
        $today = date('Y-m-d');
        foreach ($placeList as $data) {
            $nodeId = $data['nodeid'];
            $sensorId = $data['nodename'];
            $displayName = $data['displayname'];
            $oldDisplayName = $data['olddisplayname'];
            $displayCd = $data['displaycd'];
            $oldDisplayCd = $data['olddisplaycd'];
            $place = $data['place'];
            $oldPlace = $data['oldplace'];
            $placeName = $data['placename'];
            $oldPlaceName = $data['oldplacename'];
            $memo = $data['memo'];
            $oldMemo = $data['oldmemo'];
            $serial = $data['serial'];
            $startDate = $data['startdate'];
            $initFlag = $data['initflag'];

            if ($initFlag == 1) {
                $hasMsg = false;

                if (!is_empty($displayCd) && $displayCd != $oldDisplayCd) {
                    $hasMsg = true;
                }

                if (!is_empty($place) && $place != $oldPlace) {
                    $hasMsg = true;
                }

                if ($hasMsg) {
                    $updateItemMsg .= "更新前\n" . $sensorId . " " . $oldDisplayName . " " . $oldPlaceName . "\n\n更新後\n" .
                        $sensorId . " " . $displayName . " " . $placeName . "\n";

                    $sql = "SELECT 1 FROM AZW009_serialrelation WHERE serial='$serial' AND custid='$customerId'
                            AND sensorid='$sensorId' AND startdate='$startDate'";
                    $result = sqlsrv_query($conn, $sql);
                    if (sqlsrv_has_rows($result)) {
                        if (strtotime($today) == strtotime($startDate)) {
                            $itemSql = '';

                            if (!is_empty($displayCd) && $displayCd != $oldDisplayCd) {
                                $itemSql .= ",displayname='$displayCd'";
                            }

                            if (!is_empty($place) && $place != $oldPlace) {
                                $itemSql .= ",place='$place'";
                            }

                            if ($memo != $oldMemo) {
                                $itemSql .= ",memo='$memo'";
                            }

                            if (!is_empty($itemSql)) {
                                $sql = "UPDATE AZW009_serialrelation SET " . substr($itemSql, 1) . "
                                        WHERE serial='$serial' AND custid='$customerId' AND sensorid='$sensorId' AND startdate='$startDate'";

                                $result = sqlsrv_query($conn, $sql);
                                if (!$result) {
                                    $code = '507';
                                    $errors = sqlsrv_errors();
                                    break;
                                }
                            }
                        } else {
                            $sql = "UPDATE AZW009_serialrelation SET enddate=CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE()-1,120)
                                    WHERE serial='$serial' AND custid='$customerId' AND sensorid='$sensorId' AND startdate='$startDate'";

                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $code = '504';
                                $errors = sqlsrv_errors();
                                break;
                            }
                            if (!is_empty($serial) && !is_empty($customerId) && !is_empty($sensorId)) {
                                $sql = "INSERT INTO AZW009_serialrelation(serial,sensorid,facilitycd,custid,displayname,place,memo,startdate,initflag)
                                        VALUES('$serial','$sensorId','$facilityCd','$customerId','$displayCd','$place','$memo',CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE(),120),1)";

                                $result = sqlsrv_query($conn, $sql);
                                if (!$result) {
                                    $code = '502';
                                    $errors = sqlsrv_errors();
                                    break;
                                }
                            } else {
                                $code = '605';
                                $errors = array('parament is empty.');
                                break;
                            }
                        }
                    } else {
                        $code = '503';
                        $errors = array('db\'s data is not exists..', $sql);
                        break;
                    }
                } else if ($memo != $oldMemo) {
                    $sql = "SELECT 1 FROM AZW009_serialrelation WHERE serial='$serial' AND custid='$customerId'
                            AND sensorid='$sensorId' AND startdate='$startDate'";
                    $result = sqlsrv_query($conn, $sql);
                    if (sqlsrv_has_rows($result)) {
                        $sql = "UPDATE AZW009_serialrelation SET memo='$memo' WHERE serial='$serial' AND custid='$customerId'
                                AND sensorid='$sensorId' AND startdate='$startDate'";

                        $result = sqlsrv_query($conn, $sql);
                        if (!$result) {
                            $code = '505';
                            $errors = sqlsrv_errors();
                            break;
                        }
                    }
                }
            } else {
                if (is_empty($displayCd) && is_empty($oldDisplayCd) && is_empty($place) && is_empty($oldPlace)) {
                    if (!is_empty($memo) && $memo != $oldMemo) {
                        $sql = "SELECT 1 FROM AZW009_serialrelation WHERE serial='$serial' AND custid='$customerId'
                                AND sensorid='$sensorId' AND startdate='$startDate'";
                        $result = sqlsrv_query($conn, $sql);
                        if (sqlsrv_has_rows($result)) {
                            $sql = "UPDATE AZW009_serialrelation SET memo='$memo' WHERE serial='$serial'
                                    AND custid='$customerId' AND sensorid='$sensorId' AND startdate='$startDate'";

                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $code = '506';
                                $errors = sqlsrv_errors();
                                break;
                            }
                        }
                    }
                } else {
                    $sql = "SELECT 1 FROM AZW009_serialrelation WHERE serial='$serial' AND custid='$customerId'
                        AND sensorid='$sensorId' AND startdate='$startDate' AND initflag=0";
                    $result = sqlsrv_query($conn, $sql);
                    if (sqlsrv_has_rows($result)) {
                        $itemSql = '';

                        if (!is_empty($displayCd) && $displayCd != $oldDisplayCd) {
                            $itemSql .= ",displayname='$displayCd'";
                        }

                        if (!is_empty($place) && $place != $oldPlace) {
                            $itemSql .= ",place='$place'";
                        }

                        if ($memo != $oldMemo) {
                            $itemSql .= ",memo='$memo'";
                        }

                        if ((!is_empty($displayName) || !is_empty($oldDisplayName)) && (!is_empty($place) || !is_empty($oldPlace))) {
                            $itemSql .= ",initflag=1";
                        }

                        if (!is_empty($itemSql)) {
                            $sql = "UPDATE AZW009_serialrelation SET " . substr($itemSql, 1) . "
                            WHERE serial='$serial' AND custid='$customerId' AND sensorid='$sensorId' AND startdate='$startDate'";

                            $result = sqlsrv_query($conn, $sql);
                            if (!$result) {
                                $code = '507';
                                $errors = sqlsrv_errors();
                                break;
                            }
                            $insertItemMsg .= "\n" . $sensorId . " " . $displayName . " " . $placeName;
                        }
                    }
                }
            }
        }

        // 設置情報変更履歴を作成する
        if ($code == '200' && (!is_empty($updateItemMsg) || !is_empty($insertItemMsg))) {
            if (!is_empty($insertItemMsg)) {
                $insertItemMsg = "\n以下のセンサー情報が追加されました。" . $insertItemMsg . "\n";
            }
            $insertSql = "INSERT INTO AZW152_vznoticetbl(receiveuser,senduser,noticetype,title,registdate,content)
                          SELECT TOP 1 '$staffId','$customerId','S','センサーの設置情報が更新されました',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120),
                          '【'+fv.roomcd+'】　'+fv.custname+'さんのセンサーの設置情報が更新されました。\n$updateItemMsg\n$insertItemMsg'
                          FROM AZW001_frscview fv WHERE fv.custid='$customerId'";

            if (!sqlsrv_query($conn, $insertSql)) {
                $code = '508';
                $errors = sqlsrv_errors();
            }
        }
    } else {
        $code = '501';
        $errors = array('置き場所情報がない。');
    }

    if ($code == "200" && !is_empty($mainNodeId)) {
        $sql = "SELECT roomcd,floorno FROM AZW001_frscview WHERE staffid='$staffId' AND custid='$customerId'";
        $result = sqlsrv_query($conn, $sql);
        if ($result && $row = sqlsrv_fetch_array($result)) {
            $roomCd = $row[0];
            $floorNo = $row[1];
            $sql = "UPDATE AZW008_custrelation SET mainnodeid='$mainNodeId',mainnodename='$mainNodeName'
                    WHERE custid='$customerId' AND roomcd='$roomCd' AND floorno='$floorNo'";

            $result = sqlsrv_query($conn, $sql);
            if (!$result) {
                $code = '509';
                $errors = sqlsrv_errors();
            }
        } else {
            $code = '510';
            $errors = sqlsrv_errors();
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