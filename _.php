<?php

require_once __DIR__ . '/_func.php';

/**
 * Ndrscr php toolkit
 * ------------------------
 *  
 * @author Thomas Portelange <thomas@lekoala.be>
 * @version 0.1
 * @licence http://www.opensource.org/licenses/MIT
 */

/**
 * Main class
 */
class _ {

	const ENV_DEV = 'dev';
	const ENV_TEST = 'test';
	const ENV_PROD = 'prod';

	/**
	 * Globally accessible data 
	 * @var array
	 */
	static $registry = array();

	/**
	 * Config data loaded through config_load
	 * @var array
	 */
	static $config = array();

	/**
	 * Variables available in all templates
	 * @var array
	 */
	static $template_vars = array();

	/**
	 * _ variables exposed in the template
	 * @var array
	 */
	static $template_expose = array('registry', 'lang', 'base_url', 'current_user');

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
	 * Default layout
	 * @var string
	 */
	static $layout = 'layout';

	/**
	 * Invalidate callback by setting it to false
	 * @var bool
	 */
	static $debug_bar_enabled = true;

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
	static $log;

	/**
	 * All log messages sent
	 * @var array
	 */
	static $log_messages = array();

	/**
	 * Provide a swift mailer instance to be used in the ::mail() method
	 * @link http://swiftmailer.org
	 * @var Swift_Mailer 
	 */
	static $mailer;

	/**
	 * Default from email
	 * @var array
	 */
	static $mail_form = array('underscore' => 'robot@underscore.dev');

	/**
	 * Cache to use for the cache method
	 * 
	 * Could be set to apc, database, xcache, memcache or to a directory (for simple file based cache)
	 * 
	 * @var string 
	 */
	static $cache;

	/**
	 * PDO instance to use
	 * @var _pdo
	 */
	static $pdo;

	/**
	 * The lang used for translations functions
	 * @var string
	 */
	static $lang;

	/**
	 * Cache translations
	 * @var array
	 */
	static $translations = array();

	/**
	 * Path to translations
	 * @var string
	 */
	static $translations_dir = null;

	/**
	 * Base path (root of the website)
	 * @var string
	 */
	static $base_path;

	/**
	 * Base url (www.mysite.com)
	 * @var string
	 */
	static $base_url;

	/**
	 * Current user
	 * @var user
	 */
	static $current_user;

	/**
	 * Routes and their options
	 * @var array
	 */
	static $routes = array();

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

	/* core functionnalities */

	/**
	 * Used as static, cannot instantiate
	 */
	protected function __construct() {
		
	}

	/**
	 * Load config files and override with .local file if exists
	 * 
	 * Will attempt to autoconfigure based on config keys (see sample config)
	 * 
	 * @param string $file
	 */
	static function config_load($file) {
		if (!is_file($file)) {
			throw new Exception('File does not exist : ' . $file);
		}

		$ext = self::file_extension($file);
		if ($ext != 'php') {
			throw new Exception('Invalid config file');
		}

		$filename = str_replace('.php', '', $file);

		$local_file = $filename . '.local' . '.php';
		$config = require $file;
		if (is_file($local_file)) {
			$local_config = require $local_file;
			$config = array_replace_recursive($config, $local_config);
		}

		// Auto configure

		if (defined('BASE_PATH')) {
			self::$base_path = BASE_PATH;
		}
		if (isset($config['db'])) {
			try {
				self::$pdo = new _pdo($config['db']);
			} catch (Exception $e) {
				echo '<pre>Failed to connect to the database :' . "\n";
				print_r($config['db']);
				exit();
			}
		}
		if (isset($config['sessions_path'])) {
			if (is_dir(self::$base_path . $config['sessions_path'])) {
				session_save_path(self::$base_path . $config['sessions_path']);
			}
		}
		if (isset($config['log'])) {
			self::$log_file = self::$base_path . $config['log'];
		}
		if (isset($config['db_log'])) {
			$config['db_log'] = self::$base_path . $config['db_log'];
		}
		if (isset($config['tracked_objects'])) {
			if (is_string($config['tracked_objects'])) {
				self::$tracked_objects[] = $config['tracked_objects'];
			}
			if (is_array($config['tracked_objects'])) {
				self::$tracked_objects = array_merge(self::$tracked_objects, $config['tracked_objects']);
			}
		}
		if (isset($config['template_vars'])) {
			self::$template_vars = array_merge(self::$template_vars, $config['template_vars']);
		}
		if (isset($config['template_prefix'])) {
			self::$template_prefix = self::$base_path . $config['template_prefix'];
		}
		if (isset($config['routes'])) {
			self::$routes = $config['routes'];
		}
		if (isset($config['base_url'])) {
			self::$base_url = $config['base_url'];
		} elseif (!self::$base_url) {
			self::$base_url = self::domain();
		}
		if (isset($config['translations_dir'])) {
			self::$translations_dir = self::$base_path . $config['translations_dir'];
		} elseif (!self::$translations_dir) {
			self::$translations_dir = self::$base_path . '/lang';
		}
		if (isset($config['cache'])) {
			self::$cache = $config['cache'];
			if (strpos(self::$cache, '/') === 0) {
				self::$cache = self::$base_path . self::$cache;
			}
		}

		self::$config = $config;

		return $config;
	}

	/**
	 * Access config value in a friendly way
	 * 
	 * @param string $key
	 * @param string $default
	 * @return mixed
	 */
	static function config($key, $default = null) {
		if (empty(self::$config)) {
			return false;
		}
		return self::array_path(self::$config, $key, $default);
	}

	static function is_dev() {
		return self::$env == 'dev';
	}

	static function is_test() {
		return self::$env == 'test';
	}

	static function is_prod() {
		return self::$env == 'prod';
	}

	static function is_cron() {
		return php_sapi_name() == 'cli' || empty($_SERVER['REMOTE_ADDR']);
	}

	/**
	 * Examine dev et test settings and set the env
	 */
	static function config_env() {
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
	 * Autoload depend class
	 */
	static function autoload($dir = null) {
		if ($dir === null) {
			$dir = __DIR__;
		}
		_::$registry['autoload_dir'] = $dir;
		return spl_autoload_register(array(__CLASS__, 'autoload_callback'));
	}

	/**
	 * Autoload callback
	 * 
	 * @param string $class
	 * @return bool
	 */
	static function autoload_callback($class) {
		if (strpos($class, '_') !== 0) {
			return false;
		}
		$dir = _::$registry['autoload_dir'];
		$file = $dir . DIRECTORY_SEPARATOR . $class . '.php';
		if (is_file($file)) {
			require($file);
			return true;
		}
		return false;
	}

	/**
	 * Check if a given template exists
	 * 
	 * @param string $filename
	 * @return string 
	 */
	static function template_exists($filename) {
		$filename = _::template_resolve($filename);
		if (is_file($filename)) {
			return $filename;
		}
		return false;
	}

	/**
	 * Resolve a template name with our conventions
	 * 
	 * @param string $filename
	 * @return string
	 */
	static function template_resolve($filename) {
		if (strpos($filename, '/') === 0) {
			$filename = self::$base_path . $filename;
		} else {
			$filename = self::$template_prefix . $filename;
		}
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if (empty($ext)) {
			$filename .= '.' . self::$template_default_extension;
		}
		return $filename;
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
		if (!is_array($vars)) {
			$vars = array('content' => $vars);
		}
		//recursive templates
		if (is_array($filename)) {
			foreach ($filename as $file) {
				$content = self::render($file, $vars);
				$vars['content'] = $content;
			}
			return $content;
		}
		$resolved_filename = self::template_exists($filename);
		if (!$resolved_filename) {
			throw new Exception($filename . ' is not a valid filename. Resolved as : ' . self::template_resolve($filename));
		}

		foreach (self::$template_expose as $var) {
			self::$template_vars[$var] = self::$$var;
		}
		// Extract variables as references
		extract(array_merge($vars, self::$template_vars), EXTR_REFS);

		//cleanup scope
		unset($vars);
		unset($var);
		unset($filename);

		ob_start();
		include($resolved_filename);
		$output = ob_get_clean();

		if (self::$template_filter) {
			$filter = self::$template_filter;
			$output = $filter($output, $vars);
		}

		return $output;
	}

	/**
	 * Helper to configure php with the right options
	 * - Set the default timezone as you always should
	 * - Make error reporting obvious and strict
	 * - Set php to be using utf8
	 * 
	 * @param string $timezone
	 * @param boolean $strict
	 * @param boolean $utf8 
	 */
	static function config_php($timezone = 'Europe/Brussels', $strict = true, $utf8 = true) {
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

		//disable magic quotes
		if (get_magic_quotes_gpc()) {
			$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
			while (list($key, $val) = each($process)) {
				foreach ($val as $k => $v) {
					unset($process[$key][$k]);
					if (is_array($v)) {
						$process[$key][stripslashes($k)] = $v;
						$process[] = &$process[$key][stripslashes($k)];
					} else {
						$process[$key][stripslashes($k)] = stripslashes($v);
					}
				}
			}
			unset($process);
		}

		ob_start('mb_output_handler');
	}

	/**
	 * Set _::$lang according to querystring
	 * 
	 * @param string $default
	 * @param string $key 
	 * @return string
	 */
	static function detect_lang($default = null, $key = 'lang') {
		//check cookie, session and then input
		$lang = self::cookie($key);
		if (self::session($key)) {
			$lang = self::session($key);
		}
		if (self::input($key)) {
			$lang = self::input($key);
		}

		if (empty($lang)) {
			if ($default === null) {
				if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
					$accept_language = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
					if (is_array($accept_language)) {
						$accept_language = $accept_language[0];
					}
					$default = strtolower(substr($accept_language, 0, 2));
				} else {
					$default = 'en';
				}
			}
			$lang = $default;
		}

		_::session($key, $lang);
		_::cookie($key, $lang);
		_::$lang = $lang;
		return $lang;
	}

	/**
	 * Retrieve current user
	 * 
	 * @param string $token
	 * @param string $class
	 * @return user 
	 */
	static function user($token = 'user_token', $class = 'user') {
		if (self::$current_user) {
			return self::$current_user;
		}
		$current_user_token = self::session('user_token');
		if ($current_user_token) {
			self::$current_user = new $class($current_user_token);
			return self::$current_user;
		}
		return null;
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
		if (!self::$debug_bar_enabled) {
			return;
		}

		//TODO : for ajax requests, we should update the parent _track_performances

		$colors = array('330000', '333300', '003300', '003333', '000033');
		$color = $colors[array_rand($colors)];

		$stats = self::$tracked_stats;

		//html
		$html = '<div id="debug-bar" style="
	opacity:0.7;
	padding:2px 20px 2px 5px;
	line-height:16px;
	font-size:10px;
	font-family:Verdana;
	position:fixed;
	bottom:0;
	right:0;
	white-space: normal;
	background:#' . $color . ';
	background-image: linear-gradient(bottom, #000000 0%, #' . $color . ' 50%);
	background-image: -o-linear-gradient(bottom, #000000 0%, #' . $color . ' 50%);
	background-image: -moz-linear-gradient(bottom, #000000 0%, #' . $color . ' 50%);
	background-image: -webkit-linear-gradient(bottom, #000000 0%, #' . $color . ' 50%);
	background-image: -ms-linear-gradient(bottom, #000000 0%, #' . $color . ' 50%);
	-webkit-box-shadow:  -2px -2px 5px 0px #ccc;
    box-shadow:  -2px -2px 5px 0px #ccc;
	color:#fff">';

		//js
		$html .= '<script language="javascript">
var _current_panel ;
function _toggle(target) {
	var el = document.getElementById(target);
	if(_current_panel && el != _current_panel) {
		_current_panel.style.display = "none";
	}
	_current_panel = el;
	if(_current_panel.style.display == "block") {
    	_current_panel.style.display = "none";
  	}
	else {
		_current_panel.style.display = "block";
	}
	return false;
}
</script>';
		$elements = array();

		//time
		if (isset($stats['start_time'])) {
			$rendering_time = sprintf('%0.6f', microtime(true) - $stats['start_time']);
			$elements[] = 'Rendering time : ' . $rendering_time . ' s';
		}

		//memory
		if (isset($stats['start_memory_usage'])) {
			$memory_usage = self::file_size(memory_get_usage(true) - $stats['start_memory_usage']);
			$memory_peak = self::file_size(memory_get_peak_usage(true));
			$elements[] = 'Memory usage : ' . $memory_usage;
			$elements[] = 'Memory peak usage : ' . $memory_peak;
		}

		//log
		$elements[] = '<a href="#_logs" onclick="_toggle(\'_logs\');return false;" style="color:#fff;">' . count(self::$log_messages) . ' logs</a>
			<div id="_logs" style="display:none;position:fixed;background:#222;bottom:16px;right:0;height:400px;overflow:auto;width:400px;white-space:pre;padding:5px 20px 5px 5px;">' . implode('', self::$log_messages) . '</div>';

		//other objects
		foreach (self::$tracked_objects as $obj) {
			if (method_exists($obj, 'stats')) {
				$elements[] = call_user_func(array($obj, 'stats'));
			}
		}

		$html .= implode(' | ', $elements);

		$html .= '</div>';

		if ($return) {
			return $html;
		}
		echo $html;
	}

	/**
	 * Route a request, returns an array of params
	 * 
	 * @param string $uri
	 * @return array 
	 */
	static function route($uri = null) {
		if ($uri === null) {
			$uri = self::pathinfo();
		}
		$uri = trim($uri, '/');
		$uri_parts = explode('/', $uri);
		$routes = self::$routes; //from config
		$params = array();
		$found_route = null;

		foreach ($routes as $route => $options) {
			$route = trim($route, '/');

			$route_parts = explode('/', $route);
			$i = 0;

			$defaults = _::array_get($options, 'defaults', array());
			$rules = _::array_get($options, 'rules', array());

			//test each part of the route
			foreach ($route_parts as $part) {
				$invalid_route = false;

				$param_name = str_replace(':', '', $part);

				$part_defaults = _::array_get($defaults, $param_name);
				$part_rules = _::array_get($rules, $param_name);

				if (!empty($uri_parts[$i])) {
					//we have a :param
					if (strpos($part, ':') === 0) {
						$rule_valid = true;

						//is it a valid value
						if ($part_rules) {
							$rule_valid = false;

							//rule is an array
							if (is_array($part_rules)) {
								if (in_array($uri_parts[$i], $part_rules)) {
									$rule_valid = true;
								}
							}
							//rule is a function
							elseif (is_callable($part_rules)) {
								if ($part_rules($uri_parts[$i])) {
									$rule_valid = true;
								}
							}
							//rule is a regex or a predefined validator
							elseif (is_string($part_rules)) {
								switch ($part_rules) {
									case 'date' :
										if (strtotime($uri_parts[$i]) !== -1) {
											$rule_valid = true;
										}
										break;
									case 'string':
										if (!is_numeric($uri_parts[$i]) && is_string($uri_parts[$i])) {
											$rule_valid = true;
										}
										break;
									case 'int':
									case 'numeric' :
									case 'number' :
									case 'integer' :
										if (is_numeric($uri_parts[$i])) {
											$rule_valid = true;
										}
										break;
									default :
										if (preg_match($part_rules, $uri_parts[$i])) {
											$rule_valid = true;
										}
										break;
								}
							}
						}

						if ($rule_valid) {
							$params[$param_name] = $uri_parts[$i];
						} else {
							if ($part_defaults) {
								$i--; //a default is used, don't advance in url
								$params[$param_name] = $part_defaults;
							} else {
								$invalid_route = true;
							}
						}
					}
					//fixed value
					else {
						//check if it match
						if ($part != $uri_parts[$i]) {
							$invalid_route = true;
						}
					}
				} else {
					//do we have a default value
					if ($part_defaults !== null) {
						$params[$param_name] = $part_defaults;
					} else {
						$invalid_route = true;
					}
				}

				if ($invalid_route) {
					break;
				}
				$i++;
			}

			if ($invalid_route == false) {
				$found_route = $route;
			}
		}

		if ($found_route === null) {
			throw new Exception('Not route found for ' . $uri);
		}

		array_map('strtolower', $params);

		//inject in template
		self::$template_vars = array_merge(self::$template_vars, $params);

		return $params;
	}

	/**
	 * Translate in a template
	 * @param string $string 
	 */
	static function t($string, $lang = null) {
		echo self::translate($string, $lang);
	}

	/**
	 * Translate a string in a language
	 * @param string $string
	 * @param string $lang
	 * @return string 
	 */
	static function translate($string, $lang = null) {
		if ($lang === null) {
			$lang = self::$lang;
		}

		$parts = explode('.', $string);

		$arr = &_::$translations;
		$path = '';
		while (count($parts) > 1) {
			$part = array_shift($parts);
			$path .= '/' . $part;
			if (!isset($arr[$part])) {
				$arr[$part] = array();
				$file = _::$translations_dir . $path . '.php';
				if (is_file($file)) {
					$arr[$part] = require $file;
				}
			}
			$arr = &$arr[$part];
		}
		$string = $parts[0];

		if (isset($arr[$string][$lang])) {
			$string = $arr[$string][$lang];
		}

		return $string;
	}

	/* request helpers */

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
		$pathinfo = $_SERVER['REQUEST_URI'];
		if (!empty($_SERVER['QUERY_STRING'])) {
			$pos = strpos($_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']);
			$pathinfo = substr($_SERVER['REQUEST_URI'], 0, $pos - 1);
		}

		if ($trim) {
			$pathinfo = trim($pathinfo, '/');
		}
		return $pathinfo;
	}

	/**
	 * Detect if a request is made from a mobile device
	 * 
	 * @return string|bool 
	 */
	static function is_mobile() {
		$devices = array(
			"android" => "android.*mobile",
			"androidtablet" => "android(?!.*mobile)",
			"blackberry" => "blackberry",
			"blackberrytablet" => "rim tablet os",
			"iphone" => "(iphone|ipod)",
			"ipad" => "(ipad)",
			"palm" => "(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)",
			"windows" => "windows ce; (iemobile|ppc|smartphone)",
			"windowsphone" => "windows phone os",
			"generic" => "(kindle|mobile|mmp|midp|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)"
		);

		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$http_accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';

		//look up in devices
		foreach ($devices as $device => $regexp) {
			if (preg_match("/" . $regexp . "/i", $user_agent)) {
				return $device;
			}
		}

		//fallback true/false
		if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
			return true;
		} elseif (strpos($http_accept, 'text/vnd.wap.wml') > 0 || strpos($http_accept, 'application/vnd.wap.xhtml+xml') > 0) {
			return true;
		}
		return false;
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
	 * Check if the request is over https
	 * 
	 * @return boolean
	 */
	static function is_https() {
		return (bool) (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
	}

	/**
	 * Request method (GET, POST...)
	 * 
	 * @return string
	 */
	static function method() {
		return isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) :
				(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
	}

	/**
	 * Is request POST
	 * @return bool
	 */
	static function is_post() {
		return self::method() == 'POST';
	}

	/**
	 * Is request GET
	 * @return bool
	 */
	static function is_get() {
		return self::method() == 'GET';
	}

	/**
	 * Is request DELETE
	 * @return bool
	 */
	static function is_delete() {
		return self::method() == 'DELETE';
	}

	/**
	 * Is request PUT
	 * @return bool
	 */
	static function is_put() {
		return self::method() == 'POST';
	}

	/**
	 * Returns the requested URL, does no include the domain name or query string
	 * 
	 * This will return the original URL requested by the user - ignores all
	 * rewrites.
	 * 
	 * @param bool $remove_querystrings
	 * @return string  The requested URL without the query string
	 */
	static function url($remove_querystrings = true) {
		if ($remove_querystrings) {
			return preg_replace('#\?.*$#D', '', $_SERVER['REQUEST_URI']);
		}
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Manipulate query string for the current url
	 * @param array|string $key
	 * @param mixed $value Leave null if you want to remove the key from the querystring
	 * @return string 
	 */
	static function querystring($key, $value = null) {
		$url = self::url(true);
		$sep = ini_get('arg_separator.output');
		$qs = $_GET;
		if (is_array($key)) {
			$qs = array_merge($qs, $key);
		} else {
			if ($value === null) {
				unset($qs[$key]);
			} else {
				$qs[$key] = $value;
			}
		}
		$str = '';
		foreach ($qs as $k => $v) {
			if (is_array($v)) {
				$str .= http_build_query(array($k => $v)) . $sep;
			} else {
				$str .= "$k=" . urlencode($v) . $sep;
			}
		}
		return $url . '?' . substr($str, 0, -1); /* trim off trailing $sep */
	}

	/**
	 * Redirect to a given location
	 * 
	 * @param string $url 
	 * @param bool $force Prevent redirect loops if set to true
	 */
	static function redirect($url, $force = false) {
		if (strpos($url, '/') === 0) {
			$url = self::domain() . rtrim($url, '/');
		}

		if (!headers_sent() && !$force) {
			header('Location: ' . $url);
		}
		echo '<meta http-equiv="refresh" content="1;url=' . $url . '" />';
		echo '<script type="text/javascript">setTimeout(function() { window.location.href = \'' . $url . '\' ; }, 1000);</script>';
		exit($url);
	}

	/**
	 * Redirect to previous page
	 */
	static function redirect_back() {
		$back_url = self::input('back_url', self::array_get($_SERVER, 'HTTP_REFERER'), null);
		self::redirect($back_url);
	}

	/**
	 * Get domain, prefixed with http or https
	 * @return string
	 */
	static function domain() {
		if (php_sapi_name() == 'cli') {
			return 'cli';
		}

		$port = (isset($_SERVER['SERVER_PORT'])) ? $_SERVER['SERVER_PORT'] : null;
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			return 'https://' . $_SERVER['SERVER_NAME'] . ($port && $port != 443 ? ':' . $port : '');
		} else {
			return 'http://' . $_SERVER['SERVER_NAME'] . ($port && $port != 80 ? ':' . $port : '');
		}
	}

	/**
	 * Make sure that we use https
	 */
	static function force_https() {
		if (!self::is_https()) {
			$url = str_replace('http', 'https', self::domain());
			self::redirect($url);
		}
	}

	/**
	 * Return an array of inputed data. Typical usage is extract(_::input_arr('some,value'))
	 * @param string|arr $arr
	 * @return array
	 */
	static function input_arr($arr) {
		$arr = _::arrayify($arr);
		$kk = array();
		foreach ($arr as $k) {
			$kk[$k] = _::input($k);
		}
		return $kk;
	}

	static function session_input($key, $default = null) {
		$v = self::input($key);
		if ($v) {
			return $v;
		}
		$v = self::session($key);
		if ($v) {
			return $v;
		}
		return $default;
	}

	/**
	 * Get a value from $_GET or $_POST
	 * 
	 * If the value is an array, it is NOT automatically filtered
	 * 
	 * @param string $key
	 * @param mixed $default (optional) default value
	 * @param int|array $filter FILTER_SANITIZE_XXX or an array of valid values
	 * @return mixed 
	 */
	static function input($key, $default = null, $filter = '') {
		if ($filter === '') {
			$filter = FILTER_SANITIZE_SPECIAL_CHARS;

			//smart default filter
			switch ($key) {
				case 'id' :
				case (strpos($key, '_id') !== false) :
				case 'page' :
				case 'p' :
					$filter = FILTER_SANITIZE_NUMBER_INT;
					break;
				case 'order' :
				case 'sort' :
					$filter = array('asc', 'desc');
				case 'email':
				case (strpos($key, '_email') !== false) :
					$filter = FILTER_SANITIZE_EMAIL;
					break;
				case 'url':
				case (strpos($key, '_url') !== false) :
				case 'website' :
					$filter = FILTER_SANITIZE_URL;
					break;
			}
		}

		$filter = function($val) use ($filter, $default) {
			if ($filter && is_string($val)) {
				//if filter is an array, check for valid values
				if (is_array($filter)) {
					if (in_array($val, $filter)) {
						return $val;
					}
					return $default;
				}
				if (is_string($filter)) {
					$filter = null;
				}
				return filter_var($val, $filter);
			}
			//do not filter arrays
			return $val;
		};

		$keys = explode('/', $key);
		$key = array_shift($keys);

		if (isset($_GET[$key])) {
			$val = $_GET[$key];
		} elseif (isset($_POST[$key])) {
			$val = $_POST[$key];
		} else {
			return $filter($default);
		}

		foreach ($keys as $key) {
			if (isset($val[$key])) {
				$val = $val[$key];
			} else {
				return $filter($default);
			}
		}

		return $filter($val);
	}

	/**
	 * Get a file references by the key (array levels must be separated with a slash)
	 *
	 * @param string $key The array key (separate multiple levels with a slash)
	 * @return string|null
	 */
	static public function file($key) {
		$file = $_FILES;
		$keys = explode('/', $key);
		$key_l1 = array_shift($keys);
		if (!isset($file[$key_l1])) {
			return null;
		}
		$path = $file[$key_l1]['tmp_name'];
		foreach ($keys as $key) {
			if (!isset($path[$key])) {
				return null;
			}
			$path = $path[$key];
		}
		if (!is_string($path) || !is_uploaded_file($path)) {
			return null;
		}
		return $path;
	}

	/**
	 * Get a file name by the key (array levels must be separated with a slash)
	 *
	 * @param string $key The array key (separate multiple levels with a slash)
	 * @return string|null
	 */
	static public function file_name($key) {
		$file = $_FILES;
		$keys = explode('/', $key);
		$key_l1 = array_shift($keys);
		if (!isset($file[$key_l1])) {
			return null;
		}
		$path = $file[$key_l1]['name'];
		foreach ($keys as $key) {
			if (!isset($path[$key])) {
				return null;
			}
			$path = $path[$key];
		}
		if (!is_string($path) || !is_uploaded_file($path)) {
			return null;
		}
		return $path;
	}

	static function read_line($filename, $line) {
		$l = 0;
		$line--;

		$fh = fopen($filename, 'r');
		while (($buffer = fgets($fh)) !== FALSE) {
			if ($l == $line) {
				return $buffer;
			}
			$l++;
		}
	}

	static function count_files_in_dir($dir) {
		$count = 0;
		if (is_dir($dir)) {
			$handler = opendir($dir);
			while ($file = readdir($handler)) {
				if ($file != "." && $file != "..") {
					$count++;
				}
			}
		}
		return $count;
	}

	static function csv_to_array($filename, $delimiter = 'auto', $header = null) {
		if (!file_exists($filename) || !is_readable($filename)) {
			return FALSE;
		}

		if ($delimiter == 'auto') {
			$line = _::read_line($filename, 1);
			$delimiters = array(',', ';');
			$max = 0;
			foreach ($delimiters as $d) {
				$c = substr_count($line, $d);
				if ($c > $max) {
					$delimiter = $d;
					$max = $c;
				}
			}
		}

		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
				if ($header !== false && !is_array($header)) {
					$header = $row;
				} else {
					if ($header !== false) {
						$data[] = array_combine($header, $row);
					} else {
						$data[] = $row;
					}
				}
			}
			fclose($handle);
		}
		return $data;
	}

	/**
	 * Get/set a value from the cache
	 * 
	 * @param string $key
	 * @param mixed $value 
	 * @return mixed
	 */
	static function cache($key, $value = null, $ttl = null) {
		if (!self::$cache) {
			throw new Exception('You must set a cache file to use');
		}

		$cache = self::$cache;
		$expire = (!$ttl) ? 0 : time() + $ttl;

		if ($value === null) {
			// Get a value
			switch ($cache) {
				case 'apc':
					$value = apc_fetch($key);
					break;

				case 'database':
					$stmt = self::$pdo->prepare("SELECT value FROM cache WHERE key = :key AND (expire = 0 OR expire >= :time)");
					$time = time();
					$stmt->execute(compact('key', 'time'));
					$res = $stmt->fetch();
					if ($res) {
						$value = $res['value'];
					}
					break;

				case $cache instanceof Memcache:
					$value = $cache->get($key);
					break;

				case 'redis':
					$value = $this->data_store->get($key);
					break;

				case 'xcache':
					$value = xcache_get($key);
					break;

				default :
					if (is_file($cache . DIRECTORY_SEPARATOR . $key)) {
						$cached_content = file_get_contents($cache . DIRECTORY_SEPARATOR . $key);
						$cached_content = unserialize($cached_content);
						if ($cached_content['expire'] == 0 || $cached_content['expire'] >= time()) {
							$value = $cached_content['value'];
						}
					}
					break;
			}
			return $value;
		}

		// Set a value
		switch ($cache) {
			case 'apc':
				return apc_store($key, $value, $ttl);

			case 'database':
				$stmt = self::$pdo->prepare("SELECT value FROM cache WHERE key = :key");
				$stmt->execute(compact('key'));
				$res = $stmt->fetch();
				if ($res) {
					$stmt = self::$pdo->prepare("INSERT INTO cache(key, value, expire) VALUES (:key,:value,:expire)");
					return $stmt->execute(compact('key', 'value', 'expire'));
				} else {
					$stmt = self::$pdo->prepare("UPDATE cache SET key = :key, value = :value, expire = :expire)");
					return $stmt->execute(compact('key', 'value', 'expire'));
				}

			case $cache instanceof Memcache:
				if ($ttl > 2592000) {
					$ttl = time() + 2592000;
				}
				$result = $cache->replace($key, $value, 0, $ttl);
				if (!$result) {
					return $cache->set($key, $value, 0, $ttl);
				}
				return $result;

			case 'redis':
				if ($ttl) {
					return $cache->setex($key, $value, $ttl);
				}
				return $cache->set($key, $value);

			case 'xcache':
				return xcache_set($key, $value, $ttl);

			default :
				if (!is_dir($cache)) {
					throw new Exception($cache . ' is not a valid directory');
				}

				$cached_content = array(
					'expire' => $expire,
					'value' => $value
				);
				$cached_content = serialize($cached_content);
				return file_put_contents($cache . DIRECTORY_SEPARATOR . $key, $cached_content);
		}
	}

	/**
	 * Debug a variable and stop the script
	 * @param mixed $var 
	 */
	static function debug($var) {
		echo '<pre>';

		if ($var instanceof Exception) {
			echo '<strong>Exception in ' . basename($var->getFile()) . ':' . $var->getLine() . "\t" . $var->getMessage() . '</strong>';
			$trace = $var->getTrace();
		} else {
			var_dump($var);
			$trace = debug_backtrace();
		}

		echo "\n\n<em>Trace</em> :\n";
		foreach ($trace as $tr) {
			$file = 'php';
			if (isset($tr['file'])) {
				$file = $tr['file'];
			}
			$line = 0;
			if (isset($tr['line'])) {
				$line = $tr['line'];
			}
			$function = isset($tr['function']) ? $tr['function'] : null;
			$class = isset($tr['class']) ? $tr['class'] : null;
			$object = isset($tr['object']) ? $tr['object'] : null;
			$type = isset($tr['type']) ? $tr['type'] : null;
			$args = isset($tr['args']) ? $tr['args'] : array();
			$args_types = array();

			$ct = function($arg) use(&$ct) {
				switch (gettype($arg)) {
					case 'integer':
					case 'double':
						return '<span style="color:red">' . $arg . '</span>';
					case 'string':
						$arg = htmlspecialchars(substr($arg, 0, 64)) . ((strlen($arg) > 64) ? '...' : '');
						return '<span style="color:green">"' . $arg . '"</span>';
					case 'array':
						$tmp = array();
						foreach ($arg as $arg) {
							$tmp[] = $ct($arg);
						}
						return 'Array(' . implode(',', $tmp) . ')';
					case 'object':
						return '<span style="color:grey">Object(<em>' . get_class($arg) . '</em>)</span>';
					case 'resource':
						return '<span style="color:orange">Resource</span>';
					case 'boolean':
						return $arg ? 'TRUE' : 'FALSE';
					case 'NULL':
						return 'NULL';
					default:
						return '?';
				}
			};

			foreach ($args as $arg) {
				$args_types[] = $ct($arg);
			}

			echo basename($file) . "<span style='color:silver'>:</span>" . $line . "\t" . $class . $type . $function . '(' . implode(',', $args_types) . ')' . "\n";
		}
		echo '</pre>';
		exit();
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

		if (in_array($level, array_keys($levels))) {
			$level_int = $levels[$level];
		} else {
			throw new Exception('Invalid log level ' . $level);
		}

		if (self::$log && self::$log instanceof Monolog\Logger) {
			//use monolog
			return self::$log->addRecord($level_int, $message, $context);
		}
		if (!self::$log_file) {
			return false;
		}
		//simple file logging
		if (is_array($message)) {
			$message = json_encode($message);
		}
		$filename = self::$log_file;
		$handle = fopen($filename, 'a+');
		$data = date('Y-m-d G:i:s') . "\t[" . $level . "]\t" . $message . "\n";
		self::$log_messages[] = $data;
		return fwrite($handle, $data);
	}

	/**
	 * Mail wrapper
	 * 
	 * Can use a Swift_Mailer instance if defined. Therefore, the method follows
	 * closely the way Swift_Mailer works
	 * 
	 * This always send html emails because who wants to send only plain text?
	 * 
	 * @param string|array $to
	 * @param string $subject
	 * @param string $message
	 * @param string|array $from
	 * @param array $headers
	 * @return Result of the mail function
	 */
	static function mail($to, $subject, $message = '', $from = null, array $headers = array()) {
		// Default values
		if ($from === null) {
			$from = self::$mail_from;
		}

		// Send emails in utf8
		if (!self::is_utf8($message)) {
			$message = self::to_utf8($message);
		}
		if (!self::is_utf8($subject)) {
			$subject = self::to_utf8($subject);
		}

		// Use swift mailer if defined
		if (isset(self::$mailer) && self::$mailer instanceof Swift_Mailer) {
			// Handle strings
			if (is_string($to)) {
				$to = explode(',', $to);
			}
			if (is_string($from)) {
				$from = array($from);
			}
			if (is_string($headers)) {
				$headers = explode("\r\n", $headers);
			}

			$message = Swift_Message::newInstance($subject)
					->setFrom($from)
					->setTo($to)
					->setBody($message);

			// Set headers
			$headers = $message->getHeaders();
			$headers->addTextHeader('Content-type', 'text/html; charset=UTF-8');
			$headers->addTextHeader('X-Mailer', 'Underscore');

			foreach ($headers as $k => $v) {
				$headers->addTextHeader($k, $v);
			}

			return self::$mailer->send($message);
		}

		// Handle arrays
		if (is_array($to)) {
			$tmp = array();
			foreach ($to as $k => $v) {
				if (is_string($k)) {
					$v = $k . ' <' . $v . '>';
				}
				$tmp[] = $v;
			}
			$to = implode(', ', $tmp);
		}
		if (is_array($from)) {
			$from = $from[0] . ' <' . $from[1] . '>';
		}

		// Headers
		$base_headers = "From: " . $from . "\r\n";
//		$base_headers .= "Reply-To: info@example.com\r\n"; 
//		$base_headers .= "Return-Path: info@example.com\r\n"; 
		$base_headers .= "X-Mailer: Underscore\r\n";
		$base_headers .= 'MIME-Version: 1.0' . "\r\n";
		$base_headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

		array_walk($headers, function (&$item, $key) {
			$item = $key . ": " . $item;
		});
		$headers = implode("\r\n", $headers);

		$headers = $base_headers . $headers;

		// Fix any bare linefeeds in the message to make it RFC821 Compliant. 
		$message = preg_replace("#(?<!\r)\n#si", "\r\n", $message);

		// Make sure there are no bare linefeeds in the headers 
		$headers = preg_replace('#(?<!\r)\n#si', "\r\n", $headers);

		return mail($to, $subject, $message, $headers);
	}

	/**
	 * set/get a value in session
	 * 
	 * @param string $name
	 * @param mixed $value 
	 * @return mixed
	 */
	static function session($name, $value = null) {
		if (!self::session_is_active()) {
			session_start();
		}

		if ($value === null) {
			if (isset($_SESSION[$name])) {
				return $_SESSION[$name];
			}
			return null;
		}

		$_SESSION[$name] = $value;
		return $value;
	}

	/**
	 * Tell if there is a session active
	 * 
	 * @link http://stackoverflow.com/questions/3788369/how-to-tell-if-a-session-is-active
	 * @return bool 
	 */
	static function session_is_active() {
		$setting = 'session.use_trans_sid';
		$current = ini_get($setting);
		if (false === $current) {
			throw new Exception('Unable to determine if the session is opened by using setting ' . $setting);
		}
		$result = @ini_set($setting, $current);
		return $result !== $current;
	}

	/**
	 * Truly destroy the session
	 * 
	 * @return bool
	 */
	static function session_destroy() {
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
			);
		}

		return session_destroy();
	}

	/**
	 * Persist session (remember me feature)
	 */
	static function remember_me($time = '2 weeks') {
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
	 * Get/set a cookie
	 * 
	 * @param string $name
	 * @param string $value
	 * @param int|string (optional) $expire Can be a timestamp or a string (e : +1 week). 0 means when browser closes
	 * @param string $path (optional) '/' for the whole domain or '/foo/' for foo directory 
	 * @param string $domain (optional) .domain.tld or www.domain.tld
	 * @param bool $secure (optional)
	 * @param bool $httponly (optional)
	 * @return mixed
	 */
	static function cookie($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = true) {
		if ($value === null) {
			if (isset($_COOKIE[$name])) {
				return $_COOKIE[$name];
			}
			return null;
		}

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

	/* js helpers */

	/**
	 * Flash message ready to be integrated with jgrowl. Call the
	 * method with no parameters to retrieve growl messages in the template
	 * 
	 * @link http://stanlemon.net/24
	 * @param string|array  $message
	 * @param array $options sticky, header, life, glue, theme, corners, speed, easing, log, beforeOpen, open, beforeClose, close, animateOpen, animateClose 
	 * @return mixed
	 */
	static function growl($message = null, $options = array()) {
		if (_::is_ajax()) {
			echo $message;
			exit();
		}
		if ($message === null) {
			//retrieve all messages
			$html = '';
			$messages = self::session('growl');
			if (is_array($messages)) {
				foreach ($messages as $msg) {
					$html .= "jQuery.jGrowl('" . $msg['content'] . "'";
					if (!empty($msg['options']) && is_array($msg['options'])) {
						$html .= ',' . json_encode($msg['options']);
					}
					$html .= ")\n";
				}
				unset($_SESSION['growl']);
			}
			return $html;
		}

		//retrieve existing messages
		$messages = self::session('growl');
		if (!is_array($messages)) {
			$messages = array();
		}
		$messages[] = array('content' => $message, 'time' => date('Y-m-d H:i:s'), 'options' => $options);
		self::session('growl', $messages);

		return $messages;
	}

	/* file helpers */

	/**
	 * Format values in a friendly format
	 * 
	 * @param int $size
	 * @param int $precision
	 * @return string 
	 */
	static function file_size($size, $precision = 2) {
		if ($size <= 0) {
			return '0B';
		}
		$base = log($size) / log(1024);
		$suffixes = array('B', 'k', 'M', 'G', 'T', 'P');

		return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
	}

	/**
	 * Create a gd resource from a filename
	 * 
	 * @param string $filename
	 * @return resource
	 */
	static function image_create($filename) {
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		switch ($ext) {
			case 'gif':
				$gd = imagecreatefromgif($filename);
				break;
			case 'jpg':
			case 'jpeg':
				$gd = imagecreatefromjpeg($filename);
				break;
			case 'png':
				$gd = imagecreatefrompng($filename);
				break;
			default:
				$gd = imagecreatefromstring(file_get_contents($filename));
		}
		return $gd;
	}

	/**
	 * Get image type from binary (eg through file_get_contents)
	 * @param string $binary
	 * @return string
	 */
	static function image_type_from_binary($binary) {
		if (!preg_match('/\A(?:(GIF8[79]a)|(\xff\xd8\xff)|(\x89PNG\x0d\x0a)))/', $image, $matches)) {
			return 'application/octet-stream';
		}
		//gif = 1, jpeg = 2, png = 3
		return count($matches);
	}

	/**
	 * Save a gd resource to a filename
	 * 
	 * @param resource $image
	 * @param string $filename filename or mimetype for browser output
	 * @param int $quality 0-100
	 * @return bool
	 * @throws Exception
	 */
	static function image_save($image, $filename = null, $quality = 75) {
		if (!is_resource($image)) {
			throw new Exception('Image must be a resource');
		}

		$mimes = array('image/gif', 'image/jpg', 'image/jpeg', 'image/png');
		if (in_array($filename, $mimes)) {
			$ext = str_replace('image/', '', $filename);
			header("Content-type: $filename");
			$filename = null;
		} else {
			$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		}

		switch ($ext) {
			case 'gif':
				return imagegif($image, $filename);
			case 'jpg':
			case 'jpeg':
				return imagejpeg($image, $filename, $quality);
			case 'png':
				$quality = round($quality / 100 * 9);
				$quality = 9 - $quality;
				return imagepng($image, $filename, $quality);
			default:
				throw new Exception('Not supported ' . $ext);
		}
	}

	/**
	 * Resize an image, proptionnaly if needed
	 * 
	 * @param string $filename
	 * @param int|string $width
	 * @param int|string $height
	 * @param bool $proportional
	 * @param bool $output
	 * @return resource
	 */
	static function image_resize($filename, $width = 0, $height = 0, $proportional = true, $output = false) {
		if ($width <= 0 && $height <= 0) {
			return false;
		}
		$infos = getimagesize($filename);
		$width_old = $infos[0];
		$height_old = $infos[1];
		$type = $infos[2];

		//handle x
		if (strpos($width, 'x') !== false) {
			$parts = explode('x', $width);
			$width = trim($parts[0]);
			$height = trim($parts[1]);
		}

		//handle %
		if (strpos($width, '%') !== false) {
			$perc = trim($width, '%');
			$width = $width_old / 100 * $perc;
		}
		if (strpos($height, '%') !== false) {
			$perc = trim($height, '%');
			$height = $height_old / 100 * $perc;
		}

		//for proportional resize, check the ratio
		if ($proportional) {
			if ($width == 0) {
				$ratio = $height / $height_old;
			} elseif ($height == 0) {
				$ratio = $width / $width_old;
			} else {
				$ratio = min($width / $width_old, $height / $height_old);
			}

			$width = round($width_old * $ratio);
			$height = round($height_old * $ratio);
		}

		$image = self::image_create($filename);
		$image_resized = imagecreatetruecolor($width, $height);

		//transparency support
		if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {
			$transparency = imagecolortransparent($image);
			if ($transparency && $transparency > 0) {
				$transparency_color = imagecolorsforindex($image, $transparency);
				$transparency = imagecolorallocate($image_resized, $transparency_color['red'], $transparency_color['green'], $transparency_color['blue']);
				imagefill($image_resized, 0, 0, $transparency);
				imagecolortransparent($image_resized, $transparency);
			} elseif ($type == IMAGETYPE_PNG) {
				imagealphablending($image_resized, false);
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				imagefill($image_resized, 0, 0, $color);
				imagesavealpha($image_resized, true);
			}
		}

		imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $width, $height, $width_old, $height_old);

		if ($output) {
			$filename = image_type_to_mime_type($type);
		}

		return self::image_save($image_resized, $filename);
	}

	/**
	 * Crop an image, resizing it before for optimal cropping result
	 * 
	 * @param string $filename
	 * @param int $width
	 * @param int $height
	 * @param int|string $from_x
	 * @param int|string $from_y
	 * @param bool $resize_before
	 * @return bool
	 * @throws Exception
	 */
	static function image_crop($filename, $width = 0, $height = 0, $from_x = '50%', $from_y = '50%', $resize_before = true) {
		if ($resize_before) {
			self::image_resize($filename, $width, $height);
		}

		$infos = getimagesize($filename);
		$width_old = $infos[0];
		$height_old = $infos[1];
		$type = $infos[2];

		if ($width == 0) {
			$width = $width_old;
		}
		if ($height == 0) {
			$height = $height_old;
		}

		//handle x
		if (strpos($from_x, 'x') !== false) {
			$parts = explode('x', $from_x);
			$from_x = trim($parts[0]);
			$from_y = trim($parts[1]);
		}

		//handle %
		if (strpos($from_x, '%') !== false) {
			$perc = trim($from_x, '%');
			$from_x = round(max($width_old - $width, 0) / 100 * $perc);
		}
		if (strpos($from_y, '%') !== false) {
			$perc = trim($from_y, '%');
			$from_y = round(max($height_old - $height, 0) / 100 * $perc);
		}

		$image = self::image_create($filename);
		$image_resized = imagecreatetruecolor($width, $height);

		//transparency support
		if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {
			$transparency = imagecolortransparent($image);
			if ($transparency) {
				$transparency_color = imagecolorsforindex($image, $transparency);
				$transparency = imagecolorallocate($image_resized, $transparency_color['red'], $transparency_color['green'], $transparency_color['blue']);
				imagefill($image_resized, 0, 0, $transparency);
				imagecolortransparent($image_resized, $transparency);
			} elseif ($type == IMAGETYPE_PNG) {
				imagealphablending($image_resized, false);
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				imagefill($image_resized, 0, 0, $color);
				imagesavealpha($image_resized, true);
			}
		}

		imagecopyresampled($image_resized, $image, 0, 0, $from_x, $from_y, $width, $height, $width, $height);
		return self::image_save($image_resized, $filename);
	}

	/**
	 * Flip image
	 * @param string|resource $filename
	 * @param string $mode vertical,horizontal,both
	 * @return type
	 */
	static function image_flip($filename, $mode = 'vertical') {
		$image = $filename;
		if (!is_resource($image)) {
			$image = self::image_create($filename);
		}

		$width = imagesx($image);
		$height = imagesy($image);

		$src_x = 0;
		$src_y = 0;
		$src_width = $width;
		$src_height = $height;

		switch ($mode) {
			case 'h':
			case 'horizontal':
				$src_y = $height;
				$src_height = -$height;
				break;
			case 'v':
			case 'h':
			case 'vertical':
				$src_x = $width;
				$src_width = -$width;
				break;
			case 'b':
			case 'wh':
			case 'hw':
			case 'both':
				$src_x = $width;
				$src_y = $height;
				$src_width = -$width;
				$src_height = -$height;
				break;

			default:
				return $image;
		}

		$image_flipped = imagecreatetruecolor($width, $height);

		imagecopyresampled($image_flipped, $image, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height);
		if (!is_resource($filename)) {
			self::image_save($image_flipped, $filename);
		}
		return $image_flipped;
	}

	/**
	 * Image auto rotate based on exif data
	 * @param string $filename Filename to be examined by exif_read_data
	 * @param resource $resource existing resource to use
	 * @return resource
	 */
	static function image_auto_rotate($filename, $resource = null) {
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if (!in_array(strtolower($ext), array('jpg', 'jpeg'))) {
			return false;
		}
		$exif = exif_read_data($filename);
		$image = $resource;
		if (!$image) {
			$image = self::image_create($filename);
		}
		if (!empty($exif['Orientation'])) {
			switch ($exif['Orientation']) {
				case 2: // horizontal flip
					$image = self::image_flip($image, 'horizontal');
					break;
				case 3: // 180 rotate left
					$image = imagerotate($image, 180, 0);
					break;
				case 4: //vertical flip
					$image = self::image_flip($image, 'vertical');
					break;
				case 5: //vertical flip + 90 rotate right
					$image = self::image_flip($image, 'vertical');
					$image = imagerotate($image, -90);
					break;
				case 6: //90 rotate right
					$image = imagerotate($image, -90, 0);
					break;
				case 7: // horizontal flip + 90 rotate right
					$image = self::image_flip($image, 'horizontal');
					$image = imagerotate($image, -90);
				case 8: // 90 rotate left
					$image = imagerotate($image, 90, 0);
					break;
			}
			if (!$resource) {
				self::image_save($image, $filename);
			}
		}
		return $image;
	}

	static function image_rotate($filename, $dir) {
		$image = self::image_create($filename);
		if (is_string($dir)) {
			switch ($dir) {
				case 'left':
					$dir = 90;
					break;
				case 'right':
					$dir = -90;
					break;
				default:
					throw new Exception("$dir not supported");
			}
		}
		$image = imagerotate($image, $dir, 0);
		return self::image_save($image, $filename);
	}

	/**
	 * Extract latitude and longitude
	 * @param string $filename
	 * @return bool|array
	 */
	static function image_location($filename) {
		//get the EXIF
		$exif = exif_read_data($filename);

		if (!isset($exif['GPSLatitudeRef'])) {
			return false;
		}

		//get the Hemisphere multiplier
		$LatM = 1;
		$LongM = 1;
		if ($exif["GPSLatitudeRef"] == 'S') {
			$LatM = -1;
		}
		if ($exif["GPSLongitudeRef"] == 'W') {
			$LongM = -1;
		}

		//get the GPS data
		$gps = array();
		$gps['LatDegree'] = $exif["GPSLatitude"][0];
		$gps['LatMinute'] = $exif["GPSLatitude"][1];
		$gps['LatgSeconds'] = $exif["GPSLatitude"][2];
		$gps['LongDegree'] = $exif["GPSLongitude"][0];
		$gps['LongMinute'] = $exif["GPSLongitude"][1];
		$gps['LongSeconds'] = $exif["GPSLongitude"][2];

		//convert strings to numbers
		foreach ($gps as $key => $value) {
			$pos = strpos($value, '/');
			if ($pos !== false) {
				$temp = explode('/', $value);
				$gps[$key] = $temp[0] / $temp[1];
			}
		}

		//calculate the decimal degree
		$result = array();
		$result['lat'] = $LatM * ($gps['LatDegree'] + ($gps['LatMinute'] / 60) + ($gps['LatgSeconds'] / 3600));
		$result['lng'] = $LongM * ($gps['LongDegree'] + ($gps['LongMinute'] / 60) + ($gps['LongSeconds'] / 3600));

		return $result;
	}

	/**
	 * Get file extension
	 * 
	 * This way is faster that pathinfo($filename,PATHINFO_EXTENSION);
	 * Warning : it fails on a path with dots like /my.folder/some_sub_folder
	 * 
	 * @param string $file
	 * @return string
	 */
	public static function file_extension($file) {
		return strtolower(substr(strrchr($file, '.'), 1));
	}

	/**
	 * Returns the mime type of a file. Returns false if the mime type is not found.
	 *
	 * @param string Full path to the file
	 * @return string
	 */
	public static function mimetype($file) {
		$extension = self::file_extension($file);

		//use hardcoded table (faster and more reliable for css files for instance)
		$mime = isset(self::$mime_types[$extension]) ? self::$mime_types[$extension] : false;

		//if we don't find it, try to discover
		if (!$mime && function_exists('finfo_open')) {
			// Get mime using the file information functions
			$info = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($info, $file);
			finfo_close($info);
		}
		return $mime;
	}

	/**
	 * Empty a directory
	 * 
	 * @param string $dir
	 * @param bool $del
	 * @return void 
	 */
	static function empty_dir($dir, $del = false) {
		if (!$dh = @opendir($dir))
			return;
		while (false !== ($obj = readdir($dh))) {
			if ($obj == '.' || $obj == '..')
				continue;
			if (!@unlink($dir . '/' . $obj))
				self::empty_dir($dir . '/' . $obj, true);
		}

		if ($del) {
			@rmdir($dir);
		}
		closedir($dh);
	}

	/**
	 * Forces a file to be downloaded
	 *
	 * @param string $file Full path to file
	 * @param string $content_type (optional) Content type of the file
	 * @param string $filename (optional) Filename of the download
	 */
	static function file_download($file, $content_type = null, $filename = null) {
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

	/* string helpers */

	/**
	 * Limit a number or a string
	 * 
	 * @param string $value
	 * @param int $limit
	 * @return string
	 */
	static function limit($value, $limit) {
		if (is_numeric($value)) {
			if ($value > $limit) {
				return $limit;
			}
		} elseif (is_string($value)) {
			$value = strip_tags($value);
			$value_parts = explode(' ', $value);
			$value_limited = '';
			foreach ($value_parts as $part) {
				if (strlen($value_limited) + strlen($part) < $limit) {
					$value_limited .= $part;
				} else {
					break;
				}
			}
			if ($value_limited != $value) {
				return $value_limited . '...';
			}
		}

		return $value;
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
	 * UTF8 safe htmlentities
	 * 
	 * @param string $str
	 * @return string 
	 */
	static function encode($str) {
		return htmlentities($str, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Converts a string to UTF-8.
	 *
	 * @param string String to convert
	 * @return string
	 */
	public static function to_utf8($str) {
		return mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
	}

	/**
	 * Encrypt a string
	 * @param string $str
	 * @return string
	 */
	static function encrypt($str) {
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, 'UNDERSCORE', $str, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
	}

	/**
	 * Decrypt a string
	 * @param string $str
	 * @return string
	 */
	static function decrypt($str) {
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, 'UNDERSCORE', base64_decode($str), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}

	/**
	 * Check if a string only contains ascii characters.
	 *
	 * @param string String to be tested
	 * @return bool
	 */
	public static function is_ascii($str) {
		return !preg_match('/[^\x00-\x7F]/', $str);
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

		// should always return something
		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}

	/**
	 * Generate a strong password with at least a lower case letter, an uppercase letter,
	 * one digit and one special character.
	 * 
	 * The generated password does not contain any ambigous character such as i, l, 1, o, 0.
	 * 
	 * @param int $length
	 * @param bool $add_dashes
	 * @param string $available_sets
	 * @return string 
	 */
	public static function generate_password($length = 9, $add_dashes = false, $available_sets = 'luds') {
		$sets = array();
		if (strpos($available_sets, 'l') !== false)
			$sets[] = 'abcdefghjkmnpqrstuvwxyz';
		if (strpos($available_sets, 'u') !== false)
			$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		if (strpos($available_sets, 'd') !== false)
			$sets[] = '23456789';
		if (strpos($available_sets, 's') !== false)
			$sets[] = '!@#$%&*?';

		$all = '';
		$password = '';
		foreach ($sets as $set) {
			$password .= $set[array_rand(str_split($set))];
			$all .= $set;
		}

		$all = str_split($all);
		for ($i = 0; $i < $length - count($sets); $i++) {
			$password .= $all[array_rand($all)];
		}

		$password = str_shuffle($password);

		if (!$add_dashes) {
			return $password;
		}

		$dash_len = floor(sqrt($length));
		$dash_str = '';
		while (strlen($password) > $dash_len) {
			$dash_str .= substr($password, 0, $dash_len) . '-';
			$password = substr($password, $dash_len);
		}
		$dash_str .= $password;
		return $dash_str;
	}

	/**
	 * Check password strength (1 to 5)
	 * 
	 * @param string $password
	 * @return int 
	 */
	function check_password_strength($password) {
		$score = 1;

		if (strlen($pwd) < 1) {
			return $strength[0];
		}
		if (strlen($pwd) < 4) {
			return $strength[1];
		}

		if (strlen($pwd) >= 8) {
			$score++;
		}
		if (strlen($pwd) >= 10) {
			$score++;
		}

		if (preg_match("/[a-z]/", $pwd) && preg_match("/[A-Z]/", $pwd)) {
			$score++;
		}
		if (preg_match("/[0-9]/", $pwd)) {
			$score++;
		}
		if (preg_match("/.[!,@,#,$,%,^,&,*,?,_,~,-,£,(,)]/", $pwd)) {
			$score++;
		}

		return $score;
	}

	/**
	 * Generate a random string of given length
	 * @param type $length
	 * @return string 
	 */
	static function random_string($length = '10') {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$alphabet_length = strlen($alphabet);
		$output = '';

		for ($i = 0; $i < $length; $i++) {
			$output .= $alphabet[rand(0, $alphabet_length - 1)];
		}

		return $output;
	}

	/**
	 * Hash a password, gives a random hash
	 * 
	 * @param string $password
	 * @param string $salt
	 * @return string
	 */
	static function password_hash($password, $salt = null) {
		if (!$salt) {
			$salt = self::random_string(10);
		}
		$sha1 = sha1($salt . $password);
		for ($i = 0; $i < 1000; $i++) {
			$sha1 = sha1($sha1 . (($i % 2 == 0) ? $password : $salt));
		}
		return $salt . '#' . $sha1;
	}

	/**
	 * Check if a password is valid
	 * 
	 * @param string $password
	 * @param string $hash
	 * @return bool 
	 */
	static function check_password_hash($password, $hash) {
		$salt = substr($hash, 0, 10);
		if (self::password_hash($password, $salt) == $hash) {
			return true;
		}
		return false;
	}

	/**
	 * Pluralizes English nouns.
	 *
	 * @param    string    $word    English noun to pluralize
	 * @return string Plural noun
	 */
	static function pluralize($word) {
		$rules = array(
			'([ml])ouse$' => '\1ice',
			'(media|info(rmation)?|news)$' => '\1',
			'(phot|log|vide)o$' => '\1os',
			'^(q)uiz$' => '\1uizzes',
			'(c)hild$' => '\1hildren',
			'(p)erson$' => '\1eople',
			'(m)an$' => '\1en',
			'([ieu]s|[ieuo]x)$' => '\1es',
			'([cs]h)$' => '\1es',
			'(ss)$' => '\1es',
			'([aeo]l)f$' => '\1ves',
			'([^d]ea)f$' => '\1ves',
			'(ar)f$' => '\1ves',
			'([nlw]i)fe$' => '\1ves',
			'([aeiou]y)$' => '\1s',
			'([^aeiou])y$' => '\1ies',
			'([^o])o$' => '\1oes',
			's$' => 'ses',
			'(.)$' => '\1s'
		);

		foreach ($rules as $from => $to) {
			if (preg_match('#' . $from . '#iD', $word)) {
				$word = preg_replace('#' . $from . '#iD', $to, $word);
				break;
			}
		}

		return $word;
	}

	/**
	 * Singularizes English nouns.
	 *
	 * @param    string    $word    English noun to singularize
	 * @return string Singular noun.
	 */
	static function singularize($word) {
		$rules = array(
			'([ml])ice$' => '\1ouse',
			'(media|info(rmation)?|news)$' => '\1',
			'(q)uizzes$' => '\1uiz',
			'(c)hildren$' => '\1hild',
			'(p)eople$' => '\1erson',
			'(m)en$' => '\1an',
			'((?!sh).)oes$' => '\1o',
			'((?<!o)[ieu]s|[ieuo]x)es$' => '\1',
			'([cs]h)es$' => '\1',
			'(ss)es$' => '\1',
			'([aeo]l)ves$' => '\1f',
			'([^d]ea)ves$' => '\1f',
			'(ar)ves$' => '\1f',
			'([nlw]i)ves$' => '\1fe',
			'([aeiou]y)s$' => '\1',
			'([^aeiou])ies$' => '\1y',
			'(la)ses$' => '\1s',
			'(.)s$' => '\1'
		);

		foreach ($rules as $from => $to) {
			if (preg_match('#' . $from . '#iD', $word)) {
				$word = preg_replace('#' . $from . '#iD', $to, $word);
				break;
			}
		}

		return $word;
	}

	/**
	 * Returns given word as CamelCased
	 *
	 * Converts a word like "send_email" to "SendEmail". It
	 * will remove non alphanumeric character from the word, so
	 * "who's online" will be converted to "WhoSOnline"
	 *
	 * @param    string    $word    Word to convert to camel case
	 * @param bool $upper_camel_case
	 * @return string UpperCamelCasedWord
	 */
	static function camelize($word, $upper_camel_case = true) {
		$word = str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $word)));
		if ($upper_camel_case) {
			return $word;
		}
		return lcfirst($word);
	}

	/**
	 * Converts a word "into_it_s_underscored_version"
	 *
	 * Convert any "CamelCased" or "ordinary Word" into an
	 * "underscored_word".
	 *
	 * This can be really useful for creating friendly URLs.
	 *
	 * @access public
	 * @param    string    $word    Word to underscore
	 * @return string Underscored word
	 */
	static function underscorize($word) {
		return strtolower(preg_replace('/[^A-Z^a-z^0-9]+/', '_', preg_replace('/([a-zd])([A-Z])/', '1_2', preg_replace('/([A-Z]+)([A-Z][a-z])/', '1_2', $word))));
	}

	/**
	 * Returns a human-readable string from $word
	 *
	 * Returns a human-readable string from $word, by replacing
	 * underscores with a space, and by upper-casing the initial
	 * character by default.
	 *
	 * If you need to uppercase all the words you just have to
	 * pass 'all' as a second parameter.
	 *
	 * @param    string    $word    String to "humanize"
	 * @param    string    $uppercase    If set to 'all' it will uppercase all the words
	 * instead of just the first one.
	 * @return string Human-readable word
	 */
	static function humanize($word, $uppercase = '') {
		$uppercase = $uppercase == 'all' ? 'ucwords' : 'ucfirst';
		return $uppercase(str_replace('_', ' ', preg_replace('/_id$/', '', $word)));
	}

	/**
	 * Converts number to its ordinal English form.
	 *
	 * This method converts 13 to 13th, 2 to 2nd ...
	 *
	 * @param    integer    $number    Number to get its ordinal value
	 * @return string Ordinal representation of given string.
	 */
	static function ordinalize($number) {
		if (in_array(($number % 100), range(11, 13))) {
			return $number . 'th';
		} else {
			switch (($number % 10)) {
				case 1:
					return $number . 'st';
					break;
				case 2:
					return $number . 'nd';
					break;
				case 3:
					return $number . 'rd';
				default:
					return $number . 'th';
					break;
			}
		}
	}

	/**
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters
	 * with the ending if the text is longer than length.
	 *
	 * @param string  $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param string $ending If string, will be used as Ending and appended to the trimmed string.
	 * @param boolean $exact If false, $text will not be cut mid-word
	 * @return string Trimmed string.
	 */
	static function truncate($text, $length = 100, $ending = '...', $exact = true) {

		if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		$total_length = mb_strlen($ending);
		$open_tags = array();
		$truncate = '';
		preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
		foreach ($tags as $tag) {
			if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
				if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
					array_unshift($open_tags, $tag[2]);
				} else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $close_tag)) {
					$pos = array_search($close_tag[1], $open_tags);
					if ($pos !== false) {
						array_splice($open_tags, $pos, 1);
					}
				}
			}
			$truncate .= $tag[1];

			$content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
			if ($content_length + $total_length > $length) {
				$left = $length - $total_length;
				$entities_length = 0;
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
					foreach ($entities[0] as $entity) {
						if ($entity[1] + 1 - $entities_length <= $left) {
							$left--;
							$entities_length += mb_strlen($entity[0]);
						} else {
							break;
						}
					}
				}

				$truncate .= mb_substr($tag[3], 0, $left + $entities_length);
				break;
			} else {
				$truncate .= $tag[3];
				$total_length += $content_length;
			}
			if ($total_length >= $length) {
				break;
			}
		}

		if (!$exact) {
			$spacepos = mb_strrpos($truncate, ' ');
			if (isset($spacepos)) {
				$bits = mb_substr($truncate, $spacepos);
				preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
				if (!empty($droppedTags)) {
					foreach ($droppedTags as $closingTag) {
						if (!in_array($closingTag[1], $open_tags)) {
							array_unshift($open_tags, $closingTag[1]);
						}
					}
				}
				$truncate = mb_substr($truncate, 0, $spacepos);
			}
		}

		$truncate .= $ending;

		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}

		return $truncate;
	}

	/**
	 * Calculate the distance using Spherical Law of Cosines
	 * @link http://sgowtham.net/blog/2009/08/04/php-calculating-distance-between-two-locations-given-their-gps-coordinates/
	 * @param float $lat1
	 * @param float $lon1
	 * @param float $lat2
	 * @param float $lon2
	 * @param string $unit K = Kilometers (default), N = Nautical miles, M = Miles
	 * @return int
	 */
	static function distance($lat1, $lon1, $lat2, $lon2, $unit = 'K') {
		if ($lat1 == $lat2 && $lon1 == $lon2)
			return 0;
		$theta = $lon1 - $lon2;
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
			return ($miles * 1.609344);
		} else if ($unit == "N") {
			return ($miles * 0.8684);
		} else {
			return $miles;
		}
	}

	static function distance_google($lat1, $lon1, $lat2, $lon2) {
		$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?sensor=false&mode=driving&origins=' . $lat1 . ',' . $lon1 . '&destinations=' . $lat2 . ',' . $lon2;

		$data = file_get_contents($url);
		$data = json_decode($data, true);

		if ($data) {
			if (isset($data['rows'][0]['elements'][0]['distance']['text'])) {
				return $data['rows'][0]['elements'][0]['distance']['text'];
			}
		}
		return '';
	}

	/**
	 * Highlight words in a string
	 * 
	 * @param string $str
	 * @param string|array $words
	 * @param string $color
	 * @return string 
	 */
	static function highlight($str, $words, $color = 'yellow') {
		$words = self::arrayify($words);

		if (empty($words) || !is_string($str)) {
			return false;
		}

		$str = implode('|', $words);
		return preg_replace('@\b(' . $words . ')\b@si', '<strong style="background-color:' . $color . '">$1</strong>', $str);
	}

	/**
	 * Wrap json decode and throw exception on error
	 * 
	 * @param string $json
	 * @param bool $assoc
	 * @return array|object
	 */
	static function json_decode($json, $assoc = false) {
		$result = json_decode($json, $assoc);
		switch (json_last_error()) {
			case JSON_ERROR_DEPTH:
				$error = ' - Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = ' - Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$error = ' - Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				$error = ' - Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				$error = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			case JSON_ERROR_NONE:
			default:
				$error = '';
		}
		if (!empty($error)) {
			throw new Exception('JSON Error: ' . $error);
		}

		return $result;
	}

	/* array helpers */

	/**
	 * Get a value from array, allowing dot notation
	 * 
	 * @param array $array
	 * @param string $index
	 * @param mixed $default
	 * @return mixed 
	 */
	static function array_path(array $array, $index, $default = null) {
		$loc = &$array;
		foreach (explode('.', $index) as $step) {
			if (isset($loc[$step])) {
				$loc = &$loc[$step];
			} else {
				return $default;
			}
		}
		return $loc;
	}

	/**
	 * Get a value from an array
	 * 
	 * @param array $array
	 * @param string $index
	 * @param mixed $default 
	 */
	static function array_get(array $array, $index, $default = null) {
		if (isset($array[$index])) {
			return $array[$index];
		}
		return $default;
	}

	/**
	 * Set a value in an array, allowing dot notation
	 * 
	 * @param array $array
	 * @param string $index
	 * @param mixed $value
	 * @return mixed 
	 */
	static function array_set(array $array, $index, $value) {
		$loc = &$array;
		foreach (explode('.', $index) as $step) {
			$loc = &$loc[$step];
		}
		return $loc = $value;
	}

	/**
	 * Groups/splits an array by the key given. Can supply additional parameters beyond the first key
	 * for additional groupins.
	 *
	 * @author Jake Zatecky
	 *
	 * @param array $arr The array to have grouping performed on.
	 * @param mixed $key The key to group/split by.
	 *
	 * @return array
	 */
	static function array_group_by($arr, $key) {

		if (!is_array($arr)) {
			trigger_error("array_group_by(): The first argument should be an array", E_USER_ERROR);
		}  // End if

		if (!is_string($key) &&
				!is_int($key) &&
				!is_float($key)) {
			trigger_error("array_group_by(): The key should be a string or integer", E_USER_ERROR);
		}  // End if

		$newArr = array();

		// Load the new array splitting by the target key
		foreach ($arr as $value) {
			$arrKey = $value [$key];
			$newArr [$arrKey][] = $value;
		}  // End foreach
		// Recursively build a nested grouping if more parameters are supplied
		// Build for each previously grouped values, slicing off the new split parameters for each call
		if (func_num_args() > 2) {

			$args = func_get_args();

			foreach ($newArr as $key => $value) {
				$parms = array_merge(array($value), array_slice($args, 2, func_num_args()));
				$newArr [$key] = call_user_func_array(array(__CLASS__, "array_group_by"), $parms);
			}  // End foreach
		}  // End if

		return $newArr;
	}

// End array_group_by

	static function array_rand(array $arr) {
		return $arr[array_rand($arr) - 1];
	}

	/**
	 * Return a list of elements matching the index
	 * 
	 * @param array $array
	 * @param string $index
	 * @return array 
	 */
	static function array_list($array, $index) {
		$list = array();
		if (empty($array)) {
			return $list;
		}
		foreach ($array as $row) {
			if (is_array($row)) {
				if (isset($row[$index])) {
					$list[] = $row[$index];
				}
			} elseif (is_object($row)) {
				if (isset($row->$index)) {
					$list[] = $row->$index;
				}
			}
		}
		return $list;
	}

	/**
	 * Is associative array
	 * 
	 * @param array $arr
	 * @return bool
	 */
	static function array_is_assoc(array $array) {
		//don't use array_keys or array_values because it takes a lot of memory for large arrays
		foreach ($array as $k => $v) {
			if (!is_int($k)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Convert a var to array
	 * 
	 * @param mixed $var
	 * @param string $delimiter (for string parameters)
	 * @param bool $trim (for string parameters)
	 * @return array 
	 */
	static function arrayify($var, $delimiter = ',', $trim = true) {
		if (is_array($var)) {
			return $var;
		}
		if (empty($var)) {
			return array();
		}
		if (is_string($var)) {
			$array = explode($delimiter, $var);
			if ($trim) {
				array_walk($array, 'trim');
			}
			return $array;
		}
		if (is_object($var)) {
			if (method_exists($var, 'to_array')) {
				return $var->to_array();
			}
			return get_object_vars($var);
		}
		throw new Exception('Arrayify does not support objects of type ' . gettype($var));
	}

	/**
	 * Convert a var to a string
	 * 
	 * @param mixed $var
	 * @param string $glue
	 * @return string
	 */
	static function stringify($var, $glue = ',') {
		if (empty($var)) {
			return '';
		}
		if (is_bool($var)) {
			if ($var) {
				return 'true';
			}
			return 'false';
		}
		if (is_string($var) || is_int($var) || is_float($var)) {
			return (string) $var;
		}
		if (is_array($var)) {
			$string = implode($glue, $var);
			return $string;
		}
		if (is_object($var)) {
			if (method_exists($var, '__toString')) {
				return (string) $var;
			}
			throw new Exception('Object does not have a __toString method');
		}
		throw new Exception('Stringify does not support objects of type ' . gettype($var));
	}

	/* webservices */

	/**
	 * Geocode an address using google api
	 * 
	 * @param string $address
	 * @return array
	 */
	static function geocode($address) {
		if (!is_string($address)) {
			throw new Exception("All Addresses must be passed as a string");
		}
		$url = sprintf('http://maps.google.com/maps?output=js&q=%s', rawurlencode($address));
		$result = false;
		if ($result = file_get_contents($url)) {
			if (strpos($result, 'errortips') > 1 || strpos($result, 'Did you mean:') !== false) {
				return false;
			}
			preg_match('!center:\s*{lat:\s*(-?\d+\.\d+),lng:\s*(-?\d+\.\d+)}!U', $result, $match);
			$coords['lat'] = $match[1];
			$coords['lng'] = $match[2];
		}
		return $coords;
	}

	static function pretty_json($json) {

		$result = '';
		$pos = 0;
		$strLen = strlen($json);
		$indentStr = '  ';
		$newLine = "\n";
		$prevChar = '';
		$outOfQuotes = true;

		for ($i = 0; $i <= $strLen; $i++) {

			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;

				// If this character is the end of an element,
				// output a new line and indent the next line.
			} else if (($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos--;
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}

			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}

			$prevChar = $char;
		}

		return $result;
	}

	static function get_max_upload_size() {
		$max_upload = (int) (ini_get('upload_max_filesize'));
		$max_post = (int) (ini_get('post_max_size'));
		$memory_limit = (int) (ini_get('memory_limit'));
		return min($max_upload, $max_post, $memory_limit);
	}

	/**
	 * Use Google Geocoding API to do a reverse address lookup from GPS coordinates
	 * 
	 * @param float $lat
	 * @param float $lng
	 * @return string
	 */
	static function reverse_geocode($lat, $lng) {
		$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&sensor=false";
		$data = file($url);
		foreach ($data as $line_num => $line) {
			if (false != strstr($line, "\"formatted_address\"")) {
				return substr(trim($line), 23, -2);
			}
		}
		return false;
	}

	/**
	 * Make a tiny url
	 * 
	 * @param string $url 
	 */
	static function tinyurl($url) {
		return file_get_contents("http://tinyurl.com/api-create.php?url=" . $url);
	}

	/**
	 * Convert a tiny url to a norma url
	 * 
	 * @param string $tinyurl 
	 */
	static function untinyurl($tinyurl) {
		if ($fp = fsockopen("tinyurl.com", 80, $errno, $errstr, 30)) {
			if ($fp) {
				fputs($fp, "HEAD /$tinyurl HTTP/1.0\r\nHost: tinyurl.com\r\n\r\n");
				$headers = '';
				while (!feof($fp)) {
					$headers .= fgets($fp, 128);
				}
				fclose($fp);
			}

			$arr1 = explode("Location:", $headers);
			$arr = explode("\n", trim($arr1[1]));
			echo trim($arr[0]);
		}
	}

	/**
	 * Autolinkable twitter links
	 * 
	 * @param string $ret
	 * @return string 
	 */
	static function twitterify($ret) {
		$ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1\\2", $ret);
		$ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1\\2", $ret);
		$ret = preg_replace("/@(\w+)/", "@\\1", $ret);
		$ret = preg_replace("/#(\w+)/", "#\\1", $ret);
		return $ret;
	}

	/**
	 * Get a twitter feed for a given user
	 * 
	 * @param string $username
	 * @return array
	 */
	static function twitter_feed($username, $num = 10) {
		$json = file_get_contents("http://twitter.com/status/user_timeline/tareq_cse.json?count=" . $num, true); //getting the file content
		$decode = json_decode($json, true); //getting the file content as array
		return $decode;
	}

	/**
	 * Get a twitter feed for a given hashtag
	 * 
	 * @param string $hashtag
	 * @return array
	 */
	static function twitter_feed_hashtag($hashtag) {
		$json = file_get_contents("http://search.twitter.com/search.json?rpp=100&q=%23" . $hashtag);
		$decode = json_decode($json, true); //getting the file content as array
		return $decode;
	}

	/**
	 * Get tweet count
	 * @param string $url
	 * @return int
	 */
	static function tweets_count($url) {

		$json_string = file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url=' . $url);
		$json = json_decode($json_string, true);

		return intval($json['count']);
	}

	/**
	 * Get facebook likes count
	 * @param string $url
	 * @return string
	 */
	static function likes_count($url) {

		$json_string = file_get_contents('http://graph.facebook.com/?ids=' . $url);
		$json = json_decode($json_string, true);

		return intval($json[$url]['shares']);
	}

	/**
	 * Get google +1 count
	 * @param string $url
	 * @return int
	 */
	static function plusones_count($url) {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://clients6.google.com/rpc");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		$curl_results = curl_exec($curl);
		curl_close($curl);

		$json = json_decode($curl_results, true);

		return intval($json[0]['result']['metadata']['globalCounts']['count']);
	}

	/**
	 * Mail merge string to a new file. The patterns should be ${key}
	 * 
	 * @param string $template
	 * @param string $output
	 * @param array $data
	 * @return boolean
	 */
	static function mail_merge($template, $output, array $data = array()) {
		if (!copy($template, $output)) {
			// make a duplicate so we dont overwrite the template
			return false; // could not duplicate template
		}
		$zip = new ZipArchive();
		if ($zip->open($output, ZIPARCHIVE::CHECKCONS) !== TRUE) {
			return false; // probably not a docx file
		}
		$file = substr($template, -4) == '.odt' ? 'content.xml' : 'word/document.xml';
		$content = $zip->getFromName($file);
		foreach ($data as $key => $value) {
			$content = str_replace('${' . $key . '}', $value, $content);
		}
		$zip->deleteName($file);
		$zip->addFromString($file, $content);
		$zip->close();
		echo $content;
		return true;
	}

}

/**
 * Extend datetime to allow to use it as a string
 */
class _datetime extends DateTime {

	public $type = 'datetime';

	/**
	 * Return Date in ISO8601 format
	 *
	 * @return String
	 */
	public function __toString() {
		return $this->db_format();
	}

	/**
	 * Return difference between $this and $now
	 *
	 * @param Datetime|String $date
	 * @return DateInterval
	 */
	public function diff($date = 'now', $absolute = null) {
		if (!($date instanceOf DateTime)) {
			$date = new DateTime($date);
		}
		return parent::diff($date);
	}

	/**
	 * Check if date is between to date
	 * 
	 * @param string|int $start
	 * @param string|int $end
	 * @return boolean
	 */
	public function is_between($start, $end) {
		$t = $this->format('U');
		return self::is_date_between($t, $start, $end);
	}

	/**
	 * Check if date is between to date
	 * 
	 * @param string|int $date
	 * @param string|int $start
	 * @param string|int $end
	 * @return boolean
	 */
	public static function is_date_between($date, $start, $end) {
		if (!is_int($date)) {
			$date = strtotime((string) $date);
		}
		if (!is_int($start)) {
			$start = strtotime((string) $start);
		}
		if (!is_int($end)) {
			$end = strtotime((string) $end);
		}
		if ($date >= $start && $date <= $end) {
			return true;
		}
		return false;
	}

	/**
	 *  Get all weeks of a year as follows
	 * 
	 * array(
	 * 	array('num' => x, 'start' => x, 'end' => x);
	 * )
	 * @param type $year
	 * @return type
	 */
	public static function weeks($year = null) {
		if (!$year) {
			$year = date('Y');
		}

		$first_day_of_year = mktime(0, 0, 0, 1, 1, $year);
		$first_thursday = strtotime('thursday', $first_day_of_year); //needed to get first week as ISO format
		$next_monday = strtotime(date("Y-m-d", $first_thursday) . " - 3 days");
		$next_sunday = strtotime('sunday', $next_monday);
		$i = 1; //weeks counter
		$weeks = array();
		while (date('Y', $next_monday) == $year) {
			$weeks[] = array('num' => $i, 'start' => date('Y-m-d', $next_monday), 'end' => date('Y-m-d', $next_sunday));
			$i++;
			$next_monday = strtotime('+1 week', $next_monday);
			$next_sunday = strtotime('+1 week', $next_sunday);
		}
		return $weeks;
	}

	/**
	 * Format for database
	 * 
	 * @return string 
	 */
	public function db_format() {
		switch ($this->type) {
			case 'datetime' :
				$format = 'Y-m-d H:i:s';
				break;
			case 'date' :
				$format = 'Y-m-d';
				break;
			case 'time' :
				$format = 'H:i:s';
				break;
		}
		return $this->format($format);
	}

	/**
	 * Now string for db
	 * 
	 * @param bool $only_day
	 * @return string 
	 */
	public static function now_db($only_day = false) {
		if ($only_day) {
			return date('Y-m-d');
		}
		return date('Y-m-d H:i:s');
	}

	/**
	 * Give the last day of month 
	 * 
	 * @param int $month
	 * @param int $year
	 * @return string 
	 */
	public static function last_day_of_month($month = null, $year = null) {
		if (!$year) {
			$year = date('Y');
		}
		if (!$month) {
			$month = date('m');
		}
		$day = date('d');
		$date = $year . '-' . $month . '-' . $day;
		//t returns the number of days in the month of a given date
		return date("Y-m-t", strtotime($date));
	}

	public static function convert($format, $date) {
		if (empty($date)) {
			return '';
		}
		return date($format, strtotime($date));
	}

	/**
	 * Return Age in Years
	 *
	 * @param Datetime|String $date
	 * @return Integer
	 */
	public function age($date = 'now') {
		return $this->diff($date)->format('%y');
	}

	/**
	 * Returns the approximate difference in time, discarding any unit of measure but the least specific.
	 * 
	 * The output will read like:
	 * 
	 *  - "This date is `{return value}` the provided one" when a date it passed
	 *  - "This date is `{return value}`" when no date is passed and comparing with today
	 * 
	 * Examples of output for a date passed might be:
	 * 
	 *  - `'2 days after'`
	 *  - `'1 year before'`
	 *  - `'same day'`
	 * 
	 * Examples of output for no date passed might be:
	 * 
	 *  - `'2 days from now'`
	 *  - `'1 year ago'`
	 *  - `'today'`
	 * 
	 * You would never get the following output since it includes more than one unit of time measurement:
	 * 
	 *  - `'3 weeks and 1 day'`
	 *  - `'1 year and 2 months'`
	 * 
	 * Values that are close to the next largest unit of measure will be rounded up:
	 * 
	 *  - `6 days` would be represented as `1 week`, however `5 days` would not
	 *  - `29 days` would be represented as `1 month`, but `21 days` would be shown as `3 weeks`
	 * 
	 * @param  object|string|integer $date  The date to create the difference with, now by default
	 * @param  boolean                     $simple      When `true`, the returned value will only include the difference in the two dates, but not `from now`, `ago`, `after` or `before`
	 * @return string  The fuzzy difference in time between the this date and the one provided
	 */
	public function fuzzy_diff($date = 'now', $simple = false) {
		$relative_to_now = false;
		if ($date == 'now') {
			$relative_to_now = true;
		}
		if (!($date instanceOf DateTime)) {
			$date = new DateTime($date);
		}

		$diff = $this->format('U') - $date->format('U');
		$result = '';

		if (abs($diff) < 86400) {
			if ($relative_to_now) {
				return 'today';
			}
			return 'same day';
		}

		$break_points = array(
			/* 5 days      */
			432000 => array(86400, 'day', 'days'),
			/* 3 weeks     */
			1814400 => array(604800, 'week', 'weeks'),
			/* 9 months    */
			23328000 => array(2592000, 'month', 'months'),
			/* largest int */
			2147483647 => array(31536000, 'year', 'years')
		);

		foreach ($break_points as $break_point => $unit_info) {
			if (abs($diff) > $break_point) {
				continue;
			}

			$unit_diff = round(abs($diff) / $unit_info[0]);
			$units = ($unit_diff == 1) ? $unit_info[1] : $unit_info[2];
			break;
		}

		if ($simple) {
			return vsprintf('%1$s %2$s', array($unit_diff, $units));
		}

		if ($relative_to_now) {
			if ($diff > 0) {
				return vsprintf('%1$s %2$s from now', array($unit_diff, $units));
			}

			return vsprintf('%1$s %2$s ago', array($unit_diff, $units));
		}

		if ($diff > 0) {
			return vsprintf('%1$s %2$s after', array($unit_diff, $units));
		}

		return vsprintf('%1$s %2$s before', array($unit_diff, $units));
	}

	/* static helpers */

	static function seconds_to_time($secs) {
		$times = array(3600, 60, 1);
		$time = '';
		$tmp = '';
		for ($i = 0; $i < 3; $i++) {
			$tmp = floor($secs / $times[$i]);
			if ($tmp < 1) {
				$tmp = '00';
			} elseif ($tmp < 10) {
				$tmp = '0' . $tmp;
			}
			$time .= $tmp;
			if ($i < 2) {
				$time .= ':';
			}
			$secs = $secs % $times[$i];
		}
		return $time;
	}

	/**
	 * Translate a day to its abbreviation or the opposite
	 * 
	 * @param string $day
	 * @return string 
	 */
	static function day_abbrev($day = null) {
		if (empty($day)) {
			return false;
		}

		$days = self::week_days();

		$day = strtolower($day);

		$i = 0;
		foreach ($days as $k => $v) {
			$i++;
			if ($day == $i) {
				return $k;
			}
			if ($day == $v) {
				return $k;
			} elseif ($day == $k) {
				return ucfirst($v);
			}
		}
		return false;
	}

	/**
	 * Return an array of days of the week like 1 => monday.... 
	 * 
	 * @param bool $abbrev_as_key use abbreviation (mon) instead of number (1)
	 * @return array
	 */
	static function week_days($abbrev_as_key = true) {
		if ($abbrev_as_key) {
			return array(
				'mon' => 'monday',
				'tue' => 'tuesday',
				'wed' => 'wednesday',
				'thu' => 'thursday',
				'fri' => 'friday',
				'sat' => 'saturday',
				'sun' => 'sunday'
			);
		}
		return array(
			'1' => 'monday',
			'2' => 'tuesday',
			'3' => 'wednesday',
			'4' => 'thursday',
			'5' => 'friday',
			'6' => 'saturday',
			'7' => 'sunday'
		);
	}

	/**
	 * Return an array of days of weeks as abbrev
	 * @param bool $day_as_key use day (Monday) instead of num (1)
	 * @return 
	 */
	static function week_days_abbrev($day_as_key = false) {
		if ($day_as_key) {
			return array(
				'Monday' => 'mon',
				'Tuesday' => 'tue',
				'Wednesday' => 'wed',
				'Thursday' => 'thu',
				'Friday' => 'fri',
				'Saturday' => 'sat',
				'Sunday' => 'sun'
			);
		}
		return array(
			'1' => 'mon',
			'2' => 'tue',
			'3' => 'wed',
			'4' => 'thu',
			'5' => 'fri',
			'6' => 'sat',
			'7' => 'sun'
		);
	}

	/* static factory */

	static function createFromFormat($f, $t, $tz = null) {
		if (!$tz) {
			$tz = new DateTimeZone(date_default_timezone_get());
		}
		$dt = parent::createFromFormat($f, $t, $tz);
		if (!$dt) {
			return null;
		}
		return new static($dt->format('Y-m-d H:i:s e'));
	}

	static function from_datetime($time = null) {
		if (empty($time) || $time == '0000-00-00 00:00:00') {
			return null;
		}
		return static::createFromFormat('Y-m-d H:i:s', $time);
	}

	static function from_date($time = null) {
		if (empty($time) || $time == '0000-00-00') {
			return null;
		}
		$dt = self::createFromFormat('Y-m-d', $time);
		if (!$dt) {
			return null;
		}
		$dt->type = 'date';
		return $dt;
	}

	static function from_time($time = null) {
		if (empty($time) || $time == '00:00:00') {
			return null;
		}
		$dt = self::createFromFormat('H:i:s', $time);
		if (!$dt) {
			return null;
		}
		$dt->type = 'time';
		return $dt;
	}

}

class _array extends ArrayObject {

	/**
	 * Alias of getArrayCopy()
	 * @return array
	 */
	function to_array() {
		return $this->getArrayCopy();
	}

	/**
	 * Shortcut for array_ methods
	 * 
	 * @method array_change_key_case — Changes all keys in an array
	 * @method array_chunk — Split an array into chunks
	 * @method array_combine — Creates an array by using one array for keys and another for its values
	 * @method array_count_values — Counts all the values of an array
	 * @method array_diff_assoc — Computes the difference of arrays with additional index check
	 * @method array_diff_key — Computes the difference of arrays using keys for comparison
	 * @method array_diff_uassoc — Computes the difference of arrays with additional index check which is performed by a user supplied callback function
	 * @method array_diff_ukey — Computes the difference of arrays using a callback function on the keys for comparison
	 * @method array_diff — Computes the difference of arrays
	 * @method array_fill_keys — Fill an array with values, specifying keys
	 * @method array_fill — Fill an array with values
	 * @method array_filter — Filters elements of an array using a callback function
	 * @method array_flip — Exchanges all keys with their associated values in an array
	 * @method array_intersect_assoc — Computes the intersection of arrays with additional index check
	 * @method array_intersect_key — Computes the intersection of arrays using keys for comparison
	 * @method array_intersect_uassoc — Computes the intersection of arrays with additional index check, compares indexes by a callback function
	 * @method array_intersect_ukey — Computes the intersection of arrays using a callback function on the keys for comparison
	 * @method array_intersect — Computes the intersection of arrays
	 * @method array_key_exists — Checks if the given key or index exists in the array
	 * @method array_keys — Return all the keys or a subset of the keys of an array
	 * @method array_map — Applies the callback to the elements of the given arrays
	 * @method array_merge_recursive — Merge two or more arrays recursively
	 * @method array_merge — Merge one or more arrays
	 * @method array_multisort — Sort multiple or multi-dimensional arrays
	 * @method array_pad — Pad array to the specified length with a value
	 * @method array_pop — Pop the element off the end of array
	 * @method array_product — Calculate the product of values in an array
	 * @method array_push — Push one or more elements onto the end of array
	 * @method array_rand — Pick one or more random entries out of an array
	 * @method array_reduce — Iteratively reduce the array to a single value using a callback function
	 * @method array_replace_recursive — Replaces elements from passed arrays into the first array recursively
	 * @method array_replace — Replaces elements from passed arrays into the first array
	 * @method array_reverse — Return an array with elements in reverse order
	 * @method array_search — Searches the array for a given value and returns the corresponding key if successful
	 * @method array_shift — Shift an element off the beginning of array
	 * @method array_slice — Extract a slice of the array
	 * @method array_splice — Remove a portion of the array and replace it with something else
	 * @method array_sum — Calculate the sum of values in an array
	 * @method array_udiff_assoc — Computes the difference of arrays with additional index check, compares data by a callback function
	 * @method array_udiff_uassoc — Computes the difference of arrays with additional index check, compares data and indexes by a callback function
	 * @method array_udiff — Computes the difference of arrays by using a callback function for data comparison
	 * @method array_uintersect_assoc — Computes the intersection of arrays with additional index check, compares data by a callback function
	 * @method array_uintersect_uassoc — Computes the intersection of arrays with additional index check, compares data and indexes by a callback functions
	 * @method array_uintersect — Computes the intersection of arrays, compares data by a callback function
	 * @method array_unique — Removes duplicate values from an array
	 * @method array_unshift — Prepend one or more elements to the beginning of an array
	 * @method array_values — Return all the values of an array
	 * @method array_walk_recursive — Apply a user function recursively to every member of an array
	 * @method array_walk — Apply a user function to every member of an array
	 * @param string $func
	 * @param string $argv
	 * @return mixed
	 */
	public function __call($func, $argv) {
		if (!is_callable($func) || substr($func, 0, 6) !== 'array_') {
			throw new BadMethodCallException(__CLASS__ . '->' . $func);
		}
		return call_user_func_array($func, array_merge(array($this->getArrayCopy()), $argv));
	}

	function apply($callback) {
		foreach ($this as $k => $v) {
			$this[$k] = $callback($v);
		}
	}

	function first() {
		return $this[0];
	}

	function last() {
		return $this[$this->count()];
	}

}
