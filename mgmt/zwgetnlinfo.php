<?php
/**
 * 設置マスタ一覧
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

$nlList = array();

if ($conn) {
    $sql = "SELECT [code],[value] FROM AZW110_classmst WHERE classcd='" . CLASS_NODE_LOCATION . "' ORDER BY [order]";

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $nlList[$index] = array(
                'cd' => $row[0],
                'oldcd' => $row[0],
                'name' => $row[1]
            );
            $index = $index + 1;
        }
    } else {
        $code = '501';
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['nllist'] = $nlList;

sendResponse(json_encode($arrReturn));

?>