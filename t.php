<?php

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';

$discuz = C::app();

$discuz->init();

require_once libfile('function/spacecp');
if($_POST['firstName']) {

$data = [
	'firstName'=>$_POST['firstName'],
	];
DB::insert('test', $data);
} else {
echo 'empty';
}
?>
