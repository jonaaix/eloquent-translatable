<?php

namespace Aaix\EloquentTranslatable;

use Aaix\EloquentTranslatable\Commands\MakeTranslationTableCommand;
use Illuminate\Support\ServiceProvider;

class EloquentTranslatableServiceProvider extends ServiceProvider
{
   /**
    * Bootstrap any application services.
    *
    * @return void
    */
   public function boot(): void
   {
      if ($this->app->runningInConsole()) {
         $this->publishes(
            [
               __DIR__ . '/../config/translatable.php' => config_path('translatable.php'),
            ],
            'translatable-config',
         );

         $this->commands([MakeTranslationTableCommand::class]);
      }
   }
}
