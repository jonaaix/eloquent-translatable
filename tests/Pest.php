<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific
| instance of a test case. By default, that will be a generic PHPUnit instance
| but you can change that specified class here.
|
*/

uses(Aaix\EloquentTranslatable\Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain
| conditions. The "expect()" function gives you access to a set of "expectations"
| that you can use to check those conditions.
|
| Pest has several expectations built-in, but you can add more by extending
| the "expect()" function. Here's an example of how you can do that:
|
*/

// expect()->extend('toBeOne', function () {
//     return $this->toBe(1);
// });

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code
| that you would like to reuse across different tests. Here you can add your own
| custom functions to the Pest global scope. Helps you to avoid repetitive code.
|
*/

// function something() {
//     // ..
// }
