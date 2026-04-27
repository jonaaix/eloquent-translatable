<?php

namespace Aaix\EloquentTranslatable;

use Aaix\EloquentTranslatable\Commands\MakeTranslationTableCommand;
use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EloquentTranslatableServiceProvider extends PackageServiceProvider
{
   public function configurePackage(Package $package): void
   {
      $package
         ->name('eloquent-translatable')
         ->hasConfigFile()
         ->hasCommand(MakeTranslationTableCommand::class);
   }

   public function packageBooted(): void
   {
      Builder::macro('getWithTranslations', function ($columns = ['*']) {
         /** @var Builder $this */
         $collection = $this->get($columns);

         if ($collection->isNotEmpty()) {
            $modelClass = get_class($collection->first());
            if (in_array(HasTranslations::class, class_uses_recursive($modelClass))) {
               $modelClass::loadTranslationsForCollection($collection);
            }
         }

         return $collection;
      });
   }
}
