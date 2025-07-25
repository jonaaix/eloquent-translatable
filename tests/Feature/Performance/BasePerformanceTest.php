<?php

namespace Aaix\EloquentTranslatable\Tests\Feature\Performance;

use Faker\Factory;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\DB;
use Aaix\EloquentTranslatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class BasePerformanceTest extends TestCase
{
   protected int $productCount = 100000;
   protected int $chunkSize = 100;
   protected array $locales = ['en', 'de', 'fr', 'es', 'nl'];
   protected OutputStyle $output;
   private const BASELINE_DRIVER_NAME = 'aaix/eloquent-translatable';
   private const BASELINE_RESULTS_FILE = __DIR__ . '/baseline_results.json';
   private const SUMMARY_RESULTS_FILE = __DIR__ . '/performance_summary.json';

   public function setUp(): void
   {
      parent::setUp();
      $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/performance');
   }

   public function __construct(?string $name = null, array $data = [], $dataName = '')
   {
      parent::__construct($name, $data, $dataName);
      $this->output = new OutputStyle(new ArrayInput([]), new ConsoleOutput());
   }

   abstract protected function getDriverName(): string;
   abstract protected function getModelClass(): string;
   abstract protected function seedChunk(int $count, int $startIndex): void;
   abstract protected function pruneChunk(int $count): void;
   abstract protected function getProduct(int $id): object;
   abstract protected function getTranslatedName(object $product, string $locale): ?string;
   abstract protected function queryByName(string $name, string $locale): ?object;
   abstract protected function eagerLoadProducts(int $count): void;
   abstract protected function createWithOneTranslation(): void;
   abstract protected function createWithAllTranslations(): void;
   abstract protected function updateOneTranslation(): void;

   public function prepareDatabase(): void
   {
      $modelClass = $this->getModelClass();
      $targetCount = $this->productCount;

      DB::disableQueryLog();
      $currentCount = $modelClass::count();

      if ($currentCount < $targetCount) {
         $toCreate = $targetCount - $currentCount;
         $this->output->writeln("<info>[{$this->getDriverName()}] Seeding {$toCreate} new records...</info>");

         for ($i = 0; $i < $toCreate; $i += $this->chunkSize) {
            $chunkCount = min($this->chunkSize, $toCreate - $i);
            $this->seedChunk($chunkCount, $currentCount + $i);
         }
      } elseif ($currentCount > $targetCount) {
         $toDelete = $currentCount - $targetCount;
         $this->output->writeln("<info>[{$this->getDriverName()}] Pruning {$toDelete} excess records...</info>");

         for ($i = 0; $i < $toDelete; $i += $this->chunkSize) {
            $chunkCount = min($this->chunkSize, $toDelete - $i);
            $this->pruneChunk($chunkCount);
         }
      } else {
         $this->output->writeln(
            "<info>[{$this->getDriverName()}] Database is already at the correct size ({$targetCount} records).</info>",
         );
      }
      DB::enableQueryLog();
   }

   #[Test]
   public function it_measures_performance(): void
   {
      $randomId = random_int(1, $this->productCount);
      // $randomLocale = $this->locales[random_int(0, count($this->locales) - 1)];
      $randomLocale = 'nl';

      // Create a fake read to warm up php
      $this->getProduct($randomId);
      $this->queryByName('Product 500 name de', 'de');
      $this->eagerLoadProducts(50);

      $this->measure('Read: Access 1st Translation', function () use ($randomId, $randomLocale) {
         $product = $this->getProduct($randomId);
         $this->assertNotNull($product);
         $this->getTranslatedName($product, $randomLocale);
      });
      $this->measure('Read: Find by Translation', function () {
         $product = $this->queryByName('Product 500 name de', 'de');
         $this->assertNotNull($product);
      });
      $this->measure('Read: Eager Load 50 Products', function () {
         $this->eagerLoadProducts(50);
      });

      $this->updateOneTranslation();
      $this->createWithOneTranslation();
      $this->createWithAllTranslations();

      $this->measure('Write: Update 1 Translation', function () {
         $this->updateOneTranslation();
      });
      $this->measure('Write: Create + 1 Translation', function () {
         $this->createWithOneTranslation();
      });
      $this->measure('Write: Create + All Transl.', function () {
         $this->createWithAllTranslations();
      });
   }

   protected function measure(string $name, callable $callback): void
   {
      DB::enableQueryLog();
      $startMemory = memory_get_usage();
      $startTime = microtime(true);

      $callback();

      $endTime = microtime(true);
      $endMemory = memory_get_usage();
      $queries = DB::getQueryLog();
      DB::disableQueryLog();
      $duration = ($endTime - $startTime) * 1000;
      $memoryUsage = ($endMemory - $startMemory) / 1024;

      $this->logPerformance($name, $duration, $memoryUsage, $queries);
   }

   protected function logPerformance(string $name, float $duration, float $memoryUsage, array $queries = []): void
   {
      $driver = str_pad($this->getDriverName(), 30);
      $testName = str_pad($name, 30);

      $durationStr = round($duration, 2) . ' ms';
      $memoryStr = round($memoryUsage, 2) . ' KB';

      if ($this->getDriverName() === self::BASELINE_DRIVER_NAME) {
         $baselineResults = file_exists(self::BASELINE_RESULTS_FILE)
            ? json_decode(file_get_contents(self::BASELINE_RESULTS_FILE), true)
            : [];
         $baselineResults[$name] = ['duration' => $duration, 'memory' => $memoryUsage];
         file_put_contents(self::BASELINE_RESULTS_FILE, json_encode($baselineResults, JSON_PRETTY_PRINT));
      } else {
         if (file_exists(self::BASELINE_RESULTS_FILE)) {
            $baselineResults = json_decode(file_get_contents(self::BASELINE_RESULTS_FILE), true);
            if (isset($baselineResults[$name])) {
               $baseline = $baselineResults[$name];
               if ($baseline['duration'] > 0.01) {
                  $durationDiff = (($duration - $baseline['duration']) / $baseline['duration']) * 100;
                  $durationColor = $durationDiff >= 0 ? 'error' : 'info';
                  $durationStr .= sprintf(' <%s>(%+.1f%%)</%s>', $durationColor, $durationDiff, $durationColor);
               }
               if ($baseline['memory'] > 0.01) {
                  $memoryDiff = (($memoryUsage - $baseline['memory']) / $baseline['memory']) * 100;
                  $memoryColor = $memoryDiff >= 0 ? 'error' : 'info';
                  $memoryStr .= sprintf(' <%s>(%+.1f%%)</%s>', $memoryColor, $memoryDiff, $memoryColor);
               }
            }
         }
      }

      // Append result to the summary file for the final table
      $summaryResults = file_exists(self::SUMMARY_RESULTS_FILE)
         ? json_decode(file_get_contents(self::SUMMARY_RESULTS_FILE), true)
         : [];
      $summaryResults[] = [
         'driver' => $this->getDriverName(),
         'test_name' => $name,
         'duration' => $duration,
      ];
      file_put_contents(self::SUMMARY_RESULTS_FILE, json_encode($summaryResults, JSON_PRETTY_PRINT));

      $paddedDurationStr = str_pad($durationStr, 35);
      $this->output->writeln("{$driver} | {$testName} | {$paddedDurationStr} | {$memoryStr}");

      if (env('LOG_QUERIES', false) && !empty($queries)) {
         foreach ($queries as $query) {
            $sql = \Illuminate\Support\Str::replaceArray(
               '?',
               array_map(fn($b) => is_numeric($b) ? $b : "'{$b}'", $query['bindings']),
               $query['query'],
            );
            $this->output->writeln("<fg=gray>  └─ [{$query['time']}ms] {$sql}</>");
         }
      }
   }

   protected function getFaker(): \Faker\Generator
   {
      return Factory::create();
   }
}
