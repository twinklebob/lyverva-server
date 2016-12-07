<?php

namespace Twinklebob;

class Tools {
	public static function debug($variable, $title = '') {
		if(!empty($title)) {
			$title = '<b>' . $title . ':</b><br/>' . PHP_EOL;
		}
		printf($title . '%s<br/>' . PHP_EOL, $variable);
	}
}
