<?php
	require_once 'Inc/Diagnostic.php';
	use Inc\Diagnostic;
	$columns = [
				'Темапература'=>'temperature', 
				'Тип дыхания'=>'breathing', 
				'Кашель'=>'cough'
				];

	$test = (new Diagnostic($columns))->decide($array);

	include 'view/layout.php';


?>




















