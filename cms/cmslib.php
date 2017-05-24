<?php
/**
 * Created by PhpStorm.
 * User: totyu1
 * Date: 16/01/21
 * Time: 午後0:51
 */

date_default_timezone_set('Asia/Tokyo');

include 'loginuser.php';

define("MYSQL_HOST_M", "ja-cdbr-azure-east-a.cloudapp.net");
define("MYSQL_USER", "b5b35eecdcd068");
define("MYSQL_PASS", "b5074189");
define("MYSQL_DB", "rdbeacoAd7N1JMXE");

//define("SERVERNAME", "tcp:mimamori.japaneast.cloudapp.azure.com,1433");
//define("DATABASE", "mimamoriDB2Test");
//define("UID", "iosapp");
//define("PWD", "Passw0rd");

define("SERVERNAME", "tcp:mimamori.database.windows.net,1433");
define("DATABASE", "mimamoriDB2");
define("UID", "sch001");
define("PWD", "Passw0rd");

define("HASLOGINED", "isloginuser");
define("LOGINUSER", "loginuser");
define("SENSORINFO", "sensorinfo");
define("CLASS_GROUP", "group");
define("CLASS_NODE_PLACE", "nodeplace");
define("CLASS_NODE_TYPE", "nodetype");
define("CLASS_DEVICE_CLASS", "deviceclass");
define("CLASS_DEVICE_TYPE", "devicetype");
define("CLASS_USER_TYPE", "usertype");
define("CLASS_UNREGIST", "未登録");
define("CLASS_NAME_UNREGIST", "未登録");

function checkSession()
{
//    session_cache_limiter('private, must-revalidate');

    if (!session_id()) {
        session_start();
    }
    if (isset($_SESSION[HASLOGINED]) && $_SESSION[HASLOGINED] === true) {
        return unserialize($_SESSION[LOGINUSER]);
    } else {
        unset($_SESSION[HASLOGINED]);
        unset($_SESSION[LOGINUSER]);

        $refer = 'http://' . $_SERVER ['HTTP_HOST'];

        exit("<script type='text/javascript'>top.location.href='$refer/cms/login.php?errno=2'</script>");
    }
}

function getCVList($conn, $class)
{
    $sql = "SELECT [code],[value] FROM AZW110_classmst WHERE classcd='$class' ORDER BY [order]";

    $result = sqlsrv_query($conn, $sql);
    $cvs = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $cvs[$index] = array(
                'id' => $row[0],
                'name' => $row[1]
            );
            $index = $index + 1;
        }
    }

    return $cvs;
}

function getDeviceType($type)
{
    if ($type == 'temperature') {//温度
        return '1';
    } elseif ($type == 'humidity') {//湿度
        return '2';
    } elseif ($type == 'luminance') {//明るさ
        return '3';
    } elseif ($type == 'motion') {//人感
        return '4';
    } elseif ($type == 'open_close') {//ドア
        return '5';
    } elseif ($type == 'battery') {//バッテリー
        return '6';
    } elseif ($type == 'lock') {//鍵
        return '7';
    } elseif ($type == 'cover') {//遮盖
        return '8';
    } elseif ($type == 'heart_rate') {//心率
        return '9';
    } elseif ($type == 'breathing_rate') {//呼吸频率
        return '10';
    } elseif ($type == 'body_motion') {//身体动作
        return '11';
    }
}

function sendResponse($body = '', $content_type = 'text/json')
{
    //$status_header = 'HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status);
    //header($status_header);
    header('Content-type: ' . $content_type);
    header("Content-Length: " . strlen($body));
    echo $body;
}

/**
 * [std_class_object_to_array 将对象转成数组]
 * @param [stdClass] $stdClassObject [对象]
 * @return [array] [数组]
 */
function std_class_object_to_array($stdClassObject)
{
    $array = array();
    $_array = is_object($stdClassObject) ? get_object_vars($stdClassObject) : $stdClassObject;
    foreach ($_array as $key => $value) {
        @$value = (is_array($value) || is_object($value)) ? std_class_object_to_array($value) : $value;
        $array[$key] = $value;
    }
    return $array;
}

function is_empty($value)
{
    return is_null($value) || $value == null || $value == '';
}

?>