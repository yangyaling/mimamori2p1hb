<?php
/**
 * シナリオマスタIU
 * User: RDX64
 * Date: 2017/5/2
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$protoId = $_POST['protoid'];
$protoName = $_POST['protoname'];
$scopeCd = $_POST['scopecd'];
$startTime = $_POST['starttime'];
$endTime = $_POST['endtime'];
$spList = json_decode($_POST['splist'], true);

//$protoId = '0001';
//$protoName = '\u71b1\u4e2d\u75c7';
//$nodeType = '1';
//$scopeCd = '0';
//$startTime = null;
//$endTime = null;
//$spList = array(
//    array("nodetype" => "1", "nodetypename" => "\u4eba\u611f", "devicetype" => "1", "devicetypename" => "\u6e29\u5ea6", "pattern" => 3, "time" => 1.5, "value" => 22, "rpoint" => "\u4ee5\u4e0a"),
//    array("nodetype" => "1", "nodetypename" => "\u4eba\u611f", "devicetype" => "2", "devicetypename" => "\u6e7f\u5ea6", "pattern" => 3, "time" => 1.5, "value" => 66, "rpoint" => "\u4ee5\u4e0b"),
//    array("nodetype" => "1", "nodetypename" => "\u4eba\u611f", "devicetype" => "3", "devicetypename" => "\u660e\u308b\u3055", "pattern" => 3, "time" => 1.5, "value" => 66, "rpoint" => "\u4ee5\u4e0b"),
//    array("nodetype" => "1", "nodetypename" => "\u4eba\u611f", "devicetype" => "4", "devicetypename" => "\u4eba\u611f", "pattern" => 3, "time" => 1.5, "value" => 22, "rpoint" => "\u53cd\u5fdc\u306a\u3057")
//);

if ($conn && sqlsrv_begin_transaction($conn)) {
    if (!is_empty($protoId) && !is_empty($spList)) {
        $sql = "DELETE FROM AZW006_scenarioprotomst WHERE protoid = '$protoId'";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '502';
            $errors = sqlsrv_errors();
        } else {
            foreach ($spList as $data) {
                $nodeType = $data['nodetype'];
                $deviceType = $data['devicetype'];
                $pattern = $data['pattern'];
                $time = $data['time'];
                $value = $data['value'];
                $rPoint = $data['rpoint'];

                $sql = "INSERT INTO AZW006_scenarioprotomst (protoid,protoname,nodetype,scopecd,starttime,endtime,devicetype,pattern,[time],[value],rpoint,updateuser,updatedate)
                    VALUES('$protoId','$protoName','$nodeType','$scopeCd','$startTime','$endTime','$deviceType','$pattern','$time','$value','$rPoint','admin'," . $SCH . ".GETJPDATE())";

                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $code = '503';
                    $errors = sqlsrv_errors();
                    break;
                }
            }
        }
    } else {
        $code = '501';
        $errors = array('param is error.');
    }
} else {
    $code = '500';
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
$arrReturn['sl'] = $spList;

sendResponse(json_encode($arrReturn));

?>