<?php

/**
 * Underscore Php Framework
 * ------------------------
 *  
 * @author Thomas Portelange <thomas@lekoala.be>
 * @version 0.001
 * @licence THE BEER-WARE LICENSE rev.42 (see licence.txt)
 */

/**
 * Main class
 */
class _ {
	/**
	 * Variables available in all templates
	 * @var array
	 */
	static $template_vars = array();

	/**
	 * Prefix appended before a template in render method
	 * @var string
	 */
	static $template_prefix = 'templates/';

	/**
	 * Extension used in render method if no extension is set
	 * @var string
	 */
	static $template_default_extension = 'phtml';

	/**
	 * Callback at the end of the render method ($ouput, $vars)
	 * @var function
	 */
	static $template_filter;

	/**
	 * Object that should provide infos through stats() method
	 * @var array
	 */
	static $tracked_objects = array();

	/**
	 * Array to store initial values for performance tracking
	 * @var array
	 */
	static $tracked_stats = array();

	/**
	 * Env (dev, test or prod)
	 * @var string
	 */
	static $env = 'prod';

	/**
	 * Dev servers used to set env
	 * @var array
	 */
	static $dev_servers = array('127.0.0.1', '::1');

	/**
	 * Dev ip ranges used to set env
	 * @var array
	 */
	static $dev_ip_ranges = array('192.168.');

	/**
	 * Test servers used to set env
	 * @var array
	 */
	static $test_servers = array();

	/**
	 * Test ip ranges used to set env
	 * @var array
	 */
	static $test_ip_ranges = array();

	/**
	 * Is logging enabled for log method
	 * @var bool
	 */
	static $log_enabled = true;

	/**
	 * File used as a log
	 * @var string
	 */
	static $log_file;
	
	/**
	 * Provide a monolog instance to be used in the ::log() method
	 * @link https://github.com/Seldaek/monolog
	 * @var Monolog\Logger
	 */
	static $log ;
	
	/**
	 * PDO instance to use
	 * @var PDO
	 */
	static $pdo;

	/**
	 * Static list of available mimeTypes
	 * @var array
	 */
	static $mime_types = array(
		"323" => "text/h323",
		"acx" => "application/internet-property-stream",
		"ai" => "application/postscript",
		"aif" => "audio/x-aiff",
		"aifc" => "audio/x-aiff",
		"aiff" => "audio/x-aiff",
		"asf" => "video/x-ms-asf",
		"asr" => "video/x-ms-asf",
		"asx" => "video/x-ms-asf",
		"au" => "audio/basic",
		"avi" => "video/x-msvideo",
		"axs" => "application/olescript",
		"bas" => "text/plain",
		"bcpio" => "application/x-bcpio",
		"bin" => "application/octet-stream",
		"bmp" => "image/bmp",
		"c" => "text/plain",
		"cat" => "application/vnd.ms-pkiseccat",
		"cdf" => "application/x-cdf",
		"cer" => "application/x-x509-ca-cert",
		"class" => "application/octet-stream",
		"clp" => "application/x-msclip",
		"cmx" => "image/x-cmx",
		"cod" => "image/cis-cod",
		"cpio" => "application/x-cpio",
		"crd" => "application/x-mscardfile",
		"crl" => "application/pkix-crl",
		"crt" => "application/x-x509-ca-cert",
		"csh" => "application/x-csh",
		"css" => "text/css",
		"dcr" => "application/x-director",
		"der" => "application/x-x509-ca-cert",
		"dir" => "application/x-director",
		"dll" => "application/x-msdownload",
		"dms" => "application/octet-stream",
		"doc" => "application/msword",
		"dot" => "application/msword",
		"dvi" => "application/x-dvi",
		"dxr" => "application/x-director",
		"eps" => "application/postscript",
		"etx" => "text/x-setext",
		"evy" => "application/envoy",
		"exe" => "application/octet-stream",
		"fif" => "application/fractals",
		"flr" => "x-world/x-vrml",
		"gif" => "image/gif",
		"gtar" => "application/x-gtar",
		"gz" => "application/x-gzip",
		"h" => "text/plain",
		"hdf" => "application/x-hdf",
		"hlp" => "application/winhlp",
		"hqx" => "application/mac-binhex40",
		"hta" => "application/hta",
		"htc" => "text/x-component",
		"htm" => "text/html",
		"html" => "text/html",
		"htt" => "text/webviewhtml",
		"ico" => "image/x-icon",
		"ief" => "image/ief",
		"iii" => "application/x-iphone",
		"ins" => "application/x-internet-signup",
		"isp" => "application/x-internet-signup",
		"jfif" => "image/pipeg",
		"jpe" => "image/jpeg",
		"jpeg" => "image/jpeg",
		"jpg" => "image/jpeg",
		"js" => "application/x-javascript",
		"latex" => "application/x-latex",
		"lha" => "application/octet-stream",
		"lsf" => "video/x-la-asf",
		"lsx" => "video/x-la-asf",
		"lzh" => "application/octet-stream",
		"m13" => "application/x-msmediaview",
		"m14" => "application/x-msmediaview",
		"m3u" => "audio/x-mpegurl",
		"man" => "application/x-troff-man",
		"mdb" => "application/x-msaccess",
		"me" => "application/x-troff-me",
		"mht" => "message/rfc822",
		"mhtml" => "message/rfc822",
		"mid" => "audio/mid",
		"mny" => "application/x-msmoney",
		"mov" => "video/quicktime",
		"movie" => "video/x-sgi-movie",
		"mp2" => "video/mpeg",
		"mp3" => "audio/mpeg",
		"mpa" => "video/mpeg",
		"mpe" => "video/mpeg",
		"mpeg" => "video/mpeg",
		"mpg" => "video/mpeg",
		"mpp" => "application/vnd.ms-project",
		"mpv2" => "video/mpeg",
		"ms" => "application/x-troff-ms",
		"mvb" => "application/x-msmediaview",
		"nws" => "message/rfc822",
		"oda" => "application/oda",
		"p10" => "application/pkcs10",
		"p12" => "application/x-pkcs12",
		"p7b" => "application/x-pkcs7-certificates",
		"p7c" => "application/x-pkcs7-mime",
		"p7m" => "application/x-pkcs7-mime",
		"p7r" => "application/x-pkcs7-certreqresp",
		"p7s" => "application/x-pkcs7-signature",
		"pbm" => "image/x-portable-bitmap",
		"pdf" => "application/pdf",
		"pfx" => "application/x-pkcs12",
		"pgm" => "image/x-portable-graymap",
		"pko" => "application/ynd.ms-pkipko",
		"pma" => "application/x-perfmon",
		"pmc" => "application/x-perfmon",
		"pml" => "application/x-perfmon",
		"pmr" => "application/x-perfmon",
		"pmw" => "application/x-perfmon",
		"png" => 'image/png',
		"pnm" => "image/x-portable-anymap",
		"pot" => "application/vnd.ms-powerpoint",
		"ppm" => "image/x-portable-pixmap",
		"pps" => "application/vnd.ms-powerpoint",
		"ppt" => "application/vnd.ms-powerpoint",
		"prf" => "application/pics-rules",
		"ps" => "application/postscript",
		"pub" => "application/x-mspublisher",
		"qt" => "video/quicktime",
		"ra" => "audio/x-pn-realaudio",
		"ram" => "audio/x-pn-realaudio",
		"ras" => "image/x-cmu-raster",
		"rgb" => "image/x-rgb",
		"rmi" => "audio/mid",
		"roff" => "application/x-troff",
		"rtf" => "application/rtf",
		"rtx" => "text/richtext",
		"scd" => "application/x-msschedule",
		"sct" => "text/scriptlet",
		"setpay" => "application/set-payment-initiation",
		"setreg" => "application/set-registration-initiation",
		"sh" => "application/x-sh",
		"shar" => "application/x-shar",
		"sit" => "application/x-stuffit",
		"snd" => "audio/basic",
		"spc" => "application/x-pkcs7-certificates",
		"spl" => "application/futuresplash",
		"src" => "application/x-wais-source",
		"sst" => "application/vnd.ms-pkicertstore",
		"stl" => "application/vnd.ms-pkistl",
		"stm" => "text/html",
		"svg" => "image/svg+xml",
		"sv4cpio" => "application/x-sv4cpio",
		"sv4crc" => "application/x-sv4crc",
		"t" => "application/x-troff",
		"tar" => "application/x-tar",
		"tcl" => "application/x-tcl",
		"tex" => "application/x-tex",
		"texi" => "application/x-texinfo",
		"texinfo" => "application/x-texinfo",
		"tgz" => "application/x-compressed",
		"tif" => "image/tiff",
		"tiff" => "image/tiff",
		"tr" => "application/x-troff",
		"trm" => "application/x-msterminal",
		"tsv" => "text/tab-separated-values",
		"txt" => "text/plain",
		"uls" => "text/iuls",
		"ustar" => "application/x-ustar",
		"vcf" => "text/x-vcard",
		"vrml" => "x-world/x-vrml",
		"wav" => "audio/x-wav",
		"wcm" => "application/vnd.ms-works",
		"wdb" => "application/vnd.ms-works",
		"wks" => "application/vnd.ms-works",
		"wmf" => "application/x-msmetafile",
		"wps" => "application/vnd.ms-works",
		"wri" => "application/x-mswrite",
		"wrl" => "x-world/x-vrml",
		"wrz" => "x-world/x-vrml",
		"xaf" => "x-world/x-vrml",
		"xbm" => "image/x-xbitmap",
		"xla" => "application/vnd.ms-excel",
		"xlc" => "application/vnd.ms-excel",
		"xlm" => "application/vnd.ms-excel",
		"xls" => "application/vnd.ms-excel",
		"xlt" => "application/vnd.ms-excel",
		"xlw" => "application/vnd.ms-excel",
		"xof" => "x-world/x-vrml",
		"xpm" => "image/x-xpixmap",
		"xwd" => "image/x-xwindowdump",
		"z" => "application/x-compress",
		"zip" => "application/zip");

	/**
	 * Used as static, cannot instantiate
	 */
	protected function __construct() {
		
	}

	/**
	 * Examine dev et test settings and set the env
	 */
	public static function set_env_from_config() {
		$ip = self::real_ip();
		if (in_array($ip, self::$dev_servers)) {
			self::$env = 'dev';
		}
		foreach (self::$dev_ip_ranges as $ip_range) {
			if (strpos($ip, $ip_range) === 0) {
				self::$env = 'dev';
			}
		}
		if (in_array($ip, self::$test_servers)) {
			self::$env = 'test';
		}
		foreach (self::$test_ip_ranges as $ip_range) {
			if (strpos($ip, $ip_range) === 0) {
				self::$env = 'test';
			}
		}
	}

	/**
	 * Render a view file or multiple view file (eg : inner_view, layout)
	 * When rendering multiple view file, the embedded view is always placed
	 * in $content variable
	 * 
	 * @param string|array $filename
	 * @param array $vars (optional)
	 * @return string
	 */
	static function render($filename, $vars = array()) {
		if (is_array($filename)) {
			foreach ($filename as $file) {
				$content = self::render($file, $vars);
				$vars['content'] = $content;
			}
			return $content;
		}

		$filename = self::$template_prefix . $filename;
		$ext = self::get_file_extension($filename);
		if (empty($ext)) {
			$filename .= '.' . self::$template_default_extension;
		}

		if (!is_file($filename)) {
			throw new Exception($filename . ' is not a valid filename');
		}

		$vars = array_merge($vars, self::$template_vars);
		extract($vars, EXTR_REFS); // Extract variables as references
		ob_start();
		include($filename);
		$output = ob_get_clean();

		if (self::$template_filter) {
			$filter = self::$template_filter;
			$output = $filter($output, $vars);
		}

		return $output;
	}

	/**
	 * Helper to configure php with the right options
	 * 
	 * @param string $timezone
	 * @param boolean $strict
	 * @param boolean $utf8 
	 */
	static function configure($timezone = 'Europe/Brussels', $strict = true, $utf8 = true) {
		date_default_timezone_set($timezone);
		if ($strict) {
			error_reporting(E_ALL | E_STRICT);
			ini_set('display_errors', 1);
		} else {
			error_reporting(0);
			ini_set('display_errors', 0);
		}
		if ($utf8) {
			mb_internal_encoding('UTF-8');
			mb_http_output('UTF-8');
			mb_http_input('UTF-8');
			mb_language('uni');
			mb_regex_encoding('UTF-8');
			ob_start('mb_output_handler');
			//don't forget to send headers and db charset too
		} else {
			mb_internal_encoding('ISO-8859-1');
			mb_http_output('ISO-8859-1');
			mb_http_input('ISO-8859-1');
			mb_language('en');
			mb_regex_encoding('ISO-8859-1');
		}
		ob_start('mb_output_handler');
	}
	
	/**
	 * Simply log to a file or use the Monolog instance provided
	 * 
	 * @param string $message
	 * @param string $level (optional)
	 * @param array $context (optional)
	 * @return bool 
	 */
	static function log($message, $level = 'info', $context = array()) {
		if (!self::$log_enabled) {
			return false;
		}
		
		$level = strtolower($level);

		//use monolog levels
		$levels = array(
			'debug' => 100,
			'info' => 200,
			'warning' => 300,
			'error' => 400,
			'critical' => 500,
			'alert' => 550,
		);

		if(in_array($level, $levels)) {
			$level_int = $levels[$level];
		}
		else {
			throw new Exception('Invalid log level ' . $level);
		}
			
		if(self::$log) {
			//use monolog
			return self::$log->addRecord($level_int, $message, $context);
		}
		if (!self::$log_file) {
			return false;
		}
		//simple file logging
		$filename = self::$log_file;
		$handle = fopen($filename, 'a+');
		$data = date('Y-m-d G:i:s') . "\t[" . $level  . "]\t" . $message . "\n";
		return fwrite($handle, $data);
	}

	/**
	 * Track performances (rendering time and memory usage)
	 * 
	 * Define START_TIME and START_MEMORY_USAGE constants at the begining
	 * of your script for more precious
	 * 
	 * Register objects with stats() method to tracked_objects array for 
	 * more information (eg : db object)
	 * 
	 * @param bool $register_on_shutdown (optional)
	 */
	static function track_performances($register_on_shutdown = true) {
		if (defined('START_TIME')) {
			$start_time = START_TIME;
		} else {
			$start_time = microtime(true);
		}

		if (defined('START_MEMORY_USAGE')) {
			$start_memory_usage = START_MEMORY_USAGE;
		} else {
			$start_memory_usage = memory_get_usage(true);
		}

		self::$tracked_stats['start_time'] = $start_time;
		self::$tracked_stats['start_memory_usage'] = $start_memory_usage;

		if ($register_on_shutdown) {
			register_shutdown_function(array(__CLASS__, 'track_performances_callback'));
		}
	}

	/**
	 * Echo or return the performances
	 * 
	 * @param bool $return (optional)
	 */
	static function track_performances_callback($return = false) {
		$colors = array('330000', '333300', '003300', '003333', '000033');
		$color = $colors[array_rand($colors)];

		$stats = self::$tracked_stats;

		//html
		$html = '<div id="underscore_performance_tracking" style="
	opacity:0.8;
	padding:3px;
	font-size:10px;
	font-family:Verdana;
	position:absolute;
	bottom:0;
	right:0;
	background:#' . $color . ';
	color:#fff">';

		$elements = array();

		//time
		if (isset($stats['start_time'])) {
			$rendering_time = sprintf('%0.6f', microtime(true) - $stats['start_time']);
			$elements[] = 'Rendering time : ' . $rendering_time . ' s';
		}

		//memory
		if (isset($stats['start_memory_usage'])) {
			$memory_usage = self::format_bytes(memory_get_usage(true) - $stats['start_memory_usage']);
			$memory_peak = self::format_bytes(memory_get_peak_usage(true));
			$elements[] = 'Memory usage : ' . $memory_usage;
			$elements[] = 'Memory peak usage : ' . $memory_peak;
		}

		//other objects
		foreach (self::$tracked_objects as $obj) {
			if (method_exists($obj, 'stats')) {
				$elements[] = call_user_func(array($obj, 'get_stats'));
			}
		}

		$html .= implode(' | ', $elements);

		$html .= '</div>';

		if ($return) {
			return $html;
		}
		echo $html;
	}

	/**
	 * Get the real ip of the visitor
	 * 
	 * @return string
	 */
	static function real_ip() {
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

			$ip = array_pop($ip);
		} else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		} else if (!empty($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if (isset($ip) && filter_var($ip, FILTER_VALIDATE_IP) !== false) {
			return $ip;
		}
		return '0.0.0.0';
	}

	/**
	 * Get request pathinfo
	 * 
	 * @param bool $trim Should we trim the /
	 * @return string 
	 */
	static function pathinfo($trim = false) {
		$pathinfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : mb_substr($_SERVER['PHP_SELF'], mb_strlen($_SERVER['SCRIPT_NAME']));
		if ($trim) {
			$pathinfo = trim($pathinfo, '/');
		}
		return $pathinfo;
	}

	/**
	 * Check if the request is ajax
	 * 
	 * @return boolean
	 */
	static function is_ajax() {
		return (bool) (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}

	/**
	 * Request method (GET, POST...)
	 * 
	 * @return string
	 */
	static function request_method() {
		return isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) :
				(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
	}
		
	/**
	 * Returns the requested URL, does no include the domain name or query string
	 * 
	 * This will return the original URL requested by the user - ignores all
	 * rewrites.
	 * 
	 * @return string  The requested URL without the query string
	 */
	static function request_url()
	{
		return preg_replace('#\?.*$#D', '', $_SERVER['REQUEST_URI']);
	}

	/**
	 * Get a value from session
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @return mixed 
	 */
	static function session_get($name, $default = null) {
		if (session_id() == '') {
			session_start();
		}

		if (isset($_SESSION[$name])) {
			return $_SESSION[$name];
		}
		return $default;
	}

	/**
	 * Set a value in session
	 * 
	 * @param string $name
	 * @param mixed $value 
	 */
	static function session_set($name, $value) {
		if (session_id() == '') {
			session_start();
		}

		$_SESSION[$name] = $value;
	}

	/**
	 * Get a message from session
	 * 
	 * @param string $name
	 * @param mixed $default
	 * @return mixed 
	 */
	static function session_get_flash($name, $default = null) {
		if (session_id() == '') {
			session_start();
		}

		if (isset($_SESSION['flash::' . $name])) {
			$v = $_SESSION['flash::' . $name];
			unset($_SESSION['flash::' . $name]);
			return $v;
		}
		return $default;
	}

	/**
	 * Set a message in session
	 * 
	 * @param string $name
	 * @param string $value
	 */
	static function session_set_flash($name, $value) {
		if (session_id() == '') {
			session_start();
		}

		$_SESSION['flash::' . $name] = $value;
	}

	/**
	 * Persist session (remember me feature)
	 */
	static function session_persist($time = '2 weeks') {
		$currentParams = session_get_cookie_params();

		if (!is_numeric($time)) {
			$time = strtotime($time);
		}

		$params = array(
			$time,
			$currentParams['path'],
			$currentParams['domain'],
			$currentParams['secure']
		);

		call_user_func_array('session_set_cookie_params', $params);

		if (session_id() == '') {
			session_start();
		}
		session_regenerate_id();
	}

	/**
	 * Get a cookie
	 * 
	 * @param string $name
	 * @param mixed $default (optional) 
	 * @return mixed
	 */
	static function cookie_get($name, $default = null) {
		if (isset($_COOKIE[$name])) {
			return $_COOKIE[$name];
		}
		return $default;
	}

	/**
	 * Set a cookie
	 * 
	 * This is the same as setcookie with a few helpers like defaults and time 
	 * interpretation
	 * 
	 * @param string $name
	 * @param string $value
	 * @param int|string (optional) $expire Can be a timestamp or a string (e : +1 week). 0 means when browser closes
	 * @param string $path (optional) '/' for the whole domain or '/foo/' for foo directory 
	 * @param string $domain (optional) .domain.tld or www.domain.tld
	 * @param bool $secure (optional)
	 * @param bool $httponly (optional)
	 * @return bool
	 */
	static function cookie_set($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = true) {
		$ob = ini_get('output_buffering');

		// Abort the method if headers have already been sent, except when output buffering has been enabled 
		if (headers_sent() && (bool) $ob === false || strtolower($ob) == 'off') {
			return false;
		}

		//allow time to be set as string (like +1 week)
		if ($expire && !is_numeric($expire)) {
			$expire = strtotime($expire);
		}

		//make sure domain is set correctly
		if (!empty($domain)) {
			// Fix the domain to accept domains with and without 'www.'. 
			if (strtolower(substr($domain, 0, 4)) == 'www.') {
				$domain = substr($domain, 4);
			}

			// Add the dot prefix to ensure compatibility with subdomains 
			if (substr($domain, 0, 1) != '.') {
				$domain = '.' . $domain;
			}

			// Remove port information. 
			$port = strpos($domain, ':');

			if ($port !== false) {
				$domain = substr($domain, 0, $port);
			}
		}

		//rfc 2109 compatible cookie set
		header('Set-Cookie: ' . rawurlencode($name) . '=' . rawurlencode($value)
				. (empty($domain) ? '' : '; Domain=' . $domain)
				. (empty($expire) ? '' : '; Max-Age=' . $expire)
				. (empty($path) ? '' : '; Path=' . $path)
				. (!$secure ? '' : '; Secure')
				. (!$httponly ? '' : '; HttpOnly'), false);
		return true;
	}

	/**
	 * Format values in a friendly format
	 * 
	 * @param int $size
	 * @param int $precision
	 * @return string 
	 */
	static function format_bytes($size, $precision = 2) {
		if ($size <= 0) {
			return '0B';
		}
		$base = log($size) / log(1024);
		$suffixes = array('B', 'k', 'M', 'G', 'T', 'P');

		return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
	}

	/**
	 * Check if a string is utf8 with a regex
	 * 
	 * @param string $str
	 * @return bool
	 */
	static function is_utf8($str) {
		return preg_match('%^(?: 
              [\x09\x0A\x0D\x20-\x7E]            # ASCII 
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte 
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs 
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte 
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates 
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3 
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15 
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16 
        )*$%xs', $str);
	}

	/**
	 * Converts a string to UTF-8.
	 *
	 * @param string String to convert
	 * @return string
	 */
	public static function convert_to_utf8($str) {
		return mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
	}

	/**
	 * Check if a string only contains ascii characters.
	 *
	 * @param string String to be tested
	 * @return bool
	 */
	public static function is_ascii($str) {
		return!preg_match('/[^\x00-\x7F]/', $str);
	}

	/**
	 * Get file extension
	 * 
	 * This way is faster that pathinfo($filename,PATHINFO_EXTENSION);
	 * 
	 * @param string $file
	 * @return string
	 */
	public static function get_file_extension($file) {
		return strtolower(substr(strrchr($file, '.'), 1));
	}

	/**
	 * Returns the mime type of a file. Returns false if the mime type is not found.
	 *
	 * @param string Full path to the file
	 * @param boolean (optional) Set to false to disable mime type guessing
	 * @return string
	 */
	public static function mimetype($file, $guess = true) {
		if (function_exists('finfo_open')) {
			// Get mime using the file information functions
			$info = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($info, $file);
			finfo_close($info);
			return $mime;
		} else {
			if ($guess === true) {
				// Just guess mime by using the file extension
				$extension = self::get_file_extension($file);
				return isset(self::$mime_types[$extension]) ? self::$mime_types[$extension] : false;
			} else {
				return false;
			}
		}
	}

	/**
	 * Forces a file to be downloaded
	 *
	 * @param string $file Full path to file
	 * @param string $content_type (optional) Content type of the file
	 * @param string $filename (optional) Filename of the download
	 */
	public static function force_download($file, $content_type = null, $filename = null) {
		if ($content_type === null) {
			$content_type = 'application/force-download';
		}
		if ($filename === null) {
			$filename = basename($file);
		}

		header('Content-type: ' . $content_type);
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		echo file_get_contents($file);
		exit();
	}

	/**
	 * Slugify a string
	 * 
	 * @param string $text
	 * @return string
	 */
	static function slugify($text) {
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

		// trim
		$text = trim($text, '-');

		// transliterate
		if (function_exists('iconv')) {
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		}

		// lowercase
		$text = strtolower($text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}

	/**
	 * Create a random string
	 * 
	 * @param int $length
	 * @return string 
	 */
	static function random_string($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$string = '';
		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters))];
		}
		return $string;
	}

	/**
	 * Get a value from array
	 * 
	 * @param array $array
	 * @param string $index
	 * @param mixed $default
	 * @return string 
	 */
	static function array_get($array, $index, $default = null) {
		if (isset($array[$index])) {
			return $array[$index];
		}
		return $default;
	}

	/**
	 * Redirect to a given location
	 * 
	 * @param string $destination 
	 */
	static function redirect($destination) {
		if(strpos($destination,'/') === 0) {
			$destination = self::domain() . rtrim($destination,'/');
		}
		
		header('Location: ' . $destination);
		exit($url);
	}
	
	/**
	 * Get domain, prefixed with http or https
	 * @return string
	 */
	static function domain() {
		$port = (isset($_SERVER['SERVER_PORT'])) ? $_SERVER['SERVER_PORT'] : NULL;
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			return 'https://' . $_SERVER['SERVER_NAME'] . ($port && $port != 443 ? ':' . $port : '');
		} else {
			return 'http://' . $_SERVER['SERVER_NAME'] . ($port && $port != 80 ? ':' . $port : '');
		}
	}
	
	/**
	 * Get a value from $_GET or $_POST
	 * @staticvar array $input
	 * @param string $key
	 * @param mixed $default (optional) default value
	 * @param int $filter FILTER_SANITIZE_XXX
	 * @return mixed 
	 */
	static function input($key, $default = null, $filter = null) {
		static $input;
		if($input == null) {
			$input = array_merge($_GET, $_POST);
		}
		
		$val = self::array_get($input, $key, $default);
		if($filter) {
			return filter_var($val,$filter);
		}
		return filter_var($val,FILTER_SANITIZE_STRING);
	}

}