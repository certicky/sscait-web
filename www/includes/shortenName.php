<?php

function shortenName($str,$length) {
	// if name is already short enough
	if (strlen($str) <= $length) {
		return $str;
	} else {
		// if name doesn't contain whitespace
		if (strpos($str," ") === FALSE) {
			return substr($str,0,$length).".";
		} else {
			$ex = explode(" ",$str);
			return substr($ex[0],0,1).". ".shortenName(join(" ",array_slice($ex,1)),$length);
		}
	}
}

?>
