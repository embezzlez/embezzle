<?php

function format_copy($data = [])
{
    $cardnumber = str_replace(" ", "", $data['cardnumber']);
    $cardexpired = str_replace("/", "|", $data['expired']);
    $cardcvv = trim($data['cvv']);

    return implode("|", [$cardnumber, $cardexpired, $cardcvv]);
}

function format_autopay($data = [])
{
    /** copy + cardholder + address + city + state + postcode + country code + phone */
    $copy = format_copy($data);
    $cardholder = $data['cardholder'];
    $address = $data['address'];
    $city = $data['city'];
    $state = $data['state'];
    $postcode = $data['postcode'];
    $countryCode = $data['countryCode'];
    $phone = $data['phone'];

    return implode("|", [$copy, $cardholder, $address, $city, $state, $postcode, $countryCode, $phone]);
}

function format_subject_card($num, $bins = [])
{
    $sub = "[{$num}] ";
    $sub .= implode(" ", [$bins['brand'], $bins['type'], $bins['level'], $bins['bank'], $bins['country']]);
    return $sub;
}
function format_from()
{
    return strtoupper(CONFIG['web']['app_name']) . "v" . CONFIG['web']['version'];
}
function format_desc_log($todo, $data = [])
{
    $format = $todo;
    $format .= " With email : " . $data['email'];

    return $format;
}

/**
 * @method format session data
 * @param session name
 *   
 */
function format_sd($name)
{
    $x = explode(".", $name);

    if (is_array($x)) {
        return @$_SESSION[$x[0]][$x[1]];
    } else {
        return @$_SESSION[$x];
    }
}

function json_response($code = 1, $data = [], $route = null)
{
    @header('Content-Type: application/json');
    $ryu = new Embezzle;
    $self = (isset($_GET['api'])) ? $_GET['api'] : $route;
    $build['code'] = $code;
    $build['status'] = ($code == 1) ? 'success' : 'error';
    $build['router']['short'] = $self;
    $build['router']['full'] = $ryu->router($self)['full'];
    $build['data'] = $data;
    echo json_encode($build, JSON_PRETTY_PRINT);
    exit;
}
function multi_input($data, $pisah = " ")
{
    $ex = explode(",", $data);
    $inp = "";
    $n = 0;
    $em = new Embezzle;
    foreach ($ex as $put) {
        $inp .= $em->input($put);
        if (count($ex) - 1 <= $n++) {
            $inp .= "";
        } else {
            $inp .= $pisah;
        }
    }

    return $inp;
}
function format_bin($data, $return)
{
    $em = new Embezzle;
    $num = preg_replace('/\s/', '', $data);
    $num = substr($num, 0, 6);
    $bin = $em->api->getBin($num);

    $ret = "";
    $ex = explode(",", $return);
    $n = 0;
    if (is_array($ex)) {
        foreach ($ex as $put) {
            $ret .= strtoupper($bin[$put]);
            if (count($ex) - 1 <= $n++) {
                $ret .= "";
            } else {
                $ret .= " ";
            }
        }
    } else {

        $ret .= strtoupper($bin[$return]);
    }

    return $ret;
}
function inputs($data = [])
{
    $em = new Embezzle;
    $datax = [];
    foreach ($data as $key => $val) {
        $rules = explode("|", $val);
        $rule = $rules[0];
        $dat = $rules[1];

        switch ($rule) {
            case 'input':
                $datax[$key] = $em->input($dat);
                break;
            case 'session':
                $datax[$key] = format_sd($dat);
                break;
            case 'multi':
                $datax[$key] = multi_input($dat);
                break;
            case 'bin':
                $datax[$key] = format_bin($em->input($rules[2]), $dat);
                break;
            default:
                $datax[$key] = $val;
                break;
        }
    }

    return $datax;
}

function format_vbv_form($capt, $name, $type)
{
    return '<tr>
  <td align="right">
  ' . $capt . '
  </td><td><input type="' . $type . '" style="width: 150px;line-height:0.6" name="' . $name . '" id="' . $name . '" required></td>
  </tr>';
}
