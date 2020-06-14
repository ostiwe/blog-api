<?php


namespace App;


class ErrorHelper
{

	const NOT_VALID_REQUEST_CONTENT_TYPE = 1;
	const AUTH_FAILED_TOKEN = 2;
	const AUTH_FAILED_PASSWORD = 3;
	const AUTH_FAILED_TOKEN_NOT_FOUND = 4;
	const AUTH_FAILED_NOT_PERMISSION = 5;
	const USER_NOT_FOUND = 6;
	const ACCESS_TOKEN_GENERATE_ERROR = 7;
	const REQUEST_WRONG_PARAMS = 8;
	const REGISTER_USER_ALREADY_EXIST = 9;
	const INVALID_REQUEST = 10;
	const POST_NOT_FOUND = 11;

	public static function notValidRequestContentType(string $needType): array
	{
		return [
			'success' => false,
			'code' => self::NOT_VALID_REQUEST_CONTENT_TYPE,
			'message' => "request content-type must be a $needType",
		];
	}

	public static function authorizationFailed(int $authorizationType): array
	{
		return [
			'success' => false,
			'code' => $authorizationType,
			'message' => 'authorization failed',
		];
	}

	public static function userNotFound(): array
	{
		return [
			'success' => false,
			'code' => self::USER_NOT_FOUND,
			'message' => 'user not found',
		];
	}

	public static function accessTokenGenerateError(): array
	{
		return [
			'success' => false,
			'code' => self::ACCESS_TOKEN_GENERATE_ERROR,
			'message' => 'unable to create access token, try again later',
		];
	}

	public static function requestWrongParams(array $messages): array
	{
		return [
			'success' => false,
			'code' => self::REQUEST_WRONG_PARAMS,
			'message' => 'one or more parameters passed incorrectly',
			'data' => $messages,
		];
	}

	public static function registerError($type): array
	{
		return [
			'success' => false,
			'code' => $type,
			'message' => 'user already exist',
		];
	}

	public static function invalidRequest(): array
	{
		return [
			'success' => false,
			'code' => self::INVALID_REQUEST,
			'message' => 'invalid request',
		];
	}

	public static function postNotFound(): array
	{
		return [
			'success' => false,
			'code' => self::POST_NOT_FOUND,
			'message' => 'post not found',
		];
	}

}