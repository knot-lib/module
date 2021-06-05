<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use knotlib\module\Sample\SampleApp;

try{
    (new SampleApp)->install();
}
catch(Throwable $e){
    echo $e->getMessage();
}
