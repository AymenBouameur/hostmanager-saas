<?php namespace Majormedia\ToolBox\Traits;
use Exception;

trait RenameArrayKey{
	public function renameArrayKey(Array $arr, $old_key, $new_key){
		$arr[$new_key] = $arr[$old_key];
		unset($arr[$old_key]);
		return $arr;
	}
}
