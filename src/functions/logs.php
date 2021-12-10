<?php

 function statsformat($ket,$data =[])
 {
     $x = date('d-m-Y H:i').' | '.$ket. ' | '.$data['country'].' | '.$data['device'].' | '.$data['browser'].' | '.$data['ip'];
     return $x;
    }
    function save_logs($name,$content)
    {
        $dir_logs = PUBLIC_PATH . '/logs/';
        $fp = fopen($dir_logs . $name.'.log', 'a');
        fwrite($fp,$content."\n");
        fclose($fp);
    }
	 function toLogs($name,$val = [])
	{
		$val =statsformat($val['desc'] , $val['data']);
		return save_logs($name , $val);
    }
    
    function itung($num) {

        if($num>1000) {
      
              $x = round($num);
              $x_number_format = number_format($x);
              $x_array = explode(',', $x_number_format);
              $x_parts = array('K', 'M', 'B', 'T');
              $x_count_parts = count($x_array) - 1;
              $x_display = $x;
              $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
              $x_display .= $x_parts[$x_count_parts - 1];
      
              return $x_display;
      
        }
      
        return $num;
      }
        // function count_stats($filename)
        // {
        //   $dir = dirname(dirname(__DIR__)) . '/Public/logs/'.$filename.'.log';
        //   if(file_exists($dir)){
        //   $c = explode("\n",file_get_contents($dir));
        //   $c = count($c)-1;
        //   }else{
        //     $c=0;
        //   }
        //   return itung($c);
        // }

         function format_logs()
        {
          $dir = PUBLIC_PATH.'/logs/';
          foreach(scandir($dir) as $log)
          {
              if($log == '.' || $log == '..' || $log == 'index.php' )continue;
              @unlink($dir.$log);
          }

        }