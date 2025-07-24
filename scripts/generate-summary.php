#!/usr/bin/env php
<?php

$summaryFile = "tests/Feature/Performance/performance_summary.json";
$baselineFile = "tests/Feature/Performance/baseline_results.json";

if (!file_exists($summaryFile) || !file_exists($baselineFile)) {
   exit;
}

// --- ANSI Color Codes ---
define('COLOR_GREEN', "\033[0;32m");
define('COLOR_RED', "\033[0;31m");
define('COLOR_RESET', "\033[0m");

$results = json_decode(file_get_contents($summaryFile), true);
$baselineResults = json_decode(file_get_contents($baselineFile), true);

$tableData = [];
$drivers = ["aaix/eloquent-translatable", "astrotomic/laravel-translatable", "spatie/laravel-translatable"];
$testOrder = [
   "Read: Access 1st Translation",
   "Read: Find by Translation",
   "Read: Eager Load 50 Products",
   "Write: Create + 1 Translation",
   "Write: Create + All Transl.",
   "Write: Update 1 Translation",
];

// Aggregate results by test name to pivot the data
foreach ($results as $result) {
   $tableData[$result["test_name"]][$result["driver"]] = $result["duration"];
}

$line = str_repeat('=', 101);
echo $line . "\n";
echo "                                  🚀 Performance Summary (Time in ms) 🚀\n";
echo $line . "\n";
printf("| %-31s | %-12s | %-23s | %-23s |\n", "Test Case", "aaix", "astrotomic", "spatie");
echo "|---------------------------------|--------------|-------------------------|-------------------------|\n";

// Print table rows in the predefined order
foreach ($testOrder as $testName) {
   if (!isset($tableData[$testName])) {
      continue;
   }

   $baselineDuration = $baselineResults[$testName]['duration'] ?? 0;

   $aaixTime = $tableData[$testName][$drivers[0]] ?? null;
   $astrotomicTime = $tableData[$testName][$drivers[1]] ?? null;
   $spatieTime = $tableData[$testName][$drivers[2]] ?? null;

   // --- Manually pad each cell to handle ANSI color codes correctly ---

   // AAiX Cell (no color)
   $aaixCell = $aaixTime !== null ? sprintf("%.2f ms", $aaixTime) : 'N/A';
   $aaixPadded = str_pad($aaixCell, 12);

   // Astrotomic Cell (with color)
   $astrotomicPadded = str_pad('N/A', 23);
   if ($astrotomicTime !== null && $baselineDuration > 0) {
      $diff = (($astrotomicTime - $baselineDuration) / $baselineDuration) * 100;
      $color = $diff >= 0 ? COLOR_RED : COLOR_GREEN;

      $visibleString = sprintf("%.2f ms (%+.1f%%)", $astrotomicTime, $diff);
      $coloredString = sprintf("%.2f ms %s(%+.1f%%)%s", $astrotomicTime, $color, $diff, COLOR_RESET);

      $padding = 23 - strlen($visibleString);
      $astrotomicPadded = $coloredString . str_repeat(' ', $padding > 0 ? $padding : 0);
   }

   // Spatie Cell (with color)
   $spatiePadded = str_pad('N/A', 23);
   if ($spatieTime !== null && $baselineDuration > 0) {
      $diff = (($spatieTime - $baselineDuration) / $baselineDuration) * 100;
      $color = $diff >= 0 ? COLOR_RED : COLOR_GREEN;

      $visibleString = sprintf("%.2f ms (%+.1f%%)", $spatieTime, $diff);
      $coloredString = sprintf("%.2f ms %s(%+.1f%%)%s", $spatieTime, $color, $diff, COLOR_RESET);

      $padding = 23 - strlen($visibleString);
      $spatiePadded = $coloredString . str_repeat(' ', $padding > 0 ? $padding : 0);
   }

   // Print the fully constructed row
   printf(
      "| %-31s | %s | %s | %s |\n",
      $testName,
      $aaixPadded,
      $astrotomicPadded,
      $spatiePadded
   );
}

echo $line . "\n";
