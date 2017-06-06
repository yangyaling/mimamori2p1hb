<?php
/**
 * 可視化設定情報更新処理。
 * User: NITIOSRD
 * Date: 2017/2/16
 * Time: 17:19
 */
include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
//$arrReturn = array();
//$code = '200';
//$errors = array();

$staffId = $_POST['userid1'];
$customerId = $_POST['userid0'];
$actionInfoArr = json_decode($_POST['actioninfo'], true);

//$staffId = 'sw00001';
//$customerId = '00014';
//////TODO:all:$a = '[{"oflag":"R","actionid":"01","oldactionname":"\u6e29\u5ea6","actionname":"\u6e29\u5ea6","actionclass":"1","oldactionorder":1,"actionorder":1,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#FF56A7","actioncolor":"#FF56A7","olddisplaycd1":"01","displaycd1":"01","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e301","olddeviceid1":"3281","deviceid1":"3281","olddevicetype1":"1","devicetype1":"1","olddevicetypename1":"\u6e29\u5ea6","devicetypename1":"\u6e29\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"R","actionid":"02","oldactionname":"\u6e7f\u5ea6","actionname":"\u6e7f\u5ea6","actionclass":"1","oldactionorder":2,"actionorder":2,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#8B4513","actioncolor":"#8B4513","olddisplaycd1":"01","displaycd1":"01","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e301","olddeviceid1":"3282","deviceid1":"3282","olddevicetype1":"2","devicetype1":"2","olddevicetypename1":"\u6e7f\u5ea6","devicetypename1":"\u6e7f\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"R","actionid":"03","oldactionname":"\u660e\u308b\u3055","actionname":"\u660e\u308b\u3055","actionclass":"1","oldactionorder":3,"actionorder":3,"oldactionexplain":4,"actionexplain":4,"oldactionsummary":0,"actionsummary":0,"oldactioncolor":"#FFFF00|#000080","actioncolor":"#FFFF00|#000080","olddisplaycd1":"01","displaycd1":"01","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e301","olddeviceid1":"3286","deviceid1":"3286","olddevicetype1":"3","devicetype1":"3","olddevicetypename1":"\u660e\u308b\u3055","devicetypename1":"\u660e\u308b\u3055","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"R","actionid":"01","oldactionname":"\u90e8\u5c4b\u9032\u51fa","actionname":"\u90e8\u5c4b\u9032\u51fa","actionclass":"2","oldactionorder":1,"actionorder":1,"oldactionexplain":1,"actionexplain":1,"oldactionsummary":1,"actionsummary":1,"oldactioncolor":"#00BFFF","actioncolor":"#00BFFF","olddisplaycd1":"01","displaycd1":"01","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e301","olddeviceid1":"3283","deviceid1":"3283","olddevicetype1":"4","devicetype1":"4","olddevicetypename1":"\u4eba\u611f","devicetypename1":"\u4eba\u611f","olddataexplain1":2,"dataexplain1":2,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"R","actionid":"02","oldactionname":"\u5c45\u5ba4\u5185\u6d3b\u52d5","actionname":"\u5c45\u5ba4\u5185\u6d3b\u52d5","actionclass":"2","oldactionorder":2,"actionorder":2,"oldactionexplain":1,"actionexplain":1,"oldactionsummary":1,"actionsummary":1,"oldactioncolor":"#FFA500","actioncolor":"#FFA500","olddisplaycd1":"02","displaycd1":"02","olddisplayname1":"\u5c45\u5ba4\u51852","displayname1":"\u5c45\u5ba4\u51852","olddeviceid1":"3313","deviceid1":"3313","olddevicetype1":"4","devicetype1":"4","olddevicetypename1":"\u4eba\u611f","devicetypename1":"\u4eba\u611f","olddataexplain1":2,"dataexplain1":2,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"R","actionid":"03","oldactionname":"\u5bdd\u5ba4\u6d3b\u52d5","actionname":"\u5bdd\u5ba4\u6d3b\u52d5","actionclass":"2","oldactionorder":3,"actionorder":3,"oldactionexplain":1,"actionexplain":1,"oldactionsummary":1,"actionsummary":1,"oldactioncolor":"#008B8B","actioncolor":"#008B8B","olddisplaycd1":"03","displaycd1":"03","olddisplayname1":"\u5bdd\u5ba43","displayname1":"\u5bdd\u5ba43","olddeviceid1":"3320","deviceid1":"3320","olddevicetype1":"5","devicetype1":"5","olddevicetypename1":"\u958b\u9589","devicetypename1":"\u958b\u9589","olddataexplain1":1,"dataexplain1":1,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"R","actionid":"04","oldactionname":"\u6d17\u9762\u53f0\u4f7f\u7528","actionname":"\u6d17\u9762\u53f0\u4f7f\u7528","actionclass":"2","oldactionorder":4,"actionorder":4,"oldactionexplain":1,"actionexplain":1,"oldactionsummary":1,"actionsummary":1,"oldactioncolor":"#FF69B4","actioncolor":"#FF69B4","olddisplaycd1":"04","displaycd1":"04","olddisplayname1":"\u6d17\u9762\u53f04","displayname1":"\u6d17\u9762\u53f04","olddeviceid1":"3324","deviceid1":"3324","olddevicetype1":"4","devicetype1":"4","olddevicetypename1":"\u4eba\u611f","devicetypename1":"\u4eba\u611f","olddataexplain1":2,"dataexplain1":2,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"R","actionid":"05","oldactionname":"\u30c8\u30a4\u30ec\u4f7f\u7528","actionname":"\u30c8\u30a4\u30ec\u4f7f\u7528","actionclass":"2","oldactionorder":5,"actionorder":5,"oldactionexplain":1,"actionexplain":1,"oldactionsummary":1,"actionsummary":1,"oldactioncolor":"#90EE90","actioncolor":"#90EE90","olddisplaycd1":"05","displaycd1":"05","olddisplayname1":"\u30c8\u30a4\u30ec5","displayname1":"\u30c8\u30a4\u30ec5","olddeviceid1":"3328","deviceid1":"3328","olddevicetype1":"5","devicetype1":"5","olddevicetypename1":"\u958b\u9589","devicetypename1":"\u958b\u9589","olddataexplain1":1,"dataexplain1":1,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"R","actionid":"06","oldactionname":"\u30af\u30ed\u30fc\u30bc\u30c3\u30c8\u4f7f\u7528","actionname":"\u30af\u30ed\u30fc\u30bc\u30c3\u30c8\u4f7f\u7528","actionclass":"2","oldactionorder":6,"actionorder":6,"oldactionexplain":6,"actionexplain":6,"oldactionsummary":1,"actionsummary":1,"oldactioncolor":"#9370DB","actioncolor":"#9370DB","olddisplaycd1":"06","displaycd1":"06","olddisplayname1":"\u30af\u30ed\u30fc\u30bc\u30c3\u30c86","displayname1":"\u30af\u30ed\u30fc\u30bc\u30c3\u30c86","olddeviceid1":"5977","deviceid1":"5977","olddevicetype1":"4","devicetype1":"4","olddevicetypename1":"\u4eba\u611f","devicetypename1":"\u4eba\u611f","olddataexplain1":2,"dataexplain1":2,"olddisplaycd2":"06","displaycd2":"06","olddisplayname2":"\u30af\u30ed\u30fc\u30bc\u30c3\u30c86","displayname2":"\u30af\u30ed\u30fc\u30bc\u30c3\u30c86","olddeviceid2":"5980","deviceid2":"5980","olddevicetype2":"3","devicetype2":"3","olddevicetypename2":"\u660e\u308b\u3055","devicetypename2":"\u660e\u308b\u3055","olddataexplain2":3,"dataexplain2":3,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null}]';
//////TODO:i1d1:$a = '[{"oflag":"C","actionid":"XX","oldactionname":"\u6e29\u5ea6","actionname":"\u6e29\u5ea6NEW","actionclass":"1","oldactionorder":1,"actionorder":4,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#FF56A7","actioncolor":"#FFFFFF","olddisplaycd1":"01","displaycd1":"02","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e302","olddeviceid1":"3281","deviceid1":"","olddevicetype1":"1","devicetype1":"1","olddevicetypename1":"\u6e29\u5ea6","devicetypename1":"\u6e29\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"D","actionid":"02","oldactionname":"\u6e7f\u5ea6","actionname":"\u6e7f\u5ea6","actionclass":"1","oldactionorder":2,"actionorder":2,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#8B4513","actioncolor":"#8B4513","olddisplaycd1":"01","displaycd1":"01","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e301","olddeviceid1":"3282","deviceid1":"3282","olddevicetype1":"2","devicetype1":"2","olddevicetypename1":"\u6e7f\u5ea6","devicetypename1":"\u6e7f\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null}]';
//////TODO:i2d1:$a = '[{"oflag":"C","actionid":"XX","oldactionname":"\u6e29\u5ea6","actionname":"\u6e29\u5ea6NEW","actionclass":"1","oldactionorder":1,"actionorder":4,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#FF56A7","actioncolor":"#FFFFFF","olddisplaycd1":"01","displaycd1":"02","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e302","olddeviceid1":"3281","deviceid1":"","olddevicetype1":"1","devicetype1":"1","olddevicetypename1":"\u6e29\u5ea6","devicetypename1":"\u6e29\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"D","actionid":"02","oldactionname":"\u6e7f\u5ea6","actionname":"\u6e7f\u5ea6","actionclass":"1","oldactionorder":2,"actionorder":2,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#8B4513","actioncolor":"#8B4513","olddisplaycd1":"01","displaycd1":"01","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e301","olddeviceid1":"3282","deviceid1":"3282","olddevicetype1":"2","devicetype1":"2","olddevicetypename1":"\u6e7f\u5ea6","devicetypename1":"\u6e7f\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"C","actionid":"XX","oldactionname":"\u6e29\u5ea6","actionname":"\u6e29\u5ea6NEW2","actionclass":"1","oldactionorder":1,"actionorder":4,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#FF56A7","actioncolor":"#FFFFFF","olddisplaycd1":"01","displaycd1":"02","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e303","olddeviceid1":"3281","deviceid1":"","olddevicetype1":"1","devicetype1":"1","olddevicetypename1":"\u6e29\u5ea6","devicetypename1":"\u6e29\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null}]';
//////TODO:i1d1u1c:$a = '[{"oflag":"C","actionid":"XX","oldactionname":"\u6e29\u5ea6","actionname":"\u6e29\u5ea6NEW","actionclass":"1","oldactionorder":1,"actionorder":4,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#FF56A7","actioncolor":"#FFFFFF","olddisplaycd1":"01","displaycd1":"02","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e302","olddeviceid1":"3281","deviceid1":"","olddevicetype1":"1","devicetype1":"1","olddevicetypename1":"\u6e29\u5ea6","devicetypename1":"\u6e29\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"D","actionid":"02","oldactionname":"\u6e7f\u5ea6","actionname":"\u6e7f\u5ea6","actionclass":"1","oldactionorder":2,"actionorder":2,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#8B4513","actioncolor":"#8B4513","olddisplaycd1":"01","displaycd1":"01","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e301","olddeviceid1":"3282","deviceid1":"3282","olddevicetype1":"2","devicetype1":"2","olddevicetypename1":"\u6e7f\u5ea6","devicetypename1":"\u6e7f\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"U","actionid":"04","oldactionname":"\u6d17\u9762\u53f0\u4f7f\u7528","actionname":"\u6d17\u9762\u53f0\u4f7f\u7528U","actionclass":"2","oldactionorder":4,"actionorder":99,"oldactionexplain":1,"actionexplain":3,"oldactionsummary":1,"actionsummary":3,"oldactioncolor":"#FF69B4","actioncolor":"#FF0000","olddisplaycd1":"04","displaycd1":"03","olddisplayname1":"\u6d17\u9762\u53f04","displayname1":"\u6d17\u9762\u53f04to03","olddeviceid1":"3324","deviceid1":"3324","olddevicetype1":"4","devicetype1":"3","olddevicetypename1":"\u4eba\u611f","devicetypename1":"\u4eba\u611f4to3","olddataexplain1":2,"dataexplain1":1,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null}]';
//////TODO:i1d1u1:$a = '[{"oflag":"C","actionid":"XX","oldactionname":"\u6e29\u5ea6","actionname":"\u6e29\u5ea6NEW","actionclass":"1","oldactionorder":1,"actionorder":4,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#FF56A7","actioncolor":"#FFFFFF","olddisplaycd1":"01","displaycd1":"02","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e302","olddeviceid1":"3281","deviceid1":"","olddevicetype1":"1","devicetype1":"1","olddevicetypename1":"\u6e29\u5ea6","devicetypename1":"\u6e29\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"D","actionid":"02","oldactionname":"\u6e7f\u5ea6","actionname":"\u6e7f\u5ea6","actionclass":"1","oldactionorder":2,"actionorder":2,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#8B4513","actioncolor":"#8B4513","olddisplaycd1":"01","displaycd1":"01","olddisplayname1":"\u5c45\u5ba4\u5165\u53e301","displayname1":"\u5c45\u5ba4\u5165\u53e301","olddeviceid1":"3282","deviceid1":"3282","olddevicetype1":"2","devicetype1":"2","olddevicetypename1":"\u6e7f\u5ea6","devicetypename1":"\u6e7f\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null},{"oflag":"U","actionid":"04","oldactionname":"\u6d17\u9762\u53f0\u4f7f\u7528","actionname":"\u6d17\u9762\u53f0\u4f7f\u7528U","actionclass":"2","oldactionorder":4,"actionorder":99,"oldactionexplain":1,"actionexplain":3,"oldactionsummary":1,"actionsummary":3,"oldactioncolor":"#FF69B4","actioncolor":"#FF69B4","olddisplaycd1":"04","displaycd1":"03","olddisplayname1":"\u6d17\u9762\u53f04","displayname1":"\u6d17\u9762\u53f04to03","olddeviceid1":"3324","deviceid1":"3324","olddevicetype1":"4","devicetype1":"3","olddevicetypename1":"\u4eba\u611f","devicetypename1":"\u4eba\u611f4to3","olddataexplain1":2,"dataexplain1":1,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-12 00:00:00","vzenddt":null}]';
//$a='[{"oflag":"R","actionid":"01","oldactionname":"\u6e29\u5ea6","actionname":"\u6e29\u5ea6","actionclass":"1","oldactionorder":1,"actionorder":1,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#000000","actioncolor":"#000000","olddisplaycd1":"02","displaycd1":"02","olddisplayname1":"\u5c45\u5ba4\u51852","displayname1":"\u5c45\u5ba4\u51852","olddeviceid1":"3311","deviceid1":"3311","olddevicetype1":"1","devicetype1":"1","olddevicetypename1":"\u6e29\u5ea6","devicetypename1":"\u6e29\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-23 00:00:00","vzenddt":"2017-05-23 00:00:00"},{"oflag":"D","actionid":"02","oldactionname":"\u6e7f\u5ea6","actionname":"\u6e7f\u5ea6","actionclass":"1","oldactionorder":2,"actionorder":2,"oldactionexplain":5,"actionexplain":5,"oldactionsummary":2,"actionsummary":2,"oldactioncolor":"#000000","actioncolor":"#000000","olddisplaycd1":"02","displaycd1":"02","olddisplayname1":"\u5c45\u5ba4\u51852","displayname1":"\u5c45\u5ba4\u51852","olddeviceid1":"3312","deviceid1":"3312","olddevicetype1":"2","devicetype1":"2","olddevicetypename1":"\u6e7f\u5ea6","devicetypename1":"\u6e7f\u5ea6","olddataexplain1":3,"dataexplain1":3,"olddisplaycd2":null,"displaycd2":null,"olddisplayname2":null,"displayname2":null,"olddeviceid2":null,"deviceid2":null,"olddevicetype2":null,"devicetype2":null,"olddevicetypename2":null,"devicetypename2":null,"olddataexplain2":null,"dataexplain2":null,"vzstartdt":"2017-05-23 00:00:00","vzenddt":"2017-05-23 00:00:00"}]';
//$actionInfoArr = json_decode($a, true);

function getActionId($conn, $customerId, $actionClass)
{
    $actionId = '';

    $sql = "SELECT ISNULL(RIGHT('00' + CAST(RIGHT(MAX(actionid),2) + 1 AS VARCHAR), 2),'01') maxactionid
            FROM AZW150_vzconfig WHERE userid='$customerId' AND actionclass='$actionClass'";

    if ($row = sqlsrv_fetch_array(sqlsrv_query($conn, $sql))) {
        $actionId = $row[0];
    }
    return $actionId;
}

function getDeviceIdByDisplayCdWithDeviceType($conn, $customerId, $displayCd, $deviceType, &$code, &$errors)
{
    $deviceId = '';

    $sql = "SELECT TOP 1 sv.deviceid,sv.displayname,sv.devicetypename FROM AZW230_sensormstview sv
            WHERE sv.custid='$customerId' AND sv.displaycd='$displayCd' AND sv.devicetype='$deviceType' AND sv.enddate IS NULL";

    if ($result = sqlsrv_query($conn, $sql)) {
        $row = sqlsrv_fetch_array($result);
        if ($row && !is_empty($row[0])) {
            $deviceId = $row[0];
        } else {
            $code = '602';
            $errors = array('設置情報１：' . $displayCd . '：' . $row[1] . 'デバイスタイプ：' . $deviceType . '：' . $row[2] . 'デバイスが確定できません。');
        }
    } else {
        $code = '601';
        $errors = sqlsrv_errors();
    }
    return $deviceId;
}

function checkData($data, &$code, &$errors)
{
    $actionName = $data['actionname'];

    if (is_empty($data['actionid'])) {
        $code = '502';
        $errors = array($actionName . 'の設定が不正。');
        return false;
    }

    if (is_empty($data['displaycd1']) || is_empty($data['devicetype1']) || is_empty($data['dataexplain1'])) {
        $code = '502';
        $errors = array($actionName . 'の設定が不正。');
        return false;
    }

    if ($data['actionexplain'] == '6' && (is_empty($data['displaycd2']) || is_empty($data['devicetype2']) || is_empty($data['dataexplain2']))) {
        $code = '502';
        $errors = array($actionName . 'の設定が不正。');
        return false;
    }

    return true;
}

function createVZConfig($conn, $customerId, $data, $today, $hasHistory, &$vzHistory, &$code, &$errors)
{
    $actionId = $data['actionid'];
    $actionName = $data['actionname'];
    $actionClass = $data['actionclass'];
    $vzStartDt = $data['vzstartdt'];
    $actionOrder = $data['actionorder'];
    $actionExplain = $data['actionexplain'];
    $actionSummary = $data['actionsummary'];
    $actionColor = $data['actioncolor'];
    $displayCd1 = $data['displaycd1'];
    $displayName1 = $data['displayname1'];
    $deviceType1 = $data['devicetype1'];
    $deviceTypeName1 = $data['devicetypename1'];
    $dataExplain1 = $data['dataexplain1'];
    $displayCd2 = $data['displaycd2'];
    $displayName2 = $data['displayname2'];
    $deviceType2 = $data['devicetype2'];
    $deviceTypeName2 = $data['devicetypename2'];
    $dataExplain2 = $data['dataexplain2'];

    $sql = "SELECT 1 FROM AZW150_vzconfig  WHERE userid='$customerId' AND actionid='$actionId'
            AND actionclass='$actionClass' AND vzstartdt='$vzStartDt'";

    if ($result = sqlsrv_query($conn, $sql)) {
        if (!sqlsrv_has_rows($result) || !$hasHistory) {
            if ($hasHistory) {
                $newActionId = getActionId($conn, $customerId, $actionClass);
            } else {
                $newActionId = $actionId;
            }
            if (is_empty($deviceId1 = getDeviceIdByDisplayCdWithDeviceType($conn, $customerId, $displayCd1, $deviceType1, $code, $errors))) {
                return false;
            }

            if ($actionExplain == '6') {
                if (is_empty($deviceId2 = getDeviceIdByDisplayCdWithDeviceType($conn, $customerId, $displayCd2, $deviceType2, $code, $errors))) {
                    return false;
                }
                $sql = "INSERT INTO AZW150_vzconfig
                    (userid,actionid,vzstartdt,actionclass,actionname,actionorder,actionexplain,actionsummary,
                    displaycd1,deviceid1,devicetype1,dataexplain1,color1,
                    displaycd2,deviceid2,devicetype2,dataexplain2) VALUES
                    ('$customerId','$newActionId','$today','$actionClass','$actionName','$actionOrder','$actionExplain','$actionSummary',
                    '$displayCd1','$deviceId1','$deviceType1','$dataExplain1','$actionColor',
                    '$displayCd2','$deviceId2','$deviceType2','$dataExplain2')";
            } else {
                $sql = "INSERT INTO AZW150_vzconfig
                    (userid,actionid,vzstartdt,actionclass,actionname,actionorder,actionexplain,actionsummary,
                    displaycd1,deviceid1,devicetype1,dataexplain1,color1) VALUES
                    ('$customerId','$newActionId','$today','$actionClass','$actionName','$actionOrder','$actionExplain','$actionSummary',
                    '$displayCd1','$deviceId1','$deviceType1','$dataExplain1','$actionColor')";
            }

            if (!$result = sqlsrv_query($conn, $sql)) {
                $code = '507';
                $errors = array('sqlsrv_errors' => sqlsrv_errors(), 'sql' => $sql);
                return false;
            }
        } else {
            $code = '506';
            $errors = array("ID：「" . $actionId . "」、区分：「" . $actionClass . "」、開始日時：「" . $vzStartDt . "」が既に存在しました。");
            return false;
        }
    } else {
        $code = '505';
        $errors = sqlsrv_errors();
        return false;
    }

    if ($hasHistory) {
        $vzHistory .= $actionName . "を追加しました。\n";
    }
    return true;
}

function updateVZConfig($conn, $customerId, $data, $today, &$vzHistory, &$code, &$errors)
{
    global $SCH;
    $actionId = $data['actionid'];
    $oldActionName = $data['oldactionname'];
    $actionName = $data['actionname'];
    $actionClass = $data['actionclass'];
    $oldActionOrder = $data['oldactionorder'];
    $actionOrder = $data['actionorder'];
    $oldActionExplain = $data['oldactionexplain'];
    $actionExplain = $data['actionexplain'];
    $oldActionSummary = $data['oldactionsummary'];
    $actionSummary = $data['actionsummary'];
    $oldDisplayCd1 = $data['olddisplaycd1'];
    $displayCd1 = $data['displaycd1'];
    $oldDisplayName1 = $data['olddisplayname1'];
    $displayName1 = $data['displayname1'];
    $oldDeviceType1 = $data['olddevicetype1'];
    $deviceType1 = $data['devicetype1'];
    $oldDeviceTypeName1 = $data['olddevicetypename1'];
    $deviceTypeName1 = $data['devicetypename1'];
    $oldDataExplain1 = $data['olddataexplain1'];
    $dataExplain1 = $data['dataexplain1'];
    $oldDisplayCd2 = $data['olddisplaycd2'];
    $displayCd2 = $data['displaycd2'];
    $oldDisplayName2 = $data['olddisplayname2'];
    $displayName2 = $data['displayname2'];
    $oldDeviceType2 = $data['olddevicetype2'];
    $deviceType2 = $data['devicetype2'];
    $oldDeviceTypeName2 = $data['olddevicetypename2'];
    $deviceTypeName2 = $data['devicetypename2'];
    $oldDataExplain2 = $data['olddataexplain2'];
    $dataExplain2 = $data['dataexplain2'];

    $changeFlag = false;
    $itemSql = '';

    //名称
    if ($actionName != $oldActionName) {
        $changeFlag = true;
        $vzHistory .= $actionName . "の[名称]を（" . $oldActionName . "）から（" . $actionName . "）に変更しました。\n";
        $itemSql .= ",actionname = '$actionName'";
    }
    //優先順位
    if ($actionOrder != $oldActionOrder) {
        $changeFlag = true;
        $vzHistory .= $actionName . "の[優先順位]を（" . $oldActionOrder . "）から（" . $actionOrder . "）に変更しました。\n";
        $itemSql .= ",actionorder = '$actionOrder'";
    }
    //活動解釈方法
    if ($actionExplain != $oldActionExplain) {
        $changeFlag = true;
        $vzHistory .= $actionName . "の[活動解釈方法]を（" . $oldActionExplain . "）から（" . $actionExplain . "）に変更しました。\n";
        $itemSql .= ",actionexplain = '$actionExplain'";
    }
    //活動集計方法
    if ($actionSummary != $oldActionSummary) {
        $changeFlag = true;
        $vzHistory .= $actionName . "の[活動集計方法]を（" . $oldActionSummary . "）から（" . $actionSummary . "）に変更しました。\n";
        $itemSql .= ",actionsummary = '$actionSummary'";
    }
    //センサー情報
    if (($displayCd1 != $oldDisplayCd1) || ($deviceType1 != $oldDeviceType1)) {
        if (is_empty($deviceId1 = getDeviceIdByDisplayCdWithDeviceType($conn, $customerId, $displayCd1, $deviceType1, $code, $errors))) {
            return false;
        }
        $changeFlag = true;
        $vzHistory .= $actionName . "の[センサー情報]を（" . $oldDisplayName1 . "・" . $oldDeviceTypeName1 . "）から（" . $displayName1 . "・" . $deviceTypeName1 . "）に変更しました。\n";
        $itemSql .= ",displaycd1 = '$displayCd1',deviceid1 = '$deviceId1',devicetype1 = '$deviceType1'";
    }
    if ($actionExplain == '6' && ($displayCd2 != $oldDisplayCd2 || $deviceType2 != $oldDeviceType2)) {
        if (is_empty($deviceId2 = getDeviceIdByDisplayCdWithDeviceType($conn, $customerId, $displayCd2, $deviceType2, $code, $errors))) {
            return false;
        }
        $changeFlag = true;
        $vzHistory .= $actionName . "の[センサー情報2]を（" . $oldDisplayName2 . "・" . $oldDeviceTypeName2 . "）から（" . $displayName2 . "・" . $deviceTypeName2 . "）に変更しました。\n";
        $itemSql .= ",displaycd2 = '$displayCd2',deviceid2 = '$deviceId2',devicetype2 = '$deviceType2'";

        //データ解釈方法
        if ($dataExplain2 != $oldDataExplain2) {
            $changeFlag = true;
            $vzHistory .= $actionName . "の[データ解釈方法2]を（" . $oldDataExplain2 . "）から（" . $dataExplain2 . "）に変更しました。\n";
            $itemSql .= ",dataexplain2 = '$dataExplain2'";
        }
    }
    //データ解釈方法
    if ($dataExplain1 != $oldDataExplain1) {
        $changeFlag = true;
        $vzHistory .= $actionName . "の[データ解釈方法]を（" . $oldDataExplain1 . "）から（" . $dataExplain1 . "）に変更しました。\n";
        $itemSql .= ",dataexplain1 = '$dataExplain1'";
    }

    if ($changeFlag) {
        $sql = "SELECT vzstartdt FROM AZW150_vzconfig WHERE userid='$customerId' AND actionid='$actionId'
                AND actionclass='$actionClass' AND vzenddt IS NULL";

        if ($result = sqlsrv_query($conn, $sql)) {
            if ($row = sqlsrv_fetch_array($result)) {
                $vzStartDt = $row[0];
                if (strtotime($today) == strtotime($vzStartDt)) {
                    $sql = "UPDATE AZW150_vzconfig SET " . substr($itemSql, 1) . " WHERE userid='$customerId' AND actionid='$actionId'
                            AND actionclass='$actionClass' AND vzstartdt = '$vzStartDt'";

                    if (!$result = sqlsrv_query($conn, $sql)) {
                        $code = '511';
                        $errors = sqlsrv_errors();
                        return false;
                    }
                } else {
                    $sql = "UPDATE AZW150_vzconfig SET vzenddt=CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE()-1,120)+' 00:00:00'
                            WHERE userid='$customerId' AND actionid='$actionId' AND actionclass='$actionClass' AND vzstartdt='$vzStartDt'";

                    if (!$result = sqlsrv_query($conn, $sql)) {
                        $code = '511';
                        $errors = sqlsrv_errors();
                        return false;
                    }
                    return createVZConfig($conn, $customerId, $data, $today, false, $vzHistory, $code, $errors);
                }
            } else {
                $code = '510';
                $errors = array('sqlsrv_errors' => sqlsrv_errors(), 'sql' => $sql);
                return false;
            }
        } else {
            $code = '509';
            $errors = sqlsrv_errors();
            return false;
        }
    }
    return true;
}

function deleteVZConfig($conn, $customerId, $data, &$vzHistory, &$code, &$errors)
{
    global $SCH;
    $actionId = $data['actionid'];
    $oldActionName = $data['oldactionname'];
    $actionClass = $data['actionclass'];
    $vzStartDt = $data['vzstartdt'];

    $sql = "SELECT 1 FROM AZW150_vzconfig WHERE userid='$customerId' AND actionid='$actionId'
                AND actionclass='$actionClass' AND vzstartdt='$vzStartDt'";

    if (sqlsrv_has_rows(sqlsrv_query($conn, $sql))) {
        $sql = "UPDATE AZW150_vzconfig SET vzenddt = CONVERT(VARCHAR(10)," . $SCH . ".GETJPDATE()-1,120)+' 00:00:00'
                WHERE userid='$customerId' AND actionid='$actionId'
                AND actionclass='$actionClass' AND vzstartdt='$vzStartDt'";

        if (!$result = sqlsrv_query($conn, $sql)) {
            $code = '504';
            $errors = sqlsrv_errors();
            return false;
        }
    } else {
        $code = '503';
        $errors = sqlsrv_errors();
        return false;
    }

    $vzHistory .= $oldActionName . "を削除しました。\n";
    return true;
}

if ($conn && sqlsrv_begin_transaction($conn)) {
    if (!is_empty($customerId) && !is_empty($actionInfoArr)) {
        $vzHistory = '';
        $today = date('Y-m-d 00:00:00');
        foreach ($actionInfoArr as $data) {
            $oFlag = $data['oflag'];

            if (!checkData($data, $code, $errors)) {
                break;
            }

            if ($oFlag == 'C') {
                if (!createVZConfig($conn, $customerId, $data, $today, true, $vzHistory, $code, $errors)) {
                    break;
                }
            } else if ($oFlag == 'U') {
                //色
                $oldActionColor = $data['oldactioncolor'];
                $actionColor = $data['actioncolor'];
                if ($actionColor != $oldActionColor) {
                    $actionId = $data['actionid'];
                    $actionClass = $data['actionclass'];
                    $vzStartDt = $data['vzstartdt'];
                    $sql = "UPDATE AZW150_vzconfig SET color1='$actionColor'
                            WHERE userid='$customerId' AND actionid='$actionId' AND actionclass='$actionClass'";
                    if (!sqlsrv_query($conn, $sql)) {
                        $code = '508';
                        $errors = sqlsrv_errors();
                        break;
                    }
                } else {
                    if (!updateVZConfig($conn, $customerId, $data, $today, $vzHistory, $code, $errors)) {
                        break;
                    }
                }
            } elseif ($oFlag == 'D') {
                if (!deleteVZConfig($conn, $customerId, $data, $vzHistory, $code, $errors)) {
                    break;
                }
            }
        }

        // 設定変更履歴を作成する
        if ($code == '200' && !is_empty($vzHistory)) {
            $vzHistory = "さんの可視化の設定の変更が完了しました。\n" . $vzHistory;
            $insertSql = "INSERT INTO AZW152_vznoticetbl(receiveuser,senduser,noticetype,title,registdate,content)
                          SELECT TOP 1 '$staffId','$customerId','K','可視化設定の変更が完了しました。',CONVERT(VARCHAR(19)," . $SCH . ".GETJPDATE(),120),
                          '【'+ut.roomcd+'】　'+ut.custname+'$vzHistory' FROM AZW001_frscview ut WHERE ut.custid='$customerId'";

            if (!sqlsrv_query($conn, $insertSql)) {
                $code = '504';
                $errors = sqlsrv_errors();
            }
        }
    } else {
        $code = '501';
        $errors = array('パラメータ不正');
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
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

sendResponse(json_encode($arrReturn));

?>

