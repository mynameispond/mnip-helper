<?php

/**
 * สร้างสตริงสุ่มตามความยาวและประเภทที่กำหนด
 *
 * @param int $length ความยาวของสตริงที่ต้องการสร้าง
 * @param array $type (optional) ประเภทของตัวอักษรที่ต้องการใช้ (1 = ตัวเลข, 2 = ตัวพิมพ์เล็ก, 3 = ตัวพิมพ์ใหญ่) (ค่าเริ่มต้น: [1, 2, 3])
 * @return string สตริงสุ่มที่สร้างขึ้น
 */
function fnGenerateRandomString(int $length, array $type = [1, 2, 3]): string
{
	// กำหนดสตริงของตัวอักษรตามประเภทที่กำหนด
	$characters = '';
	$types = [
		1 => '1234567890',
		2 => 'abcdefghjkmnpqrstuvwxyz',
		3 => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
	];

	// วนลูปผ่านประเภทที่กำหนดและเพิ่มตัวอักษรลงใน $characters
	foreach ($type as $t) {
		if (isset($types[$t])) {
			$characters .= $types[$t];
		}
	}

	// คำนวณความยาวของสตริงตัวอักษร
	$charactersLength = strlen($characters);

	// สร้างสตริงสุ่มตามความยาวที่กำหนด
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		// สุ่มตัวอักษรจาก $characters และเพิ่มลงใน $randomString
		$randomString .= $characters[random_int(0, $charactersLength - 1)];
	}

	// คืนค่าสตริงสุ่มที่สร้างขึ้น
	return $randomString;
}

/**
 * ลบ escape character และแท็ก HTML ออกจากสตริงหรืออาร์เรย์
 *
 * @param mixed $string สตริงหรืออาร์เรย์ที่ต้องการลบ escape character และแท็ก HTML
 * @return mixed สตริงหรืออาร์เรย์ที่ถูกลบ escape character และแท็ก HTML แล้ว
 */
function fnRemoveEscapeString(mixed $string): mixed
{
	if (is_array($string)) {
		// ถ้า $string เป็นอาร์เรย์ เรียกใช้ฟังก์ชัน fnRemoveEscapeString() แบบ recursive
		return array_map('fnRemoveEscapeString', $string);
	} else {
		// ถ้า $string เป็นสตริง ลบแท็ก HTML, ช่องว่าง และเพิ่ม escape character
		return addslashes(trim(strip_tags($string)));
	}
}

/**
 * ตรวจสอบค่า GET และคืนค่าที่ถูกลบ escape character และแท็ก HTML แล้ว
 *
 * @param string $name ชื่อของค่า GET ที่ต้องการตรวจสอบ
 * @param bool $allow_all (optional) กำหนดว่าอนุญาตให้คืนค่าเดิมหรือไม่ (ค่าเริ่มต้น: false)
 * @return mixed ค่า GET ที่ถูกลบ escape character และแท็ก HTML แล้ว หรือสตริงว่างถ้าไม่มีค่า
 */
function fnChkGet(string $name, bool $allow_all = false): mixed
{
	// ตรวจสอบว่ามีค่า GET ตามชื่อที่ระบุหรือไม่
	if (isset($_GET[$name])) {
		// ถ้ามีค่า GET ตรวจสอบว่าอนุญาตให้คืนค่าเดิมหรือไม่
		if ($allow_all === true) {
			// ถ้าอนุญาต คืนค่าเดิม
			return $_GET[$name];
		} else {
			// ถ้าไม่อนุญาต ลบ escape character และแท็ก HTML แล้วคืนค่า
			return fnRemoveEscapeString($_GET[$name]);
		}
	} else {
		// ถ้าไม่มีค่า GET คืนค่าสตริงว่าง
		return '';
	}
}

/**
 * ตรวจสอบค่า POST และคืนค่าที่ถูกลบ escape character และแท็ก HTML แล้ว
 *
 * @param string $name ชื่อของค่า POST ที่ต้องการตรวจสอบ
 * @param bool $allow_all (optional) กำหนดว่าอนุญาตให้คืนค่าเดิมหรือไม่ (ค่าเริ่มต้น: false)
 * @return mixed ค่า POST ที่ถูกลบ escape character และแท็ก HTML แล้ว หรือสตริงว่างถ้าไม่มีค่า
 */
function fnChkPost(string $name, bool $allow_all = false): mixed
{
	// ตรวจสอบว่ามีค่า POST ตามชื่อที่ระบุหรือไม่
	if (isset($_POST[$name])) {
		// ถ้ามีค่า POST ตรวจสอบว่าอนุญาตให้คืนค่าเดิมหรือไม่
		if ($allow_all === true) {
			// ถ้าอนุญาต คืนค่าเดิม
			return $_POST[$name];
		} else {
			// ถ้าไม่อนุญาต ลบ escape character และแท็ก HTML แล้วคืนค่า
			return fnRemoveEscapeString($_POST[$name]);
		}
	} else {
		// ถ้าไม่มีค่า POST คืนค่าสตริงว่าง
		return '';
	}
}

/**
 * สร้าง query string จากค่า GET โดยละเว้นพารามิเตอร์ที่ระบุ
 *
 * @param mixed $except (optional) ชื่อพารามิเตอร์ที่ต้องการละเว้น (สตริงหรืออาร์เรย์) (ค่าเริ่มต้น: '')
 * @return string query string ที่สร้างขึ้น
 */
function fnGetAction(mixed $except = ''): string
{
	// ดึงค่า GET ทั้งหมด
	$get = $_GET;

	// ตรวจสอบว่ามีพารามิเตอร์ที่ต้องการละเว้นหรือไม่
	if (!empty($except)) {
		// แปลง $except เป็นอาร์เรย์ ถ้าไม่ใช่
		$except = (array) $except;

		// สร้างอาร์เรย์ที่มี keys เป็นพารามิเตอร์ที่ต้องการละเว้น
		$exceptKeys = array_flip($except);

		// ลบพารามิเตอร์ที่ต้องการละเว้นออกจากอาร์เรย์ $get
		$get = array_diff_key($get, $exceptKeys);
	}

	// สร้าง query string จากอาร์เรย์ $get
	return http_build_query($get);
}

/**
 * แปลงอาร์เรย์หรือค่าเป็นสตริงที่มีตัวคั่น '|'
 *
 * @param mixed $arr อาร์เรย์หรือค่าที่ต้องการแปลง
 * @return string สตริงที่แปลงแล้ว หรือสตริงว่างหาก $arr ว่าง
 */
function fnConvertArrToData(mixed $arr): string
{
	// ตรวจสอบว่า $arr ว่างหรือไม่
	if (empty($arr)) {
		// ถ้า $arr ว่าง คืนค่าสตริงว่าง
		return '';
	}

	// ตรวจสอบว่า $arr เป็นอาร์เรย์หรือไม่
	if (is_array($arr)) {
		// ถ้า $arr เป็นอาร์เรย์ แปลงเป็นสตริงโดยใช้ implode() และตัวคั่น '|'
		$data = '|' . implode('|', $arr) . '|';
	} else {
		// ถ้า $arr ไม่ใช่อาร์เรย์ ใช้ค่า $arr โดยตรง
		$data = '|' . $arr . '|';
	}

	// คืนค่าสตริงที่แปลงแล้ว
	return $data;
}

/**
 * แปลงสตริงที่มีตัวคั่น '|' เป็นอาร์เรย์ โดยกรองค่าว่างออก
 *
 * @param string $data สตริงที่ต้องการแปลง
 * @return array อาร์เรย์ที่แปลงแล้ว
 */
function fnConvertDataToArr(string $data): array
{
	// แยกสตริง $data ด้วยตัวคั่น '|' และกรองค่าว่างออก
	return array_filter(explode('|', $data));
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

/**
 * เข้ารหัสข้อความโดยใช้ AES-256-CBC
 *
 * @param string $text ข้อความที่ต้องการเข้ารหัส
 * @param string $secret_key (optional) คีย์ลับที่ใช้ในการเข้ารหัส (ค่าเริ่มต้น: 'mynameispond')
 * @param string $secret_iv (optional) ค่า IV ที่ใช้ในการเข้ารหัส (ค่าเริ่มต้น: 'd&&9"dh4%:@')
 * @return string ข้อความที่เข้ารหัสแล้ว (base64 encoded)
 */
function TheYeyoManEncrypt(string $text, string $secret_key = 'mynameispond', string $secret_iv = 'd&&9"dh4%:@'): string
{
	// สร้างคีย์ 256 บิตจาก secret_key โดยใช้ SHA-256
	$key = hash('sha256', $secret_key);

	// สร้างค่า IV 16 ไบต์จาก secret_iv โดยใช้ SHA-256 และตัดให้เหลือ 16 ไบต์แรก
	$iv = substr(hash('sha256', $secret_iv), 0, 16);

	// เข้ารหัสข้อความโดยใช้ AES-256-CBC และ base64 encode ผลลัพธ์
	return base64_encode(openssl_encrypt($text, "AES-256-CBC", $key, 0, $iv));
}

/**
 * ถอดรหัสข้อความที่เข้ารหัสด้วย AES-256-CBC
 *
 * @param string $text ข้อความที่เข้ารหัส (base64 encoded)
 * @param string $secret_key (optional) คีย์ลับที่ใช้ในการถอดรหัส (ค่าเริ่มต้น: 'mynameispond')
 * @param string $secret_iv (optional) ค่า IV ที่ใช้ในการถอดรหัส (ค่าเริ่มต้น: 'd&&9"dh4%:@')
 * @return string|false ข้อความที่ถอดรหัสแล้ว หรือ false หากถอดรหัสล้มเหลว
 */
function TheYeyoManDecrypt(string $text, string $secret_key = 'mynameispond', string $secret_iv = 'd&&9"dh4%:@'): string|false
{
	// สร้างคีย์ 256 บิตจาก secret_key โดยใช้ SHA-256
	$key = hash('sha256', $secret_key);

	// สร้างค่า IV 16 ไบต์จาก secret_iv โดยใช้ SHA-256 และตัดให้เหลือ 16 ไบต์แรก
	$iv = substr(hash('sha256', $secret_iv), 0, 16);

	// ถอดรหัสข้อความที่เข้ารหัส (base64 decoded) โดยใช้ AES-256-CBC
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

/**
 * ตรวจสอบว่าค่าที่ระบุอยู่ในอาร์เรย์หรือไม่ โดยรองรับอาร์เรย์หลายมิติ
 *
 * @param mixed $needle ค่าที่ต้องการค้นหา
 * @param array $haystack อาร์เรย์ที่ต้องการค้นหา
 * @param bool $strict (optional) กำหนดการเปรียบเทียบแบบเคร่งครัด (ค่าเริ่มต้น: false)
 * @return bool คืนค่า true หากพบค่าในอาร์เรย์, false หากไม่พบ
 */
function in_array_stack(mixed $needle, array $haystack, bool $strict = false): bool
{
	// วนลูปผ่านแต่ละไอเท็มในอาร์เรย์ $haystack
	foreach ($haystack as $item) {
		// ตรวจสอบว่าไอเท็มปัจจุบันตรงกับค่า $needle หรือไม่
		if (
			($strict ? ($item === $needle) : ($item == $needle)) ||
			(is_array($item) && in_array_stack($needle, $item, $strict))
		) {
			// หากตรงกัน หรือไอเท็มเป็นอาร์เรย์และมีการเรียกใช้ฟังก์ชันแบบ recursive
			return true; // คืนค่า true (พบค่าในอาร์เรย์)
		}
	}
	return false; // ไม่พบค่าในอาร์เรย์ คืนค่า false
}

function fnDeleteAllFileFolder(string $dir): void
{
	// ฟังก์ชันนี้ลบไฟล์และโฟลเดอร์ทั้งหมดใน directory ที่ระบุแบบ recursive
	// รับค่า $dir เป็น path ของ directory ที่ต้องการลบ

	if (!is_dir($dir)) {
		// ตรวจสอบว่า $dir เป็น directory หรือไม่
		return; // ถ้าไม่ใช่ directory ไม่ต้องทำอะไร
	}

	$files = glob($dir . '/*');
	// ดึงรายชื่อไฟล์และโฟลเดอร์ทั้งหมดใน $dir

	if (is_array($files)) {
		// ตรวจสอบว่า $files เป็น array หรือไม่
		foreach ($files as $file) {
			// วนลูปผ่านไฟล์และโฟลเดอร์ทั้งหมด
			if (is_dir($file)) {
				// ถ้า $file เป็น directory
				fnDeleteAllFileFolder($file);
				// เรียกฟังก์ชัน fnDeleteAllFileFolder() แบบ recursive เพื่อลบ directory นั้น
			} else {
				// ถ้า $file เป็นไฟล์
				unlink($file);
				// ลบไฟล์
			}
		}
	}

	rmdir($dir);
	// ลบ directory $dir
}

/**
 * เขียนข้อมูลลงไฟล์ log ในรูปแบบ JSON
 *
 * @param string $text ข้อความที่จะเขียนลง log (ค่าเริ่มต้น: '')
 * @param string|null $path path ของ directory ที่จะเก็บไฟล์ log (ค่าเริ่มต้น: DOCUMENT_ROOT/secure/logs/)
 * @param string|null $file ชื่อไฟล์ log (ค่าเริ่มต้น: วันที่ปัจจุบัน.log)
 * @return void
 */
function fnWriteLogFile(string $text = '', ?string $path = null, ?string $file = null): void
{
	// กำหนดชื่อไฟล์ log ถ้า $file เป็น null จะใช้ชื่อไฟล์ตามวันที่ปัจจุบัน
	$file = $file ?? date('Y-m-d') . '.log';

	// กำหนด path ของไฟล์ log ถ้า $path เป็น null จะใช้ DOCUMENT_ROOT/secure/logs/
	$path = $path ?? $_SERVER['DOCUMENT_ROOT'] . '/secure/logs/';

	// ตรวจสอบว่า path ลงท้ายด้วย '/' หรือไม่ ถ้าไม่ลงท้ายให้เพิ่ม '/'
	if (substr($path, -1) !== '/') {
		$path .= '/';
	}

	// ตรวจสอบว่า path มีอยู่หรือไม่ ถ้าไม่มีสร้าง directory ขึ้นมา
	if (!file_exists($path)) {
		mkdir($path, 0777, true);
	}

	// กำหนดวันที่และเวลาปัจจุบัน
	$dateTime = date('Y-m-d H:i:s');

	// กำหนด URL เต็มของ request
	$fullUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

	// ดึง IP address ของ client โดยใช้ฟังก์ชัน fnGetClientIp()
	$userIp = fnGetClientIp();

	// ดึง user agent ของ client ถ้ามี
	$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

	// ดึงข้อมูล GET ถ้ามี
	$getData = $_GET ?? [];

	// ดึงข้อมูล POST ถ้ามี
	$postData = $_POST ?? [];

	// ดึงข้อมูล php://input ถ้ามี
	$phpInput = file_get_contents('php://input') ?? '';

	// ดึงข้อมูล session ถ้ามี
	$sessionData = $_SESSION ?? [];

	// สร้าง array ของข้อมูลที่จะเขียนลงไฟล์ log
	$arr_text = [
		'dateTime' => $dateTime,
		'userIp' => $userIp,
		'fullUrl' => $fullUrl,
		'userAgent' => $userAgent,
		'getData' => $getData,
		'postData' => $postData,
		'phpInput' => $phpInput,
		'sessionData' => $sessionData,
		'text' => $text,
	];

	// เขียนข้อมูลลงไฟล์ log ในรูปแบบ JSON และเพิ่ม newline ต่อท้าย
	file_put_contents($path . $file, json_encode($arr_text, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
}

/**
 * ดึงค่า IP address ของ client ที่เชื่อมต่อเข้ามา
 *
 * @return string IP address ของ client
 */
function fnGetClientIp(): string
{
	// ตรวจสอบ header 'HTTP_CF_CONNECTING_IP' (ใช้โดย Cloudflare)
	if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
		// ถ้ามี header นี้ แสดงว่า client เชื่อมต่อผ่าน Cloudflare
		return $_SERVER['HTTP_CF_CONNECTING_IP'];
		// คืนค่า IP address ที่ได้จาก Cloudflare
	}

	// ตรวจสอบ header 'HTTP_X_FORWARDED_FOR' (ใช้โดย proxy)
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		// header นี้อาจมีหลาย IP address คั่นด้วย comma
		$ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		// แยก IP address ออกจาก string โดยใช้ comma เป็นตัวคั่น
		return trim($ipList[0]);
		// คืนค่า IP address แรกใน list (เป็น IP address ของ client จริง)
	}

	// ถ้าไม่มี header 'HTTP_CF_CONNECTING_IP' หรือ 'HTTP_X_FORWARDED_FOR'
	// แสดงว่า client เชื่อมต่อโดยตรง (ไม่ผ่าน proxy หรือ Cloudflare)
	return $_SERVER['REMOTE_ADDR'];
	// คืนค่า IP address จาก 'REMOTE_ADDR'
}

/**
 * แปลงตัวเลขเป็นชื่อคอลัมน์ Excel (A, B, C, ..., AA, AB, ...)
 *
 * @param int $num ตัวเลขที่ต้องการแปลง (รับค่าแบบ reference)
 * @param int $skip จำนวนคอลัมน์ที่ต้องการข้าม (ค่าเริ่มต้น: 0)
 * @return string ชื่อคอลัมน์ Excel ที่ได้จากการแปลง
 */
function fn_calc_colmn(int &$num, int $skip = 0): string
{
	// เพิ่มค่า $skip เข้าไปใน $num เพื่อเลื่อนตำแหน่งคอลัมน์
	$num += $skip;

	// ลบ 1 ออกจาก $num เพื่อให้เริ่มนับจาก 0 (A = 0, B = 1, ...)
	$tempNum = $num - 1;

	// กำหนดตัวแปร $str เป็นสตริงว่าง เพื่อเก็บชื่อคอลัมน์
	$str = '';

	// วนลูปจนกว่า $tempNum จะน้อยกว่า 0
	while ($tempNum >= 0) {
		// คำนวณค่า modulo 26 เพื่อหา index ของตัวอักษร (0-25)
		$mod = $tempNum % 26;

		// แปลง index เป็นตัวอักษร (A-Z) และเพิ่มเข้าไปในสตริง $str
		$str = chr($mod + 65) . $str;

		// หาร $tempNum ด้วย 26 และลบ 1 เพื่อคำนวณตัวอักษรต่อไป
		$tempNum = (int)($tempNum / 26) - 1;
	}

	// เพิ่มค่า $num ขึ้น 1 สำหรับการเรียกใช้ครั้งถัดไป
	++$num;

	// คืนค่าสตริง $str ที่เป็นชื่อคอลัมน์ Excel
	return $str;

	/*
	ตัวอย่างการใช้งาน
	$num = 1; // กำหนดค่าเริ่มต้นให้กับ $num
	echo fn_calc_colmn($num); // เรียกใช้ฟังก์ชันครั้งแรก (A)
	echo fn_calc_colmn($num, 2); // เรียกใช้ฟังก์ชันครั้งที่สอง โดยข้ามไป 2 ตัวอักษร (C)
	echo fn_calc_colmn($num); // เรียกใช้ฟังก์ชันครั้งที่สาม (D)
	*/
}

/**
 * ผสมสองช่องสีเข้าด้วยกันโดยใช้ค่า alpha
 *
 * @param float $alpha ค่า alpha สำหรับการผสมสี (0.0 - 1.0)
 * @param int $channel1 ค่าช่องสีแรก (0 - 255)
 * @param int $channel2 ค่าช่องสีที่สอง (0 - 255)
 * @return int ค่าช่องสีที่ผสมแล้ว (0 - 255)
 */
function blendChannels(float $alpha, int $channel1, int $channel2): int
{
	// คำนวณค่าช่องสีที่ผสมแล้วโดยใช้ค่า alpha
	$blendedChannel = ($channel1 * $alpha) + ($channel2 * (1.0 - $alpha));

	// แปลงค่าช่องสีที่ผสมแล้วเป็นจำนวนเต็มและคืนค่า
	return (int) $blendedChannel;
}

/**
 * แปลงค่าสี RGBA เป็นค่าสี HEX6
 *
 * @param string $rgba สตริงค่าสี RGBA เช่น 'rgba(255, 0, 0, 0.5)'
 * @return string ค่าสี HEX6 เช่น '#800000' หรือสตริงเดิมถ้าไม่ตรงตามรูปแบบ RGBA
 */
function convertRGBAtoHEX6(string $rgba): string
{
	// แปลงสตริงเป็นตัวพิมพ์เล็กและลบช่องว่าง
	$rgba = strtolower(trim($rgba));

	// ตรวจสอบว่าสตริงเริ่มต้นด้วย 'rgba(' หรือไม่
	if (strpos($rgba, 'rgba(') !== 0) {
		// ถ้าไม่เริ่มต้นด้วย 'rgba(' คืนค่าสตริงเดิม
		return $rgba;
	}

	// ดึงส่วนของช่องสีออกจากสตริง $rgba
	$channel_string = substr($rgba, 5, strpos($rgba, ')') - 5);

	// แยกช่องสีออกจากสตริงเป็นอาร์เรย์
	$channels = explode(',', $channel_string);

	// แปลงค่า alpha เป็นตัวเลขทศนิยม
	$alpha = (float) $channels[3];

	// คำนวณค่าสีแดงโดยผสมกับสีขาว (0xFF)
	$r = blendChannels($alpha, (int) $channels[0], 0xFF);

	// คำนวณค่าสีเขียวโดยผสมกับสีขาว (0xFF)
	$g = blendChannels($alpha, (int) $channels[1], 0xFF);

	// คำนวณค่าสีน้ำเงินโดยผสมกับสีขาว (0xFF)
	$b = blendChannels($alpha, (int) $channels[2], 0xFF);

	// จัดรูปแบบค่าสี RGB เป็นสตริง HEX6 และคืนค่า
	return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/**
 * แปลงตัวเลขเป็นข้อความภาษาไทยในรูปแบบ "บาท" และ "สตางค์"
 *
 * @param float $amount_number ตัวเลขทศนิยมที่ต้องการแปลง
 * @return string ข้อความภาษาไทยที่แปลงจากตัวเลข
 */
function convetNumberToBaht(float $amount_number): string
{
	// จัดรูปแบบตัวเลขให้มีทศนิยม 2 ตำแหน่ง
	$amount_number = number_format($amount_number, 2, '.', '');

	// หาตำแหน่งของจุดทศนิยม
	$point_position = strpos($amount_number, '.');

	// แยกส่วนของจำนวนเต็มและทศนิยม
	if ($point_position === false) {
		$baht_amount = $amount_number;
		$satang_amount = '00';
	} else {
		$baht_amount = substr($amount_number, 0, $point_position);
		$satang_amount = substr($amount_number, $point_position + 1);
	}

	// แปลงส่วนของจำนวนเต็มเป็นข้อความ
	$baht_text = convetNumberToText((int) $baht_amount);

	// สร้างข้อความส่วนของ "บาท"
	if ($baht_text !== '') {
		$result = $baht_text . 'บาท';
	} else {
		$result = '';
	}

	// แปลงส่วนของทศนิยมเป็นข้อความ
	if ($satang_amount !== '00') {
		$satang_text = convetNumberToText((int) $satang_amount);
		if ($satang_text !== '') {
			$result .= $satang_text . 'สตางค์';
		} else {
			$result .= 'ศูนย์สตางค์';
		}
	} else {
		$result .= 'ถ้วน';
	}

	// คืนค่าผลลัพธ์เป็นสตริง
	return $result;
}

/**
 * แปลงตัวเลขเป็นข้อความภาษาไทย
 *
 * @param int $number ตัวเลขที่ต้องการแปลง
 * @return string ข้อความภาษาไทยที่แปลงจากตัวเลข
 */
function convetNumberToText(int $number): string
{
	// กำหนดอาร์เรย์ของคำเรียกตำแหน่งหลักและตัวเลข
	$position_call = ["แสน", "หมื่น", "พัน", "ร้อย", "สิบ", ""];
	$number_call = ["", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];

	// แปลงตัวเลขเป็นจำนวนเต็ม
	$number = (int) $number;

	// ถ้าตัวเลขเป็น 0 คืนค่าสตริงว่าง
	if ($number === 0) {
		return "";
	}

	// ถ้าตัวเลขมากกว่าหรือเท่ากับ 1 ล้าน
	if ($number >= 1000000) {
		$ret = convetNumberToText((int) ($number / 1000000)) . "ล้าน";
		$number = (int) ($number % 1000000);
	} else {
		$ret = "";
	}

	// วนลูปเพื่อแปลงตัวเลขเป็นข้อความ
	$divider = 100000;
	$pos = 0;
	while ($number > 0) {
		$d = (int) ($number / $divider);
		$ret .= (($divider === 10) && ($d === 2)) ? "ยี่" : ((($divider === 10) && ($d === 1)) ? "" : ((($divider === 1) && ($d === 1) && ($ret !== "")) ? "เอ็ด" : $number_call[$d]));
		$ret .= ($d > 0 ? $position_call[$pos] : "");
		$number = $number % $divider;
		$divider = (int) ($divider / 10);
		$pos++;
	}

	// คืนค่าผลลัพธ์เป็นสตริง
	return $ret;
}

/**
 * ทำการ unserialize สตริงที่อาจมีปัญหา single quote
 *
 * @param string $str สตริงที่ต้องการ unserialize
 * @return mixed ค่าที่ได้จากการ unserialize
 */
function fn_un_serialize(string $str): mixed
{
	// แก้ไขสตริงที่อาจมีปัญหา single quote โดยใช้ regular expression
	$str = preg_replace_callback(
		'!s:(\d+):"(.*?)";!',
		function (array $match): string {
			// ตรวจสอบว่าความยาวของสตริงที่ระบุใน s:(\d+) ตรงกับความยาวจริงหรือไม่
			if ((int) $match[1] === strlen($match[2])) {
				// ถ้าตรง คืนค่าสตริงเดิม (ไม่มีการแก้ไข)
				return $match[0];
			}

			// ถ้าไม่ตรง แก้ไขความยาวให้ถูกต้องและคืนค่าสตริงที่แก้ไขแล้ว
			return 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
		},
		$str
	);

	// ทำการ unserialize สตริงที่ผ่านการแก้ไขแล้ว และคืนค่าผลลัพธ์
	return unserialize($str);
}

/**
 * ตรวจสอบว่าไฟล์ที่ระบุเป็นไฟล์รูปภาพหรือไม่
 *
 * @param string $path path ของไฟล์ที่ต้องการตรวจสอบ
 * @return bool true หากไฟล์เป็นรูปภาพ, false หากไม่ใช่
 */
function is_image(string $path): bool
{
	// ตรวจสอบว่าไฟล์ที่ระบุมีอยู่จริงหรือไม่
	if (!file_exists($path)) {
		// ถ้าไฟล์ไม่มีอยู่จริง คืนค่า false
		return false;
	}

	// ใช้ @ เพื่อปิด error suppression หาก getimagesize ล้มเหลว
	// getimagesize จะคืนค่า false หากไฟล์ไม่ใช่รูปภาพ
	$image_info = @getimagesize($path);

	// ตรวจสอบว่า getimagesize คืนค่า false หรือไม่
	if ($image_info === false) {
		// ถ้า getimagesize คืนค่า false แสดงว่าเป็นไฟล์ที่ไม่ใช่รูปภาพ
		return false;
	}

	// ดึงประเภทของรูปภาพจากผลลัพธ์ของ getimagesize
	$image_type = $image_info[2];

	// กำหนดประเภทของรูปภาพที่อนุญาต
	$allowed_types = [
		IMAGETYPE_GIF,
		IMAGETYPE_JPEG,
		IMAGETYPE_PNG,
		IMAGETYPE_BMP,
		IMAGETYPE_WEBP, // เพิ่ม WEBP
		IMAGETYPE_AVIF, // เพิ่ม AVIF
	];

	// ตรวจสอบว่าประเภทของรูปภาพอยู่ในรายการประเภทที่อนุญาตหรือไม่ และคืนค่าผลลัพธ์
	return in_array($image_type, $allowed_types, true);
}

/**
 * สร้างและอัปเดตค่า index (idx) ในตารางฐานข้อมูล
 *
 * @param int $record_id ID ของ record ที่ต้องการอัปเดต
 * @param string $table ชื่อตารางฐานข้อมูล
 * @param string $field_id ชื่อฟิลด์ ID ในตาราง
 * @param string $field_idx ชื่อฟิลด์ index (idx) ในตาราง
 * @param string $slug ส่วนนำหน้าของ index (optional)
 * @param int $length ความยาวของส่วนตัวเลขใน index (optional, default: 5)
 * @param string|null $ym ปี พ.ศ. สองหลักสุดท้าย (optional, default: ปีปัจจุบัน)
 * @return void
 */
function fn_gen_idx(int $record_id, string $table, string $field_id, string $field_idx, string $slug = '', int $length = 5, ?string $ym = null): void
{
	// กำหนดค่า $ym โดยใช้ null coalescing operator ถ้า $ym เป็น null จะใช้ปี พ.ศ. สองหลักสุดท้าย
	$ym = $ym ?? substr((date('Y') + 543), 2, 2);

	// เพิ่มค่า $ym ต่อท้าย $slug
	$slug .= $ym;

	// สร้างสตริงเริ่มต้นด้วยเลข 1 และเติมเลข 0 ทางซ้ายจนครบตามความยาว $length
	$start_number1 = str_pad('1', $length, '0', STR_PAD_LEFT);

	// สร้างคำสั่ง SQL สำหรับอัปเดตค่า index (idx) ในตาราง {$table}
	// ใช้ COALESCE เพื่อเลือกค่าจาก subquery หรือค่าเริ่มต้น '{$slug}{$start_number1}'
	// subquery เลือกค่า MAX({$field_idx}) จากตาราง {$table} ที่มี {$field_idx} LIKE '{$slug}%'
	$sql = "
      UPDATE {$table}
      SET {$field_idx} = COALESCE(
         (
               SELECT CONCAT(
                  '{$slug}',
                  LPAD(
                     (RIGHT(max_idx, {$length}) + 1),
                     {$length},
                     '0'
                  )
               )
               FROM (
                  SELECT MAX({$field_idx}) AS max_idx
                  FROM {$table}
                  WHERE {$field_idx} LIKE '{$slug}%'
               ) AS subquery
         ),
         '{$slug}{$start_number1}'
      )
      WHERE {$field_id} = {$record_id}
   ";

	// รันคำสั่ง SQL (เลือกใช้ตาม framework ที่ใช้งาน)
	// DB::statement($sql); // Laravel
	// global $wpdb; $wpdb->query($sql); // WordPress

	// ในกรณีที่ไม่ได้ใช้ framework สามารถใช้ PDO หรือ mysqli ในการรันคำสั่ง SQL
	// ตัวอย่างการใช้ PDO
	// $pdo->exec($sql);
}

/**
 * แทนที่ URL ในข้อความด้วยแท็ก <a>
 *
 * @param string $text ข้อความที่ต้องการแทนที่ URL
 * @return string ข้อความที่ผ่านการแทนที่ URL แล้ว
 */
function replace_hyperlink_with_anchor(string $text): string
{
	// กำหนดรูปแบบ regular expression เพื่อจับคู่ URL
	// (https?:\/\/): จับคู่ http:// หรือ https://
	// [^\s]+: จับคู่อักขระที่ไม่ใช่ช่องว่างตั้งแต่ 1 ตัวขึ้นไป
	$pattern = '/(https?:\/\/[^\s]+)/';

	// กำหนดรูปแบบการแทนที่ โดยห่อ URL ด้วยแท็ก <a>
	// href="$1": กำหนด URL เป็นค่าของ attribute href
	// target="_blank": เปิดลิงก์ในแท็บใหม่
	// $1: แทนที่ด้วย URL ที่ถูกจับคู่
	$replacement = '<a href="$1" target="_blank">$1</a>';

	// ทำการแทนที่ URL ในข้อความด้วยแท็ก <a>
	$result = preg_replace($pattern, $replacement, $text);

	// คืนค่าข้อความที่ผ่านการแทนที่แล้ว
	return $result;
}

/**
 * ตรวจสอบความถูกต้องของเลขประจำตัวประชาชนไทย 13 หลัก
 *
 * ฟังก์ชันนี้ใช้อัลกอริทึมอย่างเป็นทางการในการตรวจสอบความถูกต้องของเลขประจำตัวประชาชนไทย
 * โดยจะตรวจสอบความยาวของรหัส และคำนวณ "เลขตรวจสอบ (check digit)"
 * จาก 12 หลักแรกเพื่อนำไปเปรียบเทียบกับหลักที่ 13 ที่ให้มา
 *
 * @param string|int $pid เลขประจำตัวประชาชนไทย 13 หลัก
 * @return bool คืนค่า true หากรหัสถูกต้อง และ false หากรหัสไม่ถูกต้อง
 */
function check_thai_card_id($pid)
{
	// 1. แปลงข้อมูลนำเข้าให้เป็นสตริงและตรวจสอบว่ามีความยาว 13 หลักหรือไม่
	$pid = (string)$pid;
	if (strlen($pid) != 13) {
		return false;
	}

	// 2. คำนวณผลรวมแบบถ่วงน้ำหนักของเลข 12 หลักแรก
	// โดยหลักแรกจะคูณด้วย 13, หลักที่สองคูณด้วย 12, และทำเช่นนี้ไปเรื่อยๆ
	$sum = 0;
	for ($i = 0; $i < 12; ++$i) {
		$sum += (int)($pid[$i]) * (13 - $i);
	}

	// 3. คำนวณเลขตรวจสอบ (check digit) จากผลรวมที่ได้
	// สูตรคือ: (11 - (ผลรวม % 11)) % 10
	$check_digit_calculated = (11 - ($sum % 11)) % 10;

	// 4. นำหลักสุดท้าย (เลขตรวจสอบตัวจริง) จากสตริงที่ป้อนเข้ามา
	$check_digit_original = (int)($pid[12]);

	// 5. เปรียบเทียบเลขตรวจสอบที่คำนวณได้กับเลขตรวจสอบตัวจริง
	// ถ้าตรงกัน แสดงว่ารหัสถูกต้อง
	if ($check_digit_calculated == $check_digit_original) {
		return true;
	} else {
		return false;
	}
}
