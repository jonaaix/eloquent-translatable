<?php

namespace Aaix\EloquentTranslatable\Tests;

use Aaix\EloquentTranslatable\EloquentTranslatableServiceProvider;
use Astrotomic\Translatable\TranslatableServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
   protected function setUp(): void
   {
      parent::setUp();

      // This now only loads the general-purpose migrations like `create_test_tables.php`.
      $this->loadMigrationsFrom(__DIR__.'/database/migrations');
   }

   protected function getPackageProviders($app): array
   {
      return [EloquentTranslatableServiceProvider::class, TranslatableServiceProvider::class];
   }

   protected function getEnvironmentSetUp($app): void
   {
      $app['config']->set('database.default', 'mysql');
      $app['config']->set('database.connections.mysql', [
         'driver' => 'mysql',
         'host' => '127.0.0.1',
         'port' => '3307',
         'database' => 'eloquent_translatable_test',
         'username' => 'root',
         'password' => 'password',
         'charset' => 'utf8mb4',
         'collation' => 'utf8mb4_unicode_ci',
         'prefix' => '',
         'strict' => true,
         'engine' => null,
      ]);
   }
}
