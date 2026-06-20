<?php

namespace Faktura\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenService
{
  public static function sign(array $data, ?int $exp = null): array
  {
    $exp = ($exp ?: (getenv('JWT_EXP') ?: 3600)) + time();
    try {
      $token = JWT::encode(
        [
          'aud' => $_SERVER['HTTP_HOST'] ?? 'faktura.com',
          'iss' => $_SERVER['HTTP_HOST'] ?? 'faktura.com',
          'iat' => time(),
          'exp' => $exp,
          ...$data
        ],
        getenv('JWT_SECRET'),
        'HS256'
      );
      return ['token' => $token, 'exp' => $exp];
    } catch (\Exception $ex) {
      return [];
    }
  }

  public static function verify($token): object|bool|null
  {
    try {
      return JWT::decode(
        $token,
        new Key(getenv('JWT_SECRET'), 'HS256')
      );
    } catch (\Firebase\JWT\ExpiredException $e) {
      return false;
    } catch (\Exception $ex) {
      return null;
    }
  }
}