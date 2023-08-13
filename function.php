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
		return addslashes(strip_tags(trim($string)));
	}
}

function fnChkGet($name)
{
	return isset($_GET[$name]) ? fnRemoveEscapeString($_GET[$name]) : '';
}

function fnChkPost($name)
{
	return isset($_POST[$name]) ? fnRemoveEscapeString($_POST[$name]) : '';
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

function fnConvertArrToData($arr)
{
	if (empty($arr)) {
		return '';
	}
	return '|' . (is_array($arr) ? implode('|', $arr) : $arr) . '|';
}

function fnConvertDataToArr($data)
{
	$arr = explode('|', $data);
	$arr = array_filter($arr);
	return $arr;
}

function TheBooGeyManEncodeIdx($string, $key = 'mynameispond')
{
	$j = 0;
	$hash = null;
	$key = sha1($key);
	$strLen = strlen($string);
	$keyLen = strlen($key);
	for ($i = 0; $i < $strLen; ++$i) {
		$ordStr = ord(substr($string, $i, 1));
		if ($j == $keyLen) {
			$j = 0;
		}
		$ordKey = ord(substr($key, $j, 1));
		++$j;
		$hash .= strrev(base_convert(dechex($ordStr + $ordKey), 16, 36));
	}
	return $hash;
}
function TheBooGeyManDecodeIdx($string, $key = 'mynameispond')
{
	$j = 0;
	$hash = null;
	$key = sha1($key);
	$strLen = strlen($string);
	$keyLen = strlen($key);
	for ($i = 0; $i < $strLen; $i += 2) {
		$ordStr = hexdec(base_convert(strrev(substr($string, $i, 2)), 36, 16));
		if ($j == $keyLen) {
			$j = 0;
		}
		$ordKey = ord(substr($key, $j, 1));
		++$j;
		$hash .= chr($ordStr - $ordKey);
	}
	return $hash;
}

function TheYeyoManEncrypt($text, $secret_key = 'mynameispond', $secret_iv = 'd&&9"dh4%:@')
{
	$key = hash('sha256', $secret_key);
	$iv = substr(hash('sha256', $secret_iv), 0, 16);
	return base64_encode(openssl_encrypt($text, "AES-256-CBC", $key, 0, $iv));
}

function TheYeyoManDecrypt($text, $secret_key = 'mynameispond', $secret_iv = 'd&&9"dh4%:@')
{
	$key = hash('sha256', $secret_key);
	$iv = substr(hash('sha256', $secret_iv), 0, 16);
	return openssl_decrypt(base64_decode($text), "AES-256-CBC", $key, 0, $iv);
}

function fnCompressString($string)
{
	return TheYeyoManEncrypt(bzcompress($string, 1), 'strcompress');
}

function fnDeCompressString($string)
{
	return bzdecompress(TheYeyoManDecrypt($string, 'strcompress'));
}

function in_array_stack($needle, $haystack, $strict = false)
{
	foreach ($haystack as $item) {
		if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_stack($needle, $item, $strict))) {
			return true;
		}
	}
	return false;
}

function fnDeleteAllFileFolder($dir)
{
	foreach (glob($dir . '/*') as $file) {
		if (is_dir($file)) {
			fnDeleteAllFileFolder($file);
		} else {
			unlink($file);
		}
	}
	rmdir($dir);
}

function fnWriteLogFile($text = '', $file_slug = '', $folder = 'logfile', $path = '')
{
	$file_name = empty($file_slug) ? date('Y-m') : $file_slug . '-' . date('Y-m');
	if (empty($path)) {
		$path = $_SERVER['DOCUMENT_ROOT'];
	}
	$path .= '/' . $folder;
	if (!file_exists($path)) {
		mkdir($path, 0777, true);
	}
	$path .= '/' . $file_name . '.log';
	file_put_contents($path, $text . PHP_EOL, FILE_APPEND);
}

function fnGetClientIp()
{
	$ipaddress = '';
	if (getenv('HTTP_CLIENT_IP'))
		$ipaddress = getenv('HTTP_CLIENT_IP');
	else if (getenv('HTTP_X_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	else if (getenv('HTTP_X_FORWARDED'))
		$ipaddress = getenv('HTTP_X_FORWARDED');
	else if (getenv('HTTP_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	else if (getenv('HTTP_FORWARDED'))
		$ipaddress = getenv('HTTP_FORWARDED');
	else if (getenv('REMOTE_ADDR'))
		$ipaddress = getenv('REMOTE_ADDR');
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}
