<?php
require_once __DIR__ . '/include/autoload.php';

use KnotLib\Module\Sample\SampleApp;

try{
    (new SampleApp)->install();
}
catch(Throwable $e){
    echo $e->getMessage();
}
