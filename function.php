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
		return addslashes(trim(strip_tags($string)));
	}
}

function fnChkGet($name, $allow_all = false)
{
	return isset($_GET[$name]) ? ($allow_all === true ? $_GET[$name] : fnRemoveEscapeString($_GET[$name])) : '';
}

function fnChkPost($name, $allow_all = false)
{
	return isset($_POST[$name]) ? ($allow_all === true ? $_POST[$name] : fnRemoveEscapeString($_POST[$name])) : '';
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

function fnCompressString($string, $lib = 'b')
{
	if ($lib == 'b') {
		return TheYeyoManEncrypt(bzcompress($string, 1), 'strcompress');
	} else {
		return TheYeyoManEncrypt(gzcompress($string, 1), 'strcompress');
	}

	// ใช้ gzcompress ถ้าต้องการการบีบอัดที่รวดเร็วและมีประสิทธิภาพในสถานการณ์ที่ความเร็วเป็นสิ่งสำคัญ เช่น การบีบอัดข้อมูลที่ส่งผ่าน HTTP
	// ใช้ bzcompress ถ้าต้องการบีบอัดข้อมูลให้มีขนาดเล็กที่สุด และไม่กังวลกับความเร็วในการบีบอัด
}

function fnDeCompressString($string, $lib = 'b')
{
	if ($lib == 'b') {
		return bzdecompress(TheYeyoManDecrypt($string, 'strcompress'));
	} else {
		return gzuncompress(TheYeyoManDecrypt($string, 'strcompress'));
	}
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
	// Check for Cloudflare's CF-Connecting-IP header
	if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
		return $_SERVER["HTTP_CF_CONNECTING_IP"];
	}

	// Check for X-Forwarded-For header (can include multiple IPs, take the first one)
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$ipList = explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"]);
		return trim($ipList[0]);  // First IP in the list is the real client IP
	}

	// Default to REMOTE_ADDR if no proxy headers found
	return $_SERVER["REMOTE_ADDR"];
}

function fn_calc_colmn(&$num, $to = 0)
{
	// generate excel column A-ZZ
	// $num default start 1
	$num += $to;
	$stack = floor($num / 26);
	$mod = $num % 26;
	if (empty($mod)) {
		--$stack;
		$mod = 26;
	}
	if (empty($stack)) {
		$str = chr($mod + 64);
	} else {
		$str = chr($stack + 64) . chr($mod + 64);
	}
	// echo "$num | $stack | $mod | $str";
	++$num;
	return $str;
}

function blendChannels(float $alpha, int $channel1, int $channel2): int
{
	// blend 2 channels
	return intval(($channel1 * $alpha) + ($channel2 * (1.0 - $alpha)));
}

function convertRGBAtoHEX6(string $rgba): string
{
	// sanitize
	$rgba = strtolower(trim($rgba));
	// check
	if (substr($rgba, 0, 5) != 'rgba(') {
		return $rgba;
	}
	// extract channels
	$channels = explode(',', substr($rgba, 5, strpos($rgba, ')') - 5));
	// compute rgb with white background
	$alpha = $channels[3];
	$r = blendChannels($alpha, $channels[0], 0xFF);
	$g = blendChannels($alpha, $channels[1], 0xFF);
	$b = blendChannels($alpha, $channels[2], 0xFF);
	return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function convetNumberToBaht($amount_number)
{
	$amount_number = number_format($amount_number, 2, ".", "");
	$pt = strpos($amount_number, ".");
	$number = $fraction = "";
	if ($pt === false)
		$number = $amount_number;
	else {
		$number = substr($amount_number, 0, $pt);
		$fraction = substr($amount_number, $pt + 1);
	}

	$ret = "";
	$baht = convetNumberToText($number);
	if ($baht != "")
		$ret .= $baht . "บาท";

	$satang = convetNumberToText($fraction);
	if ($satang != "")
		$ret .=  $satang . "สตางค์";
	else
		$ret .= "ถ้วน";
	return $ret;
}

function convetNumberToText($number)
{
	$position_call = array("แสน", "หมื่น", "พัน", "ร้อย", "สิบ", "");
	$number_call = array("", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า");
	$number = $number + 0;
	$ret = "";
	if ($number == 0) return $ret;
	if ($number > 1000000) {
		$ret .= convetNumberToText(intval($number / 1000000)) . "ล้าน";
		$number = intval(fmod($number, 1000000));
	}

	$divider = 100000;
	$pos = 0;
	while ($number > 0) {
		$d = intval($number / $divider);
		$ret .= (($divider == 10) && ($d == 2)) ? "ยี่" : ((($divider == 10) && ($d == 1)) ? "" : ((($divider == 1) && ($d == 1) && ($ret != "")) ? "เอ็ด" : $number_call[$d]));
		$ret .= ($d ? $position_call[$pos] : "");
		$number = $number % $divider;
		$divider = $divider / 10;
		$pos++;
	}
	return $ret;
}

function fn_un_serialize($str)
{
	// Solve the single quote problem
	$str = preg_replace_callback('!s:(\d+):"(.*?)";!', function ($match) {
		return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
	}, $str);

	return unserialize($str);
}

function is_image($path)
{
	$a = getimagesize($path);
	$image_type = $a[2];

	if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
		return true;
	}
	return false;
}

function fn_gen_idx($record_id, $table, $field_id, $field_idx, $slug = '', $length = 5, $ym = false)
{
	if ($ym === false) {
		$ym = substr(date('Y') + 543, 2, 2);
	}

	$slug .= $ym;

	$start_number0 = str_pad(0, $length, 0, STR_PAD_LEFT);
	$start_number1 = str_pad(1, $length, 0, STR_PAD_LEFT);

	$strSql = "
		UPDATE {$table} 
		SET {$field_idx}=(
			SELECT 
				IFNULL(new_id.idx, '{$slug}{$start_number1}') AS idx
			FROM (
				SELECT 
				(
						CASE 
							WHEN tb1.{$field_idx} = '' THEN '{$slug}{$start_number1}'
							WHEN tb1.{$field_idx} IS NULL THEN '{$slug}{$start_number1}'
							ELSE (
								CONCAT('{$slug}', RIGHT(CONCAT('{$start_number0}', RIGHT(tb1.{$field_idx},{$length}) + 1), {$length}))
							) 
						END
				) as idx 
				FROM {$table} as tb1 
				WHERE tb1.{$field_idx} LIKE '{$slug}%' 
				ORDER BY tb1.{$field_idx} DESC 
				LIMIT 0,1
			) as new_id 
			RIGHT JOIN (
				SELECT
					1 AS dummy
			) AS dummy_table ON 1 = 1
		) 
		WHERE {$field_id}={$record_id}
	";

	## LARAVEL ##
	// DB::statement($strSql);

	## WP ##
	// global $wpdb;
	// $wpdb->query($strSql);
}

function replace_hyperlink_with_anchor($text)
{
	// Regular expression pattern to match URLs
	$pattern = '/(https?:\/\/[^\s]+)/';

	// Replacement pattern to wrap URLs with <a> tags
	$replacement = '<a href="$1" target="_blank">$1</a>';

	// Perform the replacement
	$result = preg_replace($pattern, $replacement, $text);

	return $result;
}
