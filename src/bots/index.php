<?php

$s = scandir(__DIR__);
foreach($s as $n)
{
	if($n == 'index.php' || $n == '.' || $n == '..')continue;
	$ext = explode(".",$n);
	$ext = $ext[0];	
	@rename($n,$ext.'.jin');
}
