<?php
function fnGenerateRandomString($length, $type = array(1, 2, 3))
{
    $characters = '';
    if (in_array(1, $type)) {
        $characters .= '1234567890';
    }
    if (in_array(2, $type)) {
        $characters .= 'abcdefghjkmnpqrstuvwxyz';
    }
    if (in_array(3, $type)) {
        $characters .= 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    }
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; ++$i) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function fnRemoveEscapeString($string)
{
    if (is_array($string)) {
        foreach ($string as $key => $item) {
            $string[$key] = fnRemoveEscapeString($item);
        }
        return $string;
    } else {
        return fnRemoveEscapeString($string);
    }
}

function fnChkGet($name)
{
    return isset($_GET[$name]) ? fnRemoveEscapeString(trim($_GET[$name])) : '';
}

function fnChkPost($name)
{
    return isset($_POST[$name]) ? fnRemoveEscapeString(trim($_POST[$name])) : '';
}

function fnGetAction($except = '')
{
    $get = $_GET;
    if (!empty($except)) {
        if (is_array($except)) {
            foreach ($except as $item) {
                if (isset($get[$item])) {
                    unset($get[$item]);
                }
            }
        } else {
            if (isset($get[$except])) {
                unset($get[$except]);
            }
        }
    }
    return http_build_query($get);
}
