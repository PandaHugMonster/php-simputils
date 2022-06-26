<?php

namespace spaf\simputils\models\urls\processors;

use spaf\simputils\generic\BasicProtocolProcessor;
use spaf\simputils\interfaces\UrlCompatible;
use spaf\simputils\models\Box;
use spaf\simputils\PHP;
use spaf\simputils\Str;
use function is_string;
use function preg_replace;
use function urldecode;
use function urlencode;

class HttpProtocolProcessor extends BasicProtocolProcessor {

	/**
	 *
	 * TODO Code is really un-optimal!
	 * @param UrlCompatible|string|Box|array $value        Host value
	 * @param bool                           $is_preparsed If it was pre-parsed
	 *
	 * @return array
	 */
	function parse(UrlCompatible|string|Box|array $value, bool $is_preparsed = false) {
		// host, path, params, data
		$proto = $this->_protocol;

		if ($value instanceof UrlCompatible) {
			return [
				$value->getHost($proto),
				$value->getPath($proto) ?? PHP::box(),
				$value->getParams($proto) ?? PHP::box(),
				$value->getData($proto) ?? PHP::box()
			];
		}

		//

		if (is_string($value)) {
			if (Str::contains($value, '%')) {
				$value = urldecode($value);
			}
		}

		$host = null;
		$path = null;
		$params = PHP::box();
		$data = PHP::box();

		$with_domain = $is_preparsed;
		if (Str::startsWith($value, '//')) {
			$with_domain = true;
			$value = Str::removeStarting($value, '//');
			$value = preg_replace('#/+#', '/', $value);
		} else {
			$value = preg_replace('#/+#', '/', $value);
			$value = Str::removeStarting($value, '/');
		}

		$pre_res = Str::split($value, '?');
		$res = Str::split($pre_res[0], '/');

		if (isset($pre_res[1])) {
			$post_res = Str::split($pre_res[1], '#');
			if (isset($post_res[0])) {
				// NOTE Args
				$pairs = Str::split($post_res[0], '&');
				foreach ($pairs as $pair) {
					[$k, $v] = Str::split($pair, '=');
					$params[$k] = $v;
				}
			}
			if (isset($post_res[1])) {
				$params['#'] = $post_res[1];
			}
		}

		if ($with_domain && isset($res[0])) {
			$host = preg_replace('#\s+#S', '', $res[0]);
			unset($res[0]);
		}
		$path = $res;

		return [$host, $path, $params, $data];
	}

	private function generateCommonUrl($host, $path, $params, $data, $is_relative = false) {
		/** @var Box $path */
		/** @var Box $params */
		/** @var Box $data */
		$path = $path->join('/');
		$res_params = PHP::box();
		$sharp = null;
		foreach ($params as $k => $v) {
			if ($k === '#') {
				$sharp = $v;
			} else {
				$res_params[] = $k.'='.urlencode($v);
			}
		}
		$params = $res_params->join('&');
		if (!empty($sharp)) {
			$sharp = "#{$sharp}";
		}
		if (!empty($params)) {
			$params = "?{$params}";
		}
		$pre = '';
		if (!$is_relative) {
			$pre = "{$this->protocol}://{$host}/";
		}

		return "{$pre}{$path}{$params}{$sharp}";
	}

	function generateForSystem($host, $path, $params, $data): string {
		return $this->generateCommonUrl($host, $path, $params, $data);
	}

	function generateForUser($host, $path, $params, $data): string {
		return $this->generateCommonUrl($host, $path, $params, $data);
	}

	function generateRelative($host, $path, $params, $data): string {
		return $this->generateCommonUrl($host, $path, $params, $data, true);
	}
}
