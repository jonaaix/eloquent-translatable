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

      \Illuminate\Database\Eloquent\Builder::macro('getWithTranslations', function ($columns = ['*']) {
         /** @var \Illuminate\Database\Eloquent\Builder $this */
         $collection = $this->get($columns);

         if ($collection->isNotEmpty()) {
            $modelClass = get_class($collection->first());
            if (in_array(\Aaix\EloquentTranslatable\Traits\HasTranslations::class, class_uses_recursive($modelClass))) {
               $modelClass::loadTranslationsForCollection($collection);
            }
         }

         return $collection;
      });
   }
}
