<?php namespace MajorMedia\ToolBox\Traits;
use Exception;
use MajorMedia\ToolBox\Utility\ErrorCodes;

trait JsonAbort{
	public function JsonAbort($payload=[
	    'status' => 'error',
	    'code' => ErrorCodes::UNEXPECTED,
	], Int $status=500){
		// if the payload is a string
		if(is_string($payload))
			$payload = [
				'status' => 'error',
				'code' => ErrorCodes::UNEXPECTED,
			];
		// if the payload is an exception return the message
		if($payload instanceof Exception)
			$payload = [
				'status' => 'error',
				'code' => ErrorCodes::UNEXPECTED,
			];
        response()->json($payload, $status)->send();
        exit();
	}
}
