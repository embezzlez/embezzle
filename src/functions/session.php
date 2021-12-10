<?php
/**
 * allow_access
 *
 * @return void
 */
function allow_access()
{
    if(isset($_SESSION['allow']))
    {
        return true;
    }else{
        return false;
    }
}
function block_access()
{
    if(isset($_SESSION['block']))
    {
        return true;
    }else{
        return false;
    }
}
/**
 * session_get
 *
 * @param  mixed $ap
 * @return void
 */
function session_get($ap)
{
    return @$_SESSION[$ap];
}
 function session_has($ap)
{
    return @isset($_SESSION[$ap]);
}
/**
 * set
 *
 * @param  mixed $name
 * @param  mixed $val
 * @return void
 */
function session_set($name,$val)
{
    @$_SESSION[''.$name.''] = $val;
}
/**
 * set_array
 *
 * @param  mixed $data
 * @return void
 */
function session_set_array($data = [])
{
    foreach($data as $key=>$val)
    {
        $_SESSION[''.$key.''] = $val;
    }
    return;
}
function image_base64($imagefile)
{
    $type = pathinfo($imagefile , PATHINFO_EXTENSION);
    $data = file_get_contents($imagefile);
    $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);
    return $base64;
}