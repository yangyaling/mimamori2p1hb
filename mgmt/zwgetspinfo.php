<?php
/**
 * シナリオマスタ
 * User: RDX64
 * Date: 2017/5/3
 * Time: 13:02
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$pdList = array();

$protoId = $_POST['protoid'];

if ($conn) {
//    $protoId = '0002';

    $sql = "SELECT spm.nodetype,spm.protoname FROM AZW006_scenarioprotomst spm WHERE spm.protoid='$protoId'";

    $nodeType = '';
    $protoName = '';
    if ($result = sqlsrv_query($conn, $sql)) {
        if ($row = sqlsrv_fetch_array($result)) {
            $nodeType = $row[0];
            $protoName = $row[1];
        }
    } else {
        $code = '501';
    }

    if ($code == '200') {
        $sql = "SELECT ISNULL(l.nodetype,m.nc) nodetype,m.nv,ISNULL(l.devicetype,m.dc) devicetype,m.dv,l.pattern,l.time,l.value,l.rpoint,ISNULL(l.scopecd,'-') scopecd,CONVERT(VARCHAR(5),l.starttime,108) starttime,CONVERT(VARCHAR(5),l.endtime,108) endtime
                FROM (SELECT n.[code] nc,n.[value] nv,d.[code] dc,d.[value] dv FROM [mimamoriDB2Test].[dbo].[AZW110_classmst] d
                LEFT OUTER JOIN [mimamoriDB2Test].[dbo].[AZW110_classmst] n ON n.classcd='" . CLASS_NODE_TYPE . "'
                AND n.[code]='" . (($nodeType == '2') ? '2' : '1') . "' WHERE d.classcd='" . CLASS_DEVICE_TYPE . "'
                AND d.code IN ('1','2','3','" . (($nodeType == '2') ? '5' : '4') . "')) m
                LEFT OUTER JOIN (SELECT spm.protoid,spm.protoname,spm.nodetype,spm.devicetype,spm.pattern,spm.time,spm.value,
                spm.rpoint,spm.scopecd,spm.starttime,spm.endtime FROM AZW006_scenarioprotomst spm WHERE spm.protoid='$protoId') l
                ON l.nodetype=m.nc AND l.devicetype=m.dc ORDER BY nc,dc";

        if ($result = sqlsrv_query($conn, $sql)) {
            $index = 0;
            while ($row = sqlsrv_fetch_array($result)) {
                if (!is_empty($row[4])) {
                    $arrReturn['protoid'] = $protoId;
                    $arrReturn['protoname'] = $protoName;
                    $arrReturn['scopecd'] = $row[8];
                    $arrReturn['starttime'] = $row[9];
                    $arrReturn['endtime'] = $row[10];
                }
                $spList[$index] = array(
                    'nodetype' => $row[0],
                    'oldnodetype' => $row[0],
                    'nodetypename' => $row[1],
                    'devicetype' => $row[2],
                    'devicetypename' => $row[3],
                    'pattern' => ($row[4] == null) ? '-' : $row[4],
                    'time' => ($row[5] == null) ? '-' : $row[5],
                    'value' => ($row[2] > 3) ? '0' : (($row[6] == null) ? '-' : $row[6]),
                    'rpoint' => ($row[7] == null) ? '-' : $row[7]
                );
                $index = $index + 1;
            }
        }
    }
} else {
    $code = '500';
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['splist'] = $spList;

sendResponse(json_encode($arrReturn));

?>