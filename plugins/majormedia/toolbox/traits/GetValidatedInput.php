<?php namespace MajorMedia\ToolBox\Traits;
use Validator;

trait GetValidatedInput{
	use ValidateOrFail;

	public function getValidatedInput(Array $fields, Array $rules=[], Array $errorCodes=[], $statusCode=400){
		return $this->ValidateOrFail(
			\Request::only($fields),
			$rules,
			$errorCodes,
			$statusCode
		);
	}

	public function getValidatedInputWithoutNull(Array $fields, Array $rules=[], Array $errorCodes=[], $statusCode=400)
	{
		$values = $this->getValidatedInput($fields, $rules, $errorCodes, $statusCode);
		
		foreach ($values as $key => $value) {
			if ($value === null) {
				unset($values[$key]);
			}
		}

		return $values;
	}
}