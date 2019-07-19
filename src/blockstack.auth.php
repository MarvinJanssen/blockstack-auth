<?php
namespace Blockstack;

use Elliptic\EC;

class AuthenticationException extends \Exception{}

class Authentication
	{
	protected static $token = null;
	protected static $alg = 'ES256K';
	protected static $required_claims = ['iss','iat','jti','exp','username','profile','public_keys'];

	/**
	 * Automatically captures and processes GET parameter authResponse. Will call $callable if a token is found and successfully verified before redirecting.
	 * @param  callable  $callable
	 * @return void
	 */
	public static function handle($callable)
		{
		if (!is_callable($callable))
			throw new InvalidArgumentException('$callable has to be a callable');
		if (!empty($_GET['authResponse']) && self::verify($_GET['authResponse']))
			$callable(self::token());
		}

	/**
	 * Decodes a Blockstack authorisation response and returns it as an array.
	 * @param  string $auth_response
	 * @return array
	 */
	public static function decode($auth_response)
		{
		$split = explode('.',$auth_response);
		$c = count($split);
		if ($c !== 2 && $c !== 3)
			throw new AuthenticationException('Invalid auth response',1);
		return [
			'header' => json_decode(self::base64_decode($split[0]),true),
			'payload' => json_decode(self::base64_decode($split[1]),true),
			'signature' => isset($split[2]) ? self::base64_decode($split[2]) : null,
			'signature_input' => $split[0].'.'.$split[1]
			];
		}

	/**
	 * Verifies an authorisation request, checks the claims, signature, and expiry.
	 * @param  string|array  $auth_response
	 * @param  boolean $ignore_expiry Optional: ignore expiry if set to true.
	 * @return boolean
	 */
	public static function verify($auth_response,$ignore_expiry = false)
		{
		if (is_string($auth_response))
			$auth_response = self::decode($auth_response);
		
		if (!isset($auth_response['header'],$auth_response['payload'],$auth_response['signature'],$auth_response['signature_input']))
			throw new AuthenticationException('Invalid auth response',1);

		if (!isset($auth_response['header']['alg']) || $auth_response['header']['alg'] !== self::$alg)
			throw new AuthenticationException('Expected alg '.self::$alg);

		foreach (self::$required_claims as $claim)
			if (!array_key_exists($claim,$auth_response['payload']))
				throw new AuthenticationException('Required claim '.$claim.' missing',2);

		if (!is_array($auth_response['payload']['public_keys']) || count($auth_response['payload']['public_keys']) > 1)
			throw new AuthenticationException('Need exactly one public key in array.',3);

		if (!$ignore_expiry && (!isset($auth_response['payload']['exp']) || time() > $auth_response['payload']['exp']))
			throw new AuthenticationException('Request expired.',3);
		
		$message = hash('sha256',$auth_response['signature_input'],false); // use byte array if last parameter is set to true
		$ec = new EC('secp256k1');
		$length = 32;
		$signature = ['r' => bin2hex(substr($auth_response['signature'],0,$length)),'s' => bin2hex(substr($auth_response['signature'],$length,$length))];
		$key = $ec->keyFromPublic($auth_response['payload']['public_keys'][0],'hex');	
		$result = $ec->verify($message,$signature,$key);
		if ($result)
			self::$token = $auth_response;
		return $result;
		}

	/**
	 * Returns the decoded token after verify has succeeded.
	 * @return null|array
	 */
	public static function token()
		{
		return self::$token;
		}

	protected static function base64_decode($string)
		{
		return base64_decode(self::base64_pad(self::base64_convert($string)),true);
		}

	protected static function base64_pad($string)
		{
		$n = strlen($string)%4;
		return $n === 0 ? $string : $string.str_repeat('=',4-$n);
		}

	protected static function base64_convert($string)
		{
		return str_replace(['-','_'],['+','/'],$string);
		}
	}
