--TEST--
Net_Gearman_Client::__call() should not trigger strict errors
--FILE--
<?php

require_once 'tests-config.php';
require_once 'Net/Gearman/Client.php';

// Full error reporting enabled
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', true);

$gearman = new Net_Gearman_Client($servers);
$res = $gearman->Sum(array(
    10, 12, 1, 5, 7 
));

// Should be a job handle
echo $res;

?>
--EXPECTREGEX--
^[A-Z]:(.+):[0-9]+$
