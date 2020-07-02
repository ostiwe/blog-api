<?php


namespace App;


class ParamsChecker
{
	public static function check(array $needParams, array $checkedParams): array
	{
		$errors = [];
		foreach ($needParams as $key => $value) {
			if (!is_array($value) && !key_exists($value, $checkedParams)) {
				$errors[$value][] = "$value not found";
			}

			if (!is_array($value) && empty($checkedParams[$value])) {
				$errors[$value][] = "$value empty";
			}

			if (is_array($value) && !empty($next = self::check($value, $checkedParams[$key]))) {
				$errors[$key] = $next;
			}
		}

		return $errors;
	}
}
