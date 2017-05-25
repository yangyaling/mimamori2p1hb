<?php
/**
 * 可視化設定情報取得処理。
 * User: NITIOSRD
 * Date: 2017/2/16
 * Time: 16:22
 */
include '../lib.php';

$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);

$arrReturn = array();
$code = '200';
$errors = array();

$hostCd = $_POST['hostcd'];
//$buildingId = $_POST['buildingid'];
$floorNo = $_POST['floorno'];
$facilityCd = $_POST['facilitycd'];
$staffId = $_POST['staffid'];

$vzConfigInfo = array();
$displayList = array();
$deviceTypeList = array();

//$floorNo = 'A棟1';
//$facilityCd = 'xxxx';
//$hostCd = 'host01';
//$staffId = 'sw00001';

if ($conn) {
    $today = date('Y-m-d 00:00:00');

    if ($facilityCd == DEFAULT_FACILITY_CD) {
        $sql = "SELECT zwm.roomcd,fv.custid,fv.custname,DATEDIFF(DAY,CAST(fv.birthday AS DATE),GETDATE()) / 365 old,
                fv.sex,vz.actionid,vz.actionname,vz.actionclass,vz.actionorder,vz.actionexplain,vz.actionsummary,vz.color1,vz.displaycd1,
                vz.deviceid1,vz.devicetype1,vz.dataexplain1,vz.displaycd2,vz.deviceid2,vz.devicetype2,vz.dataexplain2,cmd1.value displayname1,
                cmd2.value displayname2,cmt1.value devicetypename1,cmt2.value devicetypename2,vz.vzstartdt,vz.vzenddt FROM AZW001_frscview fv
                INNER JOIN AZW134_roommst zwm ON zwm.roomcd=fv.roomcd AND zwm.facilitycd=fv.facilitycd AND zwm.floorno=fv.floorno
                LEFT OUTER JOIN AZW150_vzconfig vz ON vz.userid=fv.custid AND vz.vzenddt IS NULL
                LEFT OUTER JOIN AZW110_classmst AS cmd1 ON cmd1.classcd='" . CLASS_NODE_LOCATION . "' AND cmd1.code=vz.displaycd1
                LEFT OUTER JOIN AZW110_classmst AS cmd2 ON cmd2.classcd='" . CLASS_NODE_LOCATION . "' AND cmd2.code=vz.displaycd2
                LEFT OUTER JOIN AZW110_classmst AS cmt1 ON cmt1.classcd='" . CLASS_DEVICE_TYPE . "' AND cmt1.code=vz.devicetype1
                LEFT OUTER JOIN AZW110_classmst AS cmt2 ON cmt2.classcd='" . CLASS_DEVICE_TYPE . "' AND cmt2.code=vz.devicetype2
                WHERE fv.hostcd='$hostCd' GROUP BY fv.facilitycd,fv.floorno,zwm.roomcd,fv.custid,fv.custname,fv.birthday,fv.sex,vz.actionid,
                vz.actionname,vz.actionclass,vz.actionorder,vz.actionexplain,vz.actionsummary,vz.color1,vz.displaycd1,vz.deviceid1,
                vz.devicetype1,vz.dataexplain1,vz.displaycd2,vz.deviceid2,vz.devicetype2,vz.dataexplain2,cmd1.value,cmd2.value,cmt1.value,
                cmt2.value,vz.vzstartdt,vz.vzenddt ORDER BY fv.facilitycd,fv.floorno,zwm.roomcd,fv.custid,vz.actionclass,vz.actionorder";
    } else {
        $sql = "SELECT zwm.roomcd,fv.custid,fv.custname,DATEDIFF(DAY,CAST(fv.birthday AS DATE),GETDATE()) / 365 old,
            fv.sex,vz.actionid,vz.actionname,vz.actionclass,vz.actionorder,vz.actionexplain,vz.actionsummary,vz.color1,vz.displaycd1,
            vz.deviceid1,vz.devicetype1,vz.dataexplain1,vz.displaycd2,vz.deviceid2,vz.devicetype2,vz.dataexplain2,
            cmd1.value displayname1,cmd2.value displayname2,cmt1.value devicetypename1,cmt2.value devicetypename2,vz.vzstartdt,vz.vzenddt
            FROM AZW001_frscview fv INNER JOIN (SELECT DISTINCT roomcd,floorno,facilitycd FROM AZW134_roommst WHERE floorno='$floorNo'
            AND facilitycd='$facilityCd') zwm ON zwm.roomcd=fv.roomcd AND zwm.facilitycd=fv.facilitycd AND zwm.floorno=fv.floorno
            LEFT OUTER JOIN AZW150_vzconfig vz ON vz.userid=fv.custid AND vz.vzenddt IS NULL
            LEFT OUTER JOIN AZW110_classmst AS cmd1 ON cmd1.classcd='" . CLASS_NODE_LOCATION . "' AND cmd1.code=vz.displaycd1
            LEFT OUTER JOIN AZW110_classmst AS cmd2 ON cmd2.classcd='" . CLASS_NODE_LOCATION . "' AND cmd2.code=vz.displaycd2
            LEFT OUTER JOIN AZW110_classmst AS cmt1 ON cmt1.classcd='" . CLASS_DEVICE_TYPE . "' AND cmt1.code=vz.devicetype1
            LEFT OUTER JOIN AZW110_classmst AS cmt2 ON cmt2.classcd='" . CLASS_DEVICE_TYPE . "' AND cmt2.code=vz.devicetype2
            WHERE fv.facilitycd = '$facilityCd' AND fv.staffid = '$staffId'
            ORDER BY zwm.roomcd,fv.custid,vz.actionclass,vz.actionorder";
    }

    if ($result = sqlsrv_query($conn, $sql)) {
        $index = 0;
        $index2 = 0;
        $actionInfo = array();
        $oldRoomCd = '';
        $oldRoomName = '';
        $oldCustomerId = '';
        $oldCustomerName0 = '';
        $oldCustomerOld = '';
        $oldCustomerSex = '';
        while ($row = sqlsrv_fetch_array($result)) {
            if (!is_empty($oldCustomerId) && $oldCustomerId != $row[1]) {
                $vzConfigInfo[$index2] = array(
                    'roomid' => $oldRoomCd,
                    'userid0' => $oldCustomerId,
                    'username0' => $oldCustomerName0,
                    'userold' => $oldCustomerOld,
                    'usersex' => $oldCustomerSex,
                    'actioninfo' => $actionInfo
                );
                $actionInfo = array();
                $index = 0;
                $index2++;
            }
            if ($row[5]) {
                $actionInfo[$index] = array(
                    'oflag' => 'R',
                    'actionid' => $row[5],
                    'oldactionname' => $row[6],
                    'actionname' => $row[6],
                    'actionclass' => $row[7],
                    'oldactionorder' => $row[8],
                    'actionorder' => $row[8],
                    'oldactionexplain' => $row[9],
                    'actionexplain' => $row[9],
                    'oldactionsummary' => $row[10],
                    'actionsummary' => $row[10],
                    'oldactioncolor' => $row[11],
                    'actioncolor' => $row[11],
                    'olddisplaycd1' => $row[12],
                    'displaycd1' => $row[12],
                    'olddisplayname1' => $row[20],
                    'displayname1' => $row[20],
                    'olddeviceid1' => $row[13],
                    'deviceid1' => $row[13],
                    'olddevicetype1' => $row[14],
                    'devicetype1' => $row[14],
                    'olddevicetypename1' => $row[22],
                    'devicetypename1' => $row[22],
                    'olddataexplain1' => $row[15],
                    'dataexplain1' => $row[15],
                    'olddisplaycd2' => $row[16],
                    'displaycd2' => $row[16],
                    'olddisplayname2' => $row[21],
                    'displayname2' => $row[21],
                    'olddeviceid2' => $row[17],
                    'deviceid2' => $row[17],
                    'olddevicetype2' => $row[18],
                    'devicetype2' => $row[18],
                    'olddevicetypename2' => $row[23],
                    'devicetypename2' => $row[23],
                    'olddataexplain2' => $row[19],
                    'dataexplain2' => $row[19],
                    'vzstartdt' => $row[24],
                    'vzenddt' => $row[25]
                );
            }
            $oldRoomCd = $row[0];
            $oldCustomerId = $row[1];
            $oldCustomerName0 = $row[2];
            $oldCustomerOld = $row[3];
            $oldCustomerSex = $row[4];
            $index++;
        }
        if (!is_empty($oldRoomCd)) {
            $vzConfigInfo[$index2] = array(
                'roomid' => $oldRoomCd,
                'userid0' => $oldCustomerId,
                'username0' => $oldCustomerName0,
                'userold' => $oldCustomerOld,
                'usersex' => $oldCustomerSex,
                'actioninfo' => $actionInfo
            );
        }
    } else {
        $code = '502';
        $errors = sqlsrv_errors();
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

if ($code == '200') {
    $displayList = getCVList($conn, CLASS_NODE_LOCATION);
    $deviceTypeList = getCVList($conn, CLASS_DEVICE_TYPE, 'AND code<=5');
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;

$arrReturn['vzconfiginfo'] = $vzConfigInfo;
$arrReturn['displaylist'] = $displayList;
$arrReturn['devicetypelist'] = $deviceTypeList;

sendResponse(json_encode($arrReturn));

?>
