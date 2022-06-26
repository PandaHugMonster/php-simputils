<?php

namespace spaf\simputils\interfaces;

interface UrlCompatible {

	function getHost($protocol): ?string;

	function getPath($protocol): ?string;

	function getParams($protocol): ?string;

	function getData($protocol): ?string;
}
