<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/12
 * Time: 13:19
 */

include 'cmslib.php';

$arrReturn = array();
$arrReturn['code'] = '';

@$isApi = $_POST['isapi'];

unset($_SESSION[HASLOGINED]);
unset($_SESSION[LOGINUSER]);
unset($_SESSION[SENSORINFO]);

session_start();
session_unset();
session_destroy();

if ($isApi && !is_empty($isApi)) {
    $arrReturn['code'] = '200';
    sendResponse(json_encode($arrReturn));
} else {
    header("Location: login.php");
    exit();
}