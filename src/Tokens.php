<?php

namespace App;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Tokens
{
  public static function encode(array $data, ?int $exp = 3600): array
  {
    $exp += time();
    $jwt = JWT::encode([
      'iat' => time(),
      'exp' => $exp,
      ...$data
    ], getenv('JWT_SECRET'), 'HS256');
    return ['token' => $jwt, 'expires' => $exp];
  }

  public static function decode(string $token): object|bool|null
  {
    try {
      $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
      return $decoded;
    } catch (\Firebase\JWT\ExpiredException $e) {
      return false;
    } catch (\Exception $e) {
      return null;
    }
  }
}