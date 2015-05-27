<?php

include_once('Processmanager.php');

$run_i=0;



function add_work($working_num){
	global $run_i;
	$work_num=5-$working_num;
	$work_data=array();
	for ($i=0; $i < $work_num; $i++) { 
		$work_data[]=array("script_name"=>"sleep.php ".$run_i,"script_key"=>$run_i++);
	}
	return $work_data;
}

function end_work($key){
	echo $key.":i die\n";
}



$manager              = new Processmanager();
$manager->executable  = "php";
$manager->path        = "";
$manager->show_output = false;
$manager->sleep_time  = 1;

$manager->exec("add_work","end_work");
