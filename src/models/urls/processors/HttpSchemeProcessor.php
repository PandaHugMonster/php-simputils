<?php

namespace spaf\simputils\models\urls\processors;

use spaf\simputils\attributes\DebugHide;
use spaf\simputils\generic\BasicSchemeProcessor;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\UrlObject;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use function count;
use function explode;
use function is_null;
use function preg_match;
use function preg_replace;
use function spaf\simputils\basic\pd;

class HttpSchemeProcessor extends BasicSchemeProcessor {

	const SCHEME_HTTP = 'http';
	const SCHEME_HTTPS = 'https';

	const PROTO_HTTP = self::SCHEME_HTTP;
	const PROTO_HTTPS = self::SCHEME_HTTPS;

	static bool $do_not_lower = false;

	static ?string $default_protocol = self::PROTO_HTTPS;
	static ?string $default_scheme = self::SCHEME_HTTPS;

	static string $initial_parser_regexp_full =
		'#(?:([\w\d:_-]*)@)?(?:((?:[\w\d_.-]*)|(?:\[[a-f\d:]{3,40}\]))/?)?' .
		'(?::(\w{0,5}))?/?([\w\d/_.,+%!~^$\(\)"\'-]*)(?:\?([\w\d/.,_&=+%!~^$\(\)"\'-]*))?(?:\#([\w\d/_&=+%!~^$\(\)"\'-]*))?$#S';
	static string $initial_parser_regexp_path =
		'#/?([\w\d/_.,+%~^$\(\)"\'-]*)(?:\?([\w\d/.,_&=+%!~^$\(\)"\'-]*))?(?:\#([\w\d/_&=+%!~^$\(\)"\'-]*))?$#S';
	static string $initial_parser_regexp_params =
		'#(?:\?([\w\d/_&=+%!~^$\(\)"\'-]*))?(?:\#([\w\d/_&=+%!~^$\(\)"\'-]*))?$#S';
	static string $default_host = 'localhost';
	static int $default_port = 80;

	static function supportedSchemes() {
		return PHP::box([static::SCHEME_HTTP, static::SCHEME_HTTPS]);
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
		} else if ($part === static::PART_PARAMS) {
			pd('PART', $regexp, $m, $value);
			$path = $m[1] ?? null;
			$params = $m[2] ?? null;
			$sharpy = $m[3] ?? null;
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
			$params_pre_parsed = PHP::box(explode('&', $params));
		}

		$params_res = PHP::box()->apply(
			separator: '&',
			joined_to_str: true,
			stretcher: true
		);
		if ($params_pre_parsed) {
			foreach ($params_pre_parsed as $item) {
				if ($item && Str::contains($item, '=')) {
					[$key, $val] = explode('=', $item);
					if ($key) {
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

		$r = PHP::box([
			'protocol' => $protocol,
			'user' => $user,
			'pass' => $pass,
			'host' => $host,
			'port' => $port,
			'path' => $path,
			'params' => $params,
			'sharpy' => $sharpy,
		]);
		return $r;
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
				$params[$key] = is_null($val)
					?''
					:urlencode($val);
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

}
