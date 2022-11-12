<?php

namespace spaf\simputils\models\urls\processors;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\generic\BasicProtocolProcessor;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\UrlObject;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use function count;
use function explode;
use function preg_match;
use function preg_replace;
use function spaf\simputils\basic\bx;

class HttpProtocolProcessor extends BasicProtocolProcessor {

	const PROTO_HTTP = 'http';
	const PROTO_HTTPS = 'https';

	static bool $do_not_lower = false;
	static ?string $default_protocol = self::PROTO_HTTPS;
	static string $initial_parser_regexp_full =
		'#(?:([\w\d:_-]*)@)?(?:((?:[\w\d_.-]*)|(?:\[[a-f\d:]{3,40}\]))/?)?' .
		'(?::(\w{0,5}))?/?([\w\d/_.,+%-]*)(?:\?([\w\d/.,_&=+%-]*))?(?:\#([\w\d/_&=+%-]*))?$#S';
	static string $initial_parser_regexp_path =
		'#/?([\w\d/_.,+%-]*)(?:\?([\w\d/.,_&=+%-]*))?(?:\#([\w\d/_&=+%-]*))?$#S';
	static string $initial_parser_regexp_params =
		'#(?:\?([\w\d/_&=+%-]*))?(?:\#([\w\d/_&=+%-]*))?$#S';
	static string $default_host = 'localhost';
	static int $default_port = 80;

	static function supportedProtocols() {
		return PHP::box([static::PROTO_HTTP, static::PROTO_HTTPS]);
	}

	const PART_FULL = null;
	const PART_PATH = 'path';
	const PART_PARAMS = 'params';

	static function parse(UrlCompatible|string $value, ?string $protocol = null, $part = null) {
		$params_pre_parsed = null;

//		$protocol = null;
		$creds = null;
		$user = null;
		$pass = null;
		$host = null;
		$port = null;
		$path = null;
		$params = null;
		$sharpy = null;

		$value = preg_replace('#\s+#', '', $value);

		$m = [];
		if (preg_match('#^(/{2,})(.*)#S', $value, $m)) {
			$value = $m[2];
		}

		$regexp = match ($part) {
			static::PART_PATH => static::$initial_parser_regexp_path,
			static::PART_PARAMS => static::$initial_parser_regexp_params,
			default => static::$initial_parser_regexp_full,
		};

		$m = [];
		// NOTE Parsing URL
		preg_match($regexp, $value, $m);
		if ($part === static::PART_FULL) {
			if (!$protocol) {
				$protocol = static::$default_protocol;
			}
			$creds = $m[1] ?? null;
			$host = $m[2] ?: static::$default_host;
			$port = $m[3] ?: static::$default_port;
			$path = $m[4] ?? null;
			$params = $m[5] ?? null;
			$sharpy = $m[6] ?? null;
		} else if ($part === static::PART_PATH) {
			$path = $m[1] ?? null;
			$params = $m[2] ?? null;
			$sharpy = $m[3] ?? null;
//		} else if ($part === static::PART_PARAMS) {
//			pd($m, $value);
//			$path = $m[1] ?? null;
//			$params = $m[2] ?? null;
//			$sharpy = $m[3] ?? null;
		}

		if ($path) {
			$path_new = PHP::box()->pathAlike();
			foreach (explode('/', $path) as $key => $val) {
				if ($val) {
					$path_new[$key] = $val;
				}
			}
			$path = $path_new;
		}


		if ($creds) {
			$c = [];
			preg_match('#^([\w\d_-]*)(?::(.*))?#S', $creds, $c);
			$user = $c[1] ?? null;
			$pass = $c[2] ?? null;
		}

		if ($params) {
			$params_pre_parsed = bx(explode('&', $params));
		}

		$params_res = bx()->apply(
			separator: '&',
			joined_to_str: true,
			stretcher: true
		);
		if ($params_pre_parsed) {
			foreach ($params_pre_parsed as $item) {
				if ($item && Str::contains($item, '=')) {
					[$key, $val] = explode('=', $item);
					if ($key && $val) {
						$val = Str::contains($val, '%')?urldecode($val):$val;

						$params_res[$key] = $val;
					}
				}
			}
			$params = $params_res;
		}

		if ($host && !static::$do_not_lower) {
			$host = Str::lower($host);
		}

		return PHP::box([
			'protocol' => $protocol,
			'user' => $user,
			'pass' => $pass,
			'host' => $host,
			'port' => $port,
			'path' => $path,
			'params' => $params,
			'sharpy' => $sharpy,
		]);
	}

	private static function _stringify($url, bool $obfuscate, bool $relative) {
		$cred = '';
		if ($url->user) {
			$cred = "{$url->user}";
			if ($url->password) {
				if ($obfuscate) {
					$cred .= ":".DebugHide::$default_placeholder;
				} else {
					$cred .= ":{$url->password}";
				}
			}
			$cred .= '@';
		}

		$port = '';
		if ($url->port && $url->port !== 80 && $url->port !== 443 ) {
			$port = ":{$url->port}";
		}

		$path = preg_replace('#/+#S', '/', "/{$url->path}");

		$params = '';
		if ($url->params && count($url->params) > 0) {
			$params = $url->params->clone();
			foreach ($params as $key => $val) {
				$params[$key] = urlencode($val);
			}
			$params = "?{$params}";
		}

		$sharpy = '';
		if (!empty($url->data['sharpy']) && $url->data['sharpy']) {
			$sharpy = "#{$url->data['sharpy']}";
		}

		$rel_part = "{$path}{$params}{$sharpy}";
		if ($relative) {
			return $rel_part;
		}

		return "{$url->protocol}://{$cred}{$url->host}{$port}{$rel_part}";
	}

	static function generateForSystem(UrlObject $url): string {
		return static::_stringify($url, false, false);
	}

	static function generateForUser(UrlObject $url): string {
		return static::_stringify($url, true, false);
	}

	static function generateRelative(UrlObject $url): string {
		return static::_stringify($url, true, true);
	}

//	static function preParsing($host, $protocol = null): array {
//		// FIX  Code is mess, try to do not use it until fixed!
//		$m = bx();
//		preg_match('#^(?:/*)([a-zA-Z0-9:*~@._-]*):([0-9]{0,5})$#S', $host ?? '', $m);
//
//		pd($host, $m);
//
//		$port = null;
//		if ($m) {
//			$host = $m[1] ?? null;
//			$port = $m[2] ?? null;
//		}
//
//		$user = null;
//		$pass = null;
//
//		$host_tmp = null;
//		if ($host) {
//			$m2 = bx();
//			preg_match('#(?:^([a-zA-Z0-9:_-]*)(?:@(.*)))$#S', $host ?? '', $m2);
//
//			$host_tmp = $m2[2] ?? null;
//			$creds = $m2[1] ?? null;
//			if ($creds) {
//				$m3 = bx();
//				preg_match('#(?:^([a-zA-Z0-9_-]*)(?::(.*)))$#S', $creds ?? '', $m3);
//				$user = $m3[1] ?? null;
//				$pass = $m3[2] ?? null;
//				if (empty($user) && empty($pass)) {
//					$user = $creds;
//				}
////				pd($m3);
//			}
////			pd($m2, $host);
//		}
//		if ($host_tmp) {
//			$host = $host_tmp;
//		}
//
//		if ($host === $protocol) {
//			// FIX  Total mess.
//			// NOTE If the protocol equals the host, it means initial protocol was skipped,
//			//      and the hostname was caught as a protocol name by accident
//			$protocol = 'https';
//		}
//
////		pd([$protocol, $user, $pass, $host, $port]);
//
//		return [$protocol, $user, $pass, $host, $port];
//	}
//
//	static function isValid($host, $protocol = null): bool {
//		$supported = bx(['http', 'https']);
//		[$protocol, $user, $pass, $host, $port] = static::preParsing($host, $protocol);
//		return (!empty($protocol) && $supported->containsValue(Str::lower($protocol))) || !empty($host) || !empty($port);
//	}
//
//	/**
//	 *
//	 * TODO Code is really un-optimal!
//	 * @param UrlCompatible|string|Box|array $value        Host value
//	 * @param bool                           $is_preparsed If it was pre-parsed
//	 *
//	 * @return array
//	 */
//	function parse(UrlCompatible|string|Box|array $value, bool $is_preparsed = false, $data = null) {
//		// host, path, params, data
//		$proto = $this->_protocol;
//
//		if ($value instanceof UrlCompatible) {
//			return [
//				$value->getHost($proto),
//				$value->getPath($proto) ?? PHP::box(),
//				$value->getParams($proto) ?? PHP::box(),
//				$value->getData($proto) ?? PHP::box()
//			];
//		}
//
//		//
//
//		if (is_string($value)) {
//			if (Str::contains($value, '%')) {
//				$value = urldecode($value);
//			}
//		}
//
//		$host = null;
//		$path = null;
//		$params = PHP::box();
//		$data = $data ?? PHP::box();
//
//		$with_domain = $is_preparsed;
//		if (Str::startsWith($value, '//')) {
//			$with_domain = true;
//			$value = Str::removeStarting($value, '//');
//			$value = preg_replace('#/+#', '/', $value);
//		} else {
//			$value = preg_replace('#/+#', '/', $value);
//			$value = Str::removeStarting($value, '/');
//		}
//
//		$pre_res = Str::split($value, '?');
//		$res = Str::split($pre_res[0], '/');
//
//		if (isset($pre_res[1])) {
//			$post_res = Str::split($pre_res[1], '#');
//			if (isset($post_res[0])) {
//				// NOTE Args
//				$pairs = Str::split($post_res[0], '&');
//				foreach ($pairs as $pair) {
//					[$k, $v] = Str::split($pair, '=');
//					$params[$k] = $v;
//				}
//			}
//			if (isset($post_res[1])) {
//				$params['#'] = $post_res[1];
//			}
//		}
//
//		if ($with_domain && isset($res[0])) {
//			$host = preg_replace('#\s+#S', '', $res[0]);
//			unset($res[0]);
//		}
//		$path = $res;
//
//		return [$host, $path, $params, $data];
//	}
//
//	private function generateCommonUrl($host, $path, $params, $data, $is_relative = false) {
//		/** @var Box $path */
//		/** @var Box $params */
//		/** @var Box $data */
//		$path = $path->join('/');
//		$res_params = PHP::box();
//		$sharp = null;
//		foreach ($params as $k => $v) {
//			if ($k === '#') {
//				$sharp = $v;
//			} else {
//				$res_params[] = $k.'='.urlencode($v);
//			}
//		}
//		$params = $res_params->join('&');
//		if (!empty($sharp)) {
//			$sharp = "#{$sharp}";
//		}
//		if (!empty($params)) {
//			$params = "?{$params}";
//		}
//		$pre = '';
//		if (!$is_relative) {
//			$pre = "{$this->protocol}://{$host}/";
//		}
//
//		return "{$pre}{$path}{$params}{$sharp}";
//	}
//
//	function generateForSystem($host, $path, $params, $data): string {
//		return $this->generateCommonUrl($host, $path, $params, $data);
//	}
//
//	function generateForUser($host, $path, $params, $data): string {
//		return $this->generateCommonUrl($host, $path, $params, $data);
//	}
//
//	function generateRelative($host, $path, $params, $data): string {
//		return $this->generateCommonUrl($host, $path, $params, $data, true);
//	}
//
//	function getPort($obj): ?int {
//		return $obj?->data['port'] ?? 80;
//	}
//
//	function setPort($obj, ?int $val) {
//		/** @var \spaf\simputils\models\UrlObject $obj */
//		$obj->data['port'] = $val;
//	}
}
