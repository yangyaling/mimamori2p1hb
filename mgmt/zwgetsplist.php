<?php
/**
 * シナリオマスタ
 * User: RDX64
 * Date: 2017/5/3
 * Time: 13:02
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';

$spList = array();
$maxProtoId = '';

function getMaxProtoId($conn)
{
    $maxProtoId = '0001';

    $sql = "SELECT RIGHT('0000' + CAST(MAX(protoid) + 1 AS VARCHAR), 4) maxprotoid FROM AZW006_scenarioprotomst";
    $result = sqlsrv_query($conn, $sql);

    if ($result && $row = sqlsrv_fetch_array($result)) {
        if (!is_empty($row[0])) {
            $maxProtoId = $row[0];
        }
    }
    return $maxProtoId;
}

if ($conn) {
    $sql = "SELECT DISTINCT spm.protoid,spm.protoname FROM AZW006_scenarioprotomst spm";

    $result = sqlsrv_query($conn, $sql);

    if ($result) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $spList[$index] = array(
                'protoid' => $row[0],
                'protoname' => $row[1]
            );
            $index = $index + 1;
        }
    }

    $maxProtoId = getMaxProtoId($conn, $facilityCd);
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['splist'] = $spList;
$arrReturn['maxprotoid'] = $maxProtoId;

sendResponse(json_encode($arrReturn));

?>