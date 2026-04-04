<?php

function fn_pre($arr)
{
	$str = '<pre>';
	$str .= print_r($arr, true);
	$str .= '</pre>';
	return $str;
}

/**
 * สร้างสตริงสุ่มตามความยาวและประเภทที่กำหนด
 *
 * @param int $length ความยาวของสตริงที่ต้องการสร้าง
 * @param array $type (optional) ประเภทของตัวอักษรที่ต้องการใช้ (1 = ตัวเลข, 2 = ตัวพิมพ์เล็ก, 3 = ตัวพิมพ์ใหญ่) (ค่าเริ่มต้น: [1, 2, 3])
 * @return string สตริงสุ่มที่สร้างขึ้น
 */
function fnGenerateRandomString(int $length, array $types = [1, 2, 3], string $specialChars = '!@#$%^&*()-_=+[]{}'): string
{
	if ($length < 1) {
		throw new InvalidArgumentException('$length must be greater than 0.');
	}

	$charSets = [
		1 => '0123456789',
		2 => 'abcdefghjkmnpqrstuvwxyz',
		3 => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
		4 => $specialChars,
	];

	$types = array_values(array_unique($types));

	if ($types === []) {
		throw new InvalidArgumentException('$types cannot be empty.');
	}

	if ($length < count($types)) {
		throw new InvalidArgumentException(
			'$length must be at least the number of selected types.'
		);
	}

	$allChars = '';
	$resultChars = [];

	foreach ($types as $type) {
		if (!array_key_exists($type, $charSets)) {
			throw new InvalidArgumentException("Invalid type: {$type}");
		}

		$set = $charSets[$type];

		if ($set === '') {
			throw new InvalidArgumentException("Character set for type {$type} is empty.");
		}

		// บังคับให้มีอย่างน้อย 1 ตัวจากแต่ละกลุ่มที่เลือก
		$resultChars[] = $set[random_int(0, strlen($set) - 1)];

		// รวมทุกชุดไว้สำหรับสุ่มตัวที่เหลือ
		$allChars .= $set;
	}

	$allCharsLength = strlen($allChars);

	// เติมตัวที่เหลือ
	while (count($resultChars) < $length) {
		$resultChars[] = $allChars[random_int(0, $allCharsLength - 1)];
	}

	// สลับตำแหน่งแบบ Fisher-Yates โดยใช้ random_int
	for ($i = count($resultChars) - 1; $i > 0; $i--) {
		$j = random_int(0, $i);
		[$resultChars[$i], $resultChars[$j]] = [$resultChars[$j], $resultChars[$i]];
	}

	return implode('', $resultChars);
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

/**
 * เข้ารหัสข้อความแบบปลอดภัย โดยใช้ AES-256-GCM
 *
 * จุดเด่น:
 * - ใช้ IV แบบสุ่มทุกครั้ง
 * - ใช้ salt แบบสุ่มทุกครั้ง
 * - ใช้ HKDF แปลง secret key ให้เหมาะกับ AES-256
 * - มี authentication tag ป้องกันข้อมูลถูกแก้ไข
 *
 * @param string $text ข้อความที่ต้องการเข้ารหัส
 * @param string $secret_key คีย์ลับสำหรับเข้ารหัส/ถอดรหัส
 * @return string ข้อมูลที่เข้ารหัสแล้วในรูปแบบ Base64URL
 */
function TheKwakEncrypt(string $text, string $secret_key = 'mynameispond'): string
{
	$cipher = 'aes-256-gcm';
	$ivLength = openssl_cipher_iv_length($cipher);

	if ($ivLength === false) {
		throw new RuntimeException('Unsupported cipher.');
	}

	$salt = random_bytes(16);
	$iv = random_bytes($ivLength);

	$key = hash_hkdf('sha256', $secret_key, 32, 'TheKwakEncrypt', $salt);

	$tag = '';
	$cipherText = openssl_encrypt(
		$text,
		$cipher,
		$key,
		OPENSSL_RAW_DATA,
		$iv,
		$tag,
		'',
		16
	);

	if ($cipherText === false) {
		throw new RuntimeException('Encryption failed.');
	}

	// payload = version(1 byte) + salt(16) + iv + tag(16) + ciphertext
	$payload = chr(1) . $salt . $iv . $tag . $cipherText;

	return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
}

/**
 * ถอดรหัสข้อความที่เข้ารหัสด้วย TheKwakEncrypt()
 *
 * @param string $encryptedText ข้อความที่ถูกเข้ารหัสในรูปแบบ Base64URL
 * @param string $secret_key คีย์ลับสำหรับถอดรหัส
 * @return string|false คืนข้อความต้นฉบับ หรือ false หากถอดรหัสไม่สำเร็จ
 */
function TheKwakDecrypt(string $encryptedText, string $secret_key = 'mynameispond'): string|false
{
	$cipher = 'aes-256-gcm';
	$ivLength = openssl_cipher_iv_length($cipher);

	if ($ivLength === false) {
		return false;
	}

	$normalized = strtr($encryptedText, '-_', '+/');
	$padding = strlen($normalized) % 4;
	if ($padding > 0) {
		$normalized .= str_repeat('=', 4 - $padding);
	}

	$decoded = base64_decode($normalized, true);
	if ($decoded === false) {
		return false;
	}

	$versionLength = 1;
	$saltLength = 16;
	$tagLength = 16;
	$minLength = $versionLength + $saltLength + $ivLength + $tagLength + 1;

	if (strlen($decoded) < $minLength) {
		return false;
	}

	$offset = 0;

	$version = ord(substr($decoded, $offset, $versionLength));
	$offset += $versionLength;

	if ($version !== 1) {
		return false;
	}

	$salt = substr($decoded, $offset, $saltLength);
	$offset += $saltLength;

	$iv = substr($decoded, $offset, $ivLength);
	$offset += $ivLength;

	$tag = substr($decoded, $offset, $tagLength);
	$offset += $tagLength;

	$cipherText = substr($decoded, $offset);

	$key = hash_hkdf('sha256', $secret_key, 32, 'TheKwakEncrypt', $salt);

	return openssl_decrypt(
		$cipherText,
		$cipher,
		$key,
		OPENSSL_RAW_DATA,
		$iv,
		$tag
	);
}

/**
 * บีบอัด string แล้วคืนค่าเป็น Base64URL string
 *
 * @param string $string ข้อความที่ต้องการบีบอัด
 * @param string $lib ไลบรารีที่ใช้: g = gzcompress, b = bzcompress
 * @param int $level ระดับการบีบอัด
 * @return string
 */
function fnCompressString(string $string, string $lib = 'g', int $level = 9): string
{
	$lib = strtolower($lib);

	if ($lib === 'b') {
		if (!function_exists('bzcompress')) {
			throw new RuntimeException('bzcompress() is not available on this server.');
		}

		if ($level < 1 || $level > 9) {
			throw new InvalidArgumentException('Bzip2 level must be between 1 and 9.');
		}

		$compressed = bzcompress($string, $level);

		if (!is_string($compressed)) {
			throw new RuntimeException('Bzip2 compression failed. Error code: ' . $compressed);
		}

		$header = 'B1';
	} else {
		if (!function_exists('gzcompress')) {
			throw new RuntimeException('gzcompress() is not available on this server.');
		}

		if ($level < -1 || $level > 9) {
			throw new InvalidArgumentException('Gzip level must be between -1 and 9.');
		}

		$compressed = gzcompress($string, $level);

		if ($compressed === false) {
			throw new RuntimeException('Gzip compression failed.');
		}

		$header = 'G1';
	}

	$payload = $header . $compressed;

	// Base64URL
	return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
}

/**
 * คลายข้อมูลที่ได้จาก fnCompressString()
 *
 * @param string $string Base64URL string
 * @return string|false
 */
function fnDecompressString(string $string): string|false
{
	$normalized = strtr($string, '-_', '+/');
	$padding = strlen($normalized) % 4;

	if ($padding > 0) {
		$normalized .= str_repeat('=', 4 - $padding);
	}

	$decoded = base64_decode($normalized, true);

	if ($decoded === false || strlen($decoded) < 3) {
		return false;
	}

	$header = substr($decoded, 0, 2);
	$data = substr($decoded, 2);

	if ($header === 'G1') {
		if (!function_exists('gzuncompress')) {
			return false;
		}

		return gzuncompress($data);
	}

	if ($header === 'B1') {
		if (!function_exists('bzdecompress')) {
			return false;
		}

		$result = bzdecompress($data);

		return is_string($result) ? $result : false;
	}

	return false;
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

/**
 * โหลดไฟล์ PHP ทั้งหมดจากพาธไดเรกทอรีที่กำหนด
 * @param string $directory_path พาธของโฟลเดอร์ที่ต้องการโหลด
 * @return void
 */
function include_all_php_in_dir(string $directory_path): void
{
	// ทำให้แน่ใจว่าพาธมี '/' อยู่ท้าย เพื่อการต่อสตริงที่ถูกต้อง
	if (substr($directory_path, -1) !== '/') {
		$directory_path .= '/';
	}

	// ค้นหาไฟล์ .php ทั้งหมดในไดเรกทอรีนั้น
	$files = glob($directory_path . '*.php');

	// วนซ้ำและโหลดไฟล์แต่ละไฟล์
	foreach ($files as $file) {
		// ใช้ include_once เพื่อป้องกันการโหลดซ้ำ
		include_once $file;
	}
}

if (!function_exists('esc_html')) {
	/**
	 * Escape ข้อความสำหรับแสดงใน HTML ปกติ
	 *
	 * @param string|null $text
	 * @return string
	 */
	function esc_html(?string $text): string
	{
		return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}

if (!function_exists('esc_attr')) {
	/**
	 * Escape ข้อความสำหรับใช้ใน HTML attribute
	 *
	 * @param string|null $text
	 * @return string
	 */
	function esc_attr(?string $text): string
	{
		return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}

if (!function_exists('esc_textarea')) {
	/**
	 * Escape ข้อความสำหรับใช้ใน textarea
	 *
	 * @param string|null $text
	 * @return string
	 */
	function esc_textarea(?string $text): string
	{
		return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}

if (!function_exists('wp_allowed_protocols')) {
	/**
	 * รายการ protocol ที่อนุญาตแบบใกล้เคียง WordPress
	 *
	 * @return array
	 */
	function wp_allowed_protocols(): array
	{
		return [
			'http',
			'https',
			'ftp',
			'ftps',
			'mailto',
			'news',
			'irc',
			'gopher',
			'nntp',
			'feed',
			'telnet',
			'tel',
		];
	}
}

if (!function_exists('_kwak_deep_replace')) {
	/**
	 * ลบข้อความซ้ำๆ จนกว่าจะไม่เหลือ
	 *
	 * @param array $search
	 * @param string $subject
	 * @return string
	 */
	function _kwak_deep_replace(array $search, string $subject): string
	{
		do {
			$before = $subject;
			$subject = str_ireplace($search, '', $subject);
		} while ($before !== $subject);

		return $subject;
	}
}

if (!function_exists('_kwak_is_relative_url')) {
	/**
	 * ตรวจว่าเป็น relative URL หรือไม่
	 *
	 * @param string $url
	 * @return bool
	 */
	function _kwak_is_relative_url(string $url): bool
	{
		return $url !== '' && in_array($url[0], ['/', '#', '?'], true);
	}
}

if (!function_exists('_kwak_get_url_scheme')) {
	/**
	 * ดึง scheme จาก URL ถ้ามี
	 *
	 * @param string $url
	 * @return string|null
	 */
	function _kwak_get_url_scheme(string $url): ?string
	{
		if (preg_match('/^([a-z][a-z0-9+\-.]*):/i', $url, $matches)) {
			return strtolower($matches[1]);
		}

		return null;
	}
}

if (!function_exists('_kwak_sanitize_url')) {
	/**
	 * ทำความสะอาด URL แบบไม่แปลง entity สำหรับแสดงผล
	 *
	 * @param string|null $url
	 * @param array|null $protocols
	 * @return string
	 */
	function _kwak_sanitize_url(?string $url, ?array $protocols = null): string
	{
		$url = (string) $url;

		if ($url === '') {
			return '';
		}

		$url = ltrim($url);
		$url = str_replace(' ', '%20', $url);

		// ลบอักขระที่ไม่เหมาะกับ URL ออก
		$url = preg_replace("|[^a-z0-9-~+_.?#=!&;,/:%@$\|*'()\[\]\x80-\xff]|i", '', $url);
		if ($url === null || $url === '') {
			return '';
		}

		// กัน CRLF injection สำหรับ URL ทั่วไป ยกเว้น mailto:
		if (stripos($url, 'mailto:') !== 0) {
			$url = _kwak_deep_replace(['%0d', '%0a', '%0D', '%0A', "\r", "\n"], $url);
		}

		// แก้ :// ที่เพี้ยน
		$url = str_replace(';//', '://', $url);

		// ถ้าไม่ใช่ relative URL, ไม่ใช่ protocol-relative URL, ไม่มี scheme, และไม่ใช่ไฟล์ php
		// ให้เติม http:// หรือ protocol ตัวแรกที่กำหนด
		if (
			!str_contains($url, ':')
			&& !_kwak_is_relative_url($url)
			&& !str_starts_with($url, '//')
			&& !preg_match('/^[a-z0-9-]+?\.php/i', $url)
		) {
			$allowed = is_array($protocols) && $protocols !== [] ? array_values($protocols) : wp_allowed_protocols();
			$defaultScheme = (isset($allowed[0]) && strtolower((string) $allowed[0]) === 'https') ? 'https://' : 'http://';
			$url = $defaultScheme . $url;
		}

		// relative URL และ protocol-relative URL อนุญาต
		if (_kwak_is_relative_url($url) || str_starts_with($url, '//')) {
			return $url;
		}

		$protocols = $protocols ?: wp_allowed_protocols();
		$protocols = array_map(static fn($item) => strtolower((string) $item), $protocols);

		$scheme = _kwak_get_url_scheme($url);

		if ($scheme !== null && !in_array($scheme, $protocols, true)) {
			return '';
		}

		return $url;
	}
}

if (!function_exists('esc_url')) {
	/**
	 * Escape URL สำหรับแสดงผลใน HTML
	 *
	 * @param string|null $url
	 * @param array|null $protocols
	 * @return string
	 */
	function esc_url(?string $url, ?array $protocols = null): string
	{
		$url = _kwak_sanitize_url($url, $protocols);

		if ($url === '') {
			return '';
		}

		// แปลงให้เหมาะกับการแสดงผลใน HTML
		$url = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$url = str_replace('&amp;', '&#038;', $url);

		return $url;
	}
}

if (!function_exists('esc_url_raw')) {
	/**
	 * ทำความสะอาด URL สำหรับเก็บลง DB หรือใช้ redirect
	 *
	 * @param string|null $url
	 * @param array|null $protocols
	 * @return string
	 */
	function esc_url_raw(?string $url, ?array $protocols = null): string
	{
		return _kwak_sanitize_url($url, $protocols);
	}
}

function str_pagination($maxpage, $currentpage, $show_page = 5, $page_var = 'npage', $baseUrl = '')
{
	$maxpage     = max(0, (int) $maxpage);
	$show_page   = max(1, (int) $show_page);
	$currentpage = (int) $currentpage;

	if ($maxpage <= 1) {
		return '';
	}

	if ($currentpage < 1) {
		$currentpage = 1;
	} elseif ($currentpage > $maxpage) {
		$currentpage = $maxpage;
	}

	$uqrget = fnGetAction($page_var);

	if ($baseUrl === '') {
		if (defined('APP_URL') && APP_URL !== '') {
			$baseUrl = APP_URL;
		} else {
			$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
				|| ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);

			$scheme = $isHttps ? 'https://' : 'http://';
			$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
			$path   = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

			$baseUrl = $scheme . $host . $path;
		}
	}

	$separator     = (strpos($baseUrl, '?') === false) ? '?' : '&';
	$paginationUrl = $baseUrl . $separator . (empty($uqrget) ? '' : $uqrget . '&') . $page_var . '=';

	$span_page     = (int) floor($show_page / 2);
	$start_page_at = max(1, $currentpage - $span_page);
	$max_page_end  = min($maxpage, $start_page_at + $show_page - 1);
	$start_page_at = max(1, $max_page_end - $show_page + 1);

	$buildItem = function ($label, $page = null, $active = false, $disabled = false, $allowHtmlLabel = false) use ($paginationUrl) {
		$classes = ['page-item'];

		if ($active) {
			$classes[] = 'active';
		}

		if ($disabled) {
			$classes[] = 'disabled';
		}

		$classAttr = esc_attr(implode(' ', $classes));
		$labelHtml = $allowHtmlLabel ? (string) $label : esc_html((string) $label);

		if ($disabled || $active || $page === null) {
			return '<li class="' . $classAttr . '"><span class="page-link">' . $labelHtml . '</span></li>';
		}

		$url = esc_url($paginationUrl . (int) $page);

		return '<li class="' . $classAttr . '"><a class="page-link" href="' . $url . '">' . $labelHtml . '</a></li>';
	};

	$str  = "<nav aria-label='Pagination'><ul class='pagination justify-content-end mb-0'>";
	$str .= $buildItem("<span aria-hidden='true'>&laquo;</span>", 1, false, $currentpage <= 1, true);
	$str .= $buildItem('Previous', $currentpage - 1, false, $currentpage <= 1);

	for ($i = $start_page_at; $i <= $max_page_end; $i++) {
		$str .= $buildItem((string) $i, $i, $currentpage === $i, false);
	}

	$str .= $buildItem('Next', $currentpage + 1, false, $currentpage >= $maxpage);
	$str .= $buildItem("<span aria-hidden='true'>&raquo;</span>", $maxpage, false, $currentpage >= $maxpage, true);
	$str .= '</ul></nav>';

	return $str;
}
