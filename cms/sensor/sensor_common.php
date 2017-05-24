<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/15
 * Time: 13:44
 */

function getSessionId($conn, $email)
{
    $sessionId = '';

    $sql = "SELECT sessionid FROM AZW131_zworksusermst WHERE email='$email'";

    $result = sqlsrv_query($conn, $sql);
    if ($result && $row = sqlsrv_fetch_array($result)) {
        if (!is_empty($row[0])) {
            $sessionId = $row[0];
        }
    }

    return $sessionId;
}

function getGateways($sessionId)
{
    $gateways = array();

    $url = "https://api.liveconnect.io/api/v3/gateways";

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
        if (!is_null($res)) {
            $index = 0;
            foreach ($res as $key => $node) {
                $dict = std_class_object_to_array($node);
                $id = $dict['id'];
                $gateways[$id] = array();
                $gateways[$id]['id'] = $dict['id'];
                $gateways[$id]['label'] = str_replace('name:', '', $dict['labels'][0]);
                $gateways[$id]['nodes'] = $dict['nodes'];
                $gateways[$id]['status'] = $dict['status'];
                $index++;
            }
        }
    }

    return $gateways;
}

function getZworksGatewayInfoById($sessionId, $gatewayId)
{
    $gateways = array();

    $url = "https://api.liveconnect.io/api/v3/gateways/$gatewayId";

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
        if (!is_null($res)) {
            $dict = std_class_object_to_array($res);
            $gateways['id'] = $dict['id'];
            $gateways['label'] = $dict['labels'][0];
            $gateways['nodes'] = $dict['nodes'];
            $gateways['status'] = $dict['status'];
        }
    }

    return $gateways;
}

function getNodes($sessionId)
{
    $nodes = array();

    $url = "https://api.liveconnect.io/api/v3/nodes";

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
        if (!is_null($res)) {
            $index = 0;
            foreach ($res as $key => $node) {
                $dict = std_class_object_to_array($node);
                $id = $dict['id'];
                $nodes[$id] = array();
                $nodes[$id]['id'] = $dict['id'];
                $nodes[$id]['label'] = str_replace('name:', '', $dict['labels'][0]);
                $nodes[$id]['gateway'] = str_replace('/api/v3/gateways/', '', $dict['gateway']);
                $devices = std_class_object_to_array($dict['zwave']);
                $nodes[$id]['devices'] = $devices['devices'];
                $index++;
            }
        }
    }

    return $nodes;
}

function getZworksNodeInfoById($sessionId, $nodeId)
{
    $nodes = array();

    $url = "https://api.liveconnect.io/api/v3/nodes/$nodeId";

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
        if (!is_null($res)) {
            $dict = std_class_object_to_array($res);
            $nodes['id'] = $dict['id'];
            $nodes['label'] = $dict['labels'][0];
            $nodes['gateway'] = $dict['gateway'];
            $devices = std_class_object_to_array($dict['zwave']);
            $nodes['devices'] = $devices['devices'];
        }
    }

    return $nodes;
}

function getDevices($sessionId)
{
    $devices = array();

    $url = "https://api.liveconnect.io/api/v3/devices";

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
        if (!is_null($res)) {
            $index = 0;
            foreach ($res as $key => $node) {
                $dict = std_class_object_to_array($node);
                $id = $dict['id'];
                $devices[$id] = array();
                $devices[$id]['id'] = $dict['id'];
                $devices[$id]['label'] = str_replace('name:', '', $dict['labels'][0]);
                $devices[$id]['node'] = str_replace('/api/v3/nodes/', '', $dict['node']);;
                $devices[$id]['type'] = $dict['type'];
                $index++;
            }
        }
    }

    return $devices;
}

function getZworksDeviceInfoById($sessionId, $deviceId)
{
    $devices = array();

    $url = "https://api.liveconnect.io/api/v3/devices/$deviceId";

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
        if (!is_null($res)) {
            $dict = std_class_object_to_array($res);
            $devices['id'] = $dict['id'];
            $devices['label'] = $dict['labels'][0];
            $devices['node'] = $dict['node'];
            $devices['type'] = $dict['type'];
        }
    }

    return $devices;
}

function getLatestDeviceValue($sessionId, $deviceId)
{
    $deviceValue = array();

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
        if (!is_null($res)) {
            $index = 0;
            foreach ($res as $key => $node) {
                $deviceValues = array();
                $dict = std_class_object_to_array($node);
                $timevalue = floatval($dict['timestamp']);

                $deviceValues['value'] = floatval($dict['value']);
                $deviceValues['timestamp'] = $timevalue;
                $deviceValues['date'] = date('Y-m-d', $timevalue / 1000);
                $deviceValues['hour'] = intval(date('H', $timevalue / 1000));
                $deviceValues['minute'] = intval(date('i', $timevalue / 1000));
                $deviceValues['unit'] = $dict['unit'];

                $deviceValue[$index] = $deviceValues;
                $index++;
            }
        }
    }

    return $deviceValue;
}

function getDeviceValues($sessionId, $deviceId, $startDate, $endDate)
{
    $deviceValue = array();

    $startTime = strtotime($startDate) * 1000 + 1000;

    $endDateStr = "";
    if ($endDate) {
        $endTime = strtotime($endDate) * 1000 + 1000;
        $endDateStr = "end=$endTime";
    }
    $url = "https://api.liveconnect.io/api/v3/devices/$deviceId/values?start=$startTime" . "&" . $endDateStr;
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
        if (!is_null($res)) {
            $index = 0;
            foreach ($res as $key => $node) {
                $deviceValues = array();
                $dict = std_class_object_to_array($node);
                $timevalue = floatval($dict['timestamp']);

                $deviceValues['value'] = floatval($dict['value']);
                $deviceValues['timestamp'] = $timevalue;
                $deviceValues['date'] = date('Y-m-d', $timevalue / 1000);
                $deviceValues['hour'] = intval(date('H', $timevalue / 1000));
                $deviceValues['minute'] = intval(date('i', $timevalue / 1000));
                $deviceValues['unit'] = $dict['unit'];

                $deviceValue[$index] = $deviceValues;
                $index++;
            }
        }
    }

    return $deviceValue;
}

function getSensorInfo($sessionId)
{
    $gatewayssrc = getGateways($sessionId);
    $nodessrc = getNodes($sessionId);
    $devicessrc = getDevices($sessionId);

    $result = array();
    foreach ($gatewayssrc as $kg => $gateway) {
//        if ($kg == 352 || $kg == 368 || $kg == 370) {
        $result[$kg] = array();
        $result[$kg]['id'] = $gateway['id'];
        $result[$kg]['status'] = $gateway['status'];
        $result[$kg]['name'] = str_replace('name:', '', $gateway['label']);
        $result[$kg]['nodes'] = array();
        foreach ($gateway['nodes'] as $nodeId) {
            foreach ($nodessrc as $kn => $node) {
                if (str_replace('/api/v3/nodes/', '', $nodeId) == $kn) {
                    $nodes = array();
                    $nodes['id'] = $kn;
                    $nodes['name'] = str_replace('name:', '', $node['label']);
                    $nodes['devices'] = array();
                    foreach ($node['devices'] as $deviceId) {
                        foreach ($devicessrc as $kd => $device) {
                            if (str_replace('/api/v3/devices/', '', $deviceId) == $kd) {
                                $devices = array();
                                $devices['id'] = $device['id'];
                                $devices['name'] = str_replace('name:', '', $device['label']);
                                $devices['type'] = $device['type'];
                                $nodes['devices'][$kd] = $devices;
                            }
                        }
                    }
                    $result[$kg]['nodes'][$kn] = $nodes;
                }
            }
        }
//        }
    }
    return $result;
}
