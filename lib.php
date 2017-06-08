<?php
/**
 * 共通処理。
 * User: NITIOSRD
 * Date: 2016/12/16
 * Time: 13:26
 */

date_default_timezone_set('Asia/Tokyo');

define("MYSQL_HOST_M", "ja-cdbr-azure-east-a.cloudapp.net");
define("MYSQL_USER", "b5b35eecdcd068");
define("MYSQL_PASS", "b5074189");
define("MYSQL_DB", "rdbeacoAd7N1JMXE");

//define("SERVERNAME", "tcp:mimamori.japaneast.cloudapp.azure.com,1433");
//define("DATABASE", "mimamoriDB2Test");
//define("INPUT_DATABASE", "mimamoriInputDB");
//define("UID", "iosapp");
//define("PWD", "Passw0rd");

define("SERVERNAME", "tcp:mimamori.database.windows.net,1433");
define("DATABASE", "mimamoriDB2");
//define("UID", "sch001");
define("PWD", "Passw0rd");
//define("SCH", "sch001");

define("HASLOGINED", "isloginuser");
define("LOGINUSER", "loginuser");
define("SENSORINFO", "sensorinfo");
define("CLASS_SCENARIO_SCOPE", "scenarioscope");
define("CLASS_GROUP", "group");
define("CLASS_NODE_LOCATION", "nodelocation");
define("CLASS_NODE_PLACE", "nodeplace");
define("CLASS_NODE_TYPE", "nodetype");
define("CLASS_DEVICE_CLASS", "deviceclass");
define("CLASS_DEVICE_TYPE", "devicetype");
define("CLASS_USER_TYPE", "usertype");
define("CLASS_UNREGIST", "未登録");
define("CLASS_NAME_UNREGIST", "未登録");

//define("SYS_DATE_YYYYMMDDHHMMSS", "CONVERT(VARCHAR(19),sch001.GETJPDATE(),120)");

define("DEFAULT_FACILITY_CD", "xxxx");
define("DEFAULT_FACILITY_NAME", "すべて");

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
 * @param [stdclass] $stdclassobject [对象]
 * @return [array] [数组]
 */
function std_class_object_to_array($stdclassobject)
{
    $_array = is_object($stdclassobject) ? get_object_vars($stdclassobject) : $stdclassobject;
    foreach ($_array as $key => $value) {
        $value = (is_array($value) || is_object($value)) ? std_class_object_to_array($value) : $value;
        $array[$key] = $value;
    }
    return $array;
}

function is_empty($value)
{
    return is_null($value) || $value == null || $value == '';
}

function getCVList($conn, $class, $whereString = '')
{
    $sql = "SELECT [code],[value] FROM AZW110_classmst WHERE classcd='$class' $whereString ORDER BY [order]";

    $result = sqlsrv_query($conn, $sql);
    $cvs = array();

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $cvs[$index] = array(
                'cd' => $row[0],
                'name' => $row[1]
            );
            $index = $index + 1;
        }
    }

    return $cvs;
}

function getBaseInfo($conn, $facilityCd)
{
    $baseInfo = array();

    $sql = "SELECT cm.companycd,cm.companyname,hm.hostcd,hm.hostname,fm.facilitycd,fm.facilityname2 FROM AZW003_facilitymst fm
            INNER JOIN AZW002_hostmst hm ON fm.hostcd=hm.hostcd INNER JOIN AZW001_companymst cm ON hm.companycd=cm.companycd
            WHERE fm.facilitycd='$facilityCd'";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $baseInfo[$index] = array(
                'companycd' => $row[0],
                'companyname' => $row[1],
                'hostcd' => $row[2],
                'hostname' => $row[3],
                'facilitycd' => $row[4],
                'facilityname2' => $row[5]
            );
            $index = $index + 1;
        }
    }

    return $baseInfo;
}

$SCH = $_POST['hostcd'];
//$SCH = 'sch003';
$UID = $SCH;

$arrReturn = array();
$code = '200';
$errors = array();

$connectionOptions = array('Database' => DATABASE, 'Uid' => $UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

if ($conn) {
    $sql = "SELECT  1 FROM AZW002_hostmst hm WHERE hm.hostcd='$UID'";

    if ($result = sqlsrv_query($conn, $sql)) {
        if (!sqlsrv_has_rows($result)) {
            $code = '302';
            $errors = array('ホストが存在しません');
        }
    } else {
        $code = '301';
        $errors = sqlsrv_errors();
    }
} else {
    $code = '300';
    $errors = sqlsrv_errors();
}

?>
