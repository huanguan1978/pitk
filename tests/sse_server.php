<?php

require_once '../vendor/autoload.php';

use Pitk\Jmsg;

$cbfn = function(?string $id){
    $mts = (string)microtime(true);
    $data = json_encode(['key'=>$mts, 'val'=>$mts]);
    return ['id'=>$mts, 'data'=>$data];
};

return Jmsg::ssemsger($cbfn);
