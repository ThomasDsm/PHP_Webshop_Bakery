<?php
function handleErrors($errno,$errMsg,$errFile,$errLine){
    $log = new Errorlog($errno,$errMsg,$errFile,$errLine);
    $log->WriteError();
}
set_error_handler("handleErrors");

function UncaughtExceptionHandeler($e){
    $log = new ErrorLog($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
    $log->WriteError();
    exit("Oops, er liep iets fout. Gelieve de systeem administrator te contacteren");
}
set_exception_handler('UncaughtExceptionHandeler');
?>