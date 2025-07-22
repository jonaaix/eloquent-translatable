<?php

namespace Aaix\EloquentTranslatable\Tests;

use Aaix\EloquentTranslatable\EloquentTranslatableServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
   use RefreshDatabase;

   protected function getPackageProviders($app): array
   {
      return [EloquentTranslatableServiceProvider::class];
   }

   protected function getEnvironmentSetUp($app): void
   {
      $app['config']->set('database.default', 'testing');
      $app['config']->set('database.connections.testing', [
         'driver' => 'sqlite',
         'database' => ':memory:',
         'prefix' => '',
      ]);

      // Manually run the migrations
      $migration = include __DIR__ . '/database/migrations/create_test_tables.php';
      $migration->up();
   }
}
