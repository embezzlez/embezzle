<?php
function generate_html($prop = [],$title = 'BADYOUTH')
{
  echo '<!DOCTYPE html>
  <html>
  <head>
  	<meta charset="utf-8">
  	<title> '.$title.'</title>
  </head>
  <body>';
  foreach($prop as $tag=>$value)
  {
  	echo '<'.$tag.'> '.$value.'</'.$tag.'>';
  }
  

}

function end_html()
{
	echo'</body></html>';
}
function fuckdump($arr = array())
{
	echo '<pre style="background:#333;color:#eee;font-family:monospace;padding:10px">';
	$type=gettype($arr);
	if($type == 'array' || $type == 'Object')
	{
		echo '<table style="border:1px solid #0ff;border-collapse:collapse;color:#0ff" border=1>';
		echo '<tr style="background:#eee;color:#333"><td>TYPE</td><td>VALUE</td></tr>';
		foreach($arr as $key=>$val)
		{
			if(is_array($val))
			{
				$val = "<font color=lime>ARRAY => </font>".json_encode($val,JSON_PRETTY_PRINT);
			}
			echo '<tr>';
			echo '<td>'.$key.'</td><td>'.wordwrap($val,200,"<br>").'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	echo '</pre>';
}
function ryu()
{
	return (new Embezzle);
}
function call($class)
{
	return (new $class());
}