<?php

namespace ESolution\DBEncryption\Tests;

use ESolution\DBEncryption\Providers\DBEncryptionServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
  public function setUp(): void
  {
    parent::setUp();
    // additional setup

    $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
  }

  protected function getPackageProviders($app)
  {
    return [
      DBEncryptionServiceProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    // perform environment setup
    $app['config']->set('database.default', 'mysql');
    $app['config']->set('database.connections.mysql', [
        'driver'   => 'mysql',
        'host'  => '127.0.0.1',
        'port'  => '3306',
        'database' => 'test',
        'username'   => 'root',
        'password'   => ''
    ]);
  }

  public function createUser($name = 'Jhon Doe', $email = 'jhon@doe.com') : TestUser
  {
    $user = TestUser::factory()->create(["name" => $name, "email" => $email, "password"=>"abcdef"]);

    return $user;
  }
}
