<?php

$connectionOptions = array(
    'Database' => "mimamoriDB2",
    'Uid' => "sch001",
    'PWD' => "Passw0rd",
    'CharacterSet' => 'UTF-8'
);
$conn = sqlsrv_connect("tcp:mimamori.database.windows.net,1433", $connectionOptions);

//调用存储过程，并填充参数
$callSP = " [sch001].[zwcheckscenario] ";

$stmt = sqlsrv_query($conn, $callSP);

if ($stmt === false) {
    echo "Error in executing statement 3.\n";
    die(print_r(sqlsrv_errors(), true));
}

sqlsrv_free_stmt($stmt);

?>
