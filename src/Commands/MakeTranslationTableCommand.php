<?php

namespace Aaix\EloquentTranslatable\Commands;

use Aaix\EloquentTranslatable\Traits\HasTranslations;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeTranslationTableCommand extends Command
{
   protected $signature = 'make:translation-table {model : The model for which to create a translation table.}';
   protected $description = 'Create a new migration for a model\'s translations table';
   protected Filesystem $files;

   public function __construct(Filesystem $files)
   {
      parent::__construct();
      $this->files = $files;
   }

   /**
    * Execute the console command.
    */
   public function handle(): int
   {
      $modelArgument = $this->argument('model');
      $modelClass = $this->qualifyModel($modelArgument);

      if (!class_exists($modelClass)) {
         $this->error("Model class [{$modelClass}] does not exist.");
         return self::FAILURE;
      }

      $modelInstance = new $modelClass();

      if (!in_array(HasTranslations::class, class_uses_recursive($modelInstance), true)) {
         $this->error("Model [{$modelClass}] does not use the Aaix\\EloquentTranslatable\\Traits\\HasTranslations trait.");
         return self::FAILURE;
      }

      $tableName = Str::singular($modelInstance->getTable()) . '_translations';

      try {
         DB::connection()->getPdo();
         if (Schema::hasTable($tableName)) {
            $this->error("Table [{$tableName}] already exists.");
            return self::FAILURE;
         }
      } catch (\Exception) {
         $this->warn('Warning: Could not connect to the database to check for an existing table. Creating migration anyway.');
      }

      $migrationClassName = 'Create' . Str::studly($tableName) . 'Table';
      $foreignKey = Str::snake(class_basename($modelInstance)) . '_' . $modelInstance->getKeyName();

      $stub = $this->files->get(dirname(__DIR__, 2) . '/stubs/migration.stub');

      $stub = str_replace(
         ['{{ MIGRATION_CLASS_NAME }}', '{{ TRANSLATIONS_TABLE_NAME }}', '{{ FOREIGN_KEY }}'],
         [$migrationClassName, $tableName, $foreignKey],
         $stub,
      );

      $migrationPath = $this->laravel->databasePath('migrations');
      $migrationFileName = date('Y_m_d_His') . '_create_' . $tableName . '_table.php';

      $this->files->put($migrationPath . '/' . $migrationFileName, $stub);

      $this->info("Migration [{$migrationFileName}] created successfully.");

      return self::SUCCESS;
   }

   /**
    * Qualify the given model class base name into a fully-qualified class name.
    */
   protected function qualifyModel(string $model): string
   {
      $model = ltrim($model, '\\/');
      $model = str_replace('/', '\\', $model);

      $rootNamespace = $this->laravel->getNamespace();

      if (Str::startsWith($model, $rootNamespace)) {
         return $model;
      }

      return is_dir(app_path('Models')) ? $rootNamespace . 'Models\\' . $model : $rootNamespace . $model;
   }
}
