<?php

namespace App;

class Middleware
{
  public static function id($app)
  {
    $token = $_COOKIE['token'] ?? $app->getHeader('Authorization') ?: null;
    $next = ($app->uri !== '/' ? '?redirect=' . urlencode($_SERVER['REQUEST_URI']) : '');
    if (!$token)
      return $app->redirect("/login$next");
    $user = Crud::read('users', ['is_active' => 1, 'tokens' => ['like', "%$token%"]]);
    if (!$user) {
      Crud::update('users', ['tokens' => ''], ['tokens' => ['like', "%$token%"]]);
      setcookie('token', '', [
        'expires' => time() - 3600,
        'httponly' => true,
        'samesite' => 'Strict'
      ]);
      return $app->redirect("/login$next");
    }
    $app->setCtx('user', $user);
    $app->setCtx('token', $token);
  }

  public static function can(int $bit)
  {
    return function ($app) use ($bit) {
      $user = $app->getCtx('user');
      if (!$user || !Permissions::has($user->permissions_bit, $bit))
        return $app->render('unauthorized', ['title' => 'Unauthorized']);
    };
  }
}