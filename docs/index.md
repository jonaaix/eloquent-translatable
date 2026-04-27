---
layout: home

hero:
   name: Laravel Eloquent Translatable
   text: High performance translations for Laravel models
   image:
      src: /img/logo2.png
      alt: Laravel Eloquent Translatable Logo
   actions:
      -  theme: brand
         text: Get Started
         link: /docs/10-getting-started
      -  theme: alt
         text: View on GitHub
         link: https://github.com/jonaaix/eloquent-translatable

features:
   -  icon:
         src: /img/developer.png
      title: Developer-First Experience
      details: Get productive in seconds. Scaffold your migration files with a single Artisan command, and write safer, more readable code with the built-in Locale enum that prevents typos and improves clarity.
   -  icon:
         src: /img/api.png
      title: Intuitive & Fluent API
      details: Designed with the developer in mind. Its clean and consistent API lets you manage translations with expressive, readable code, allowing you to get started quickly and maintain your projects with ease.
   -  icon:
         src: /img/performance.png
      title: Performant & Scalable
      details: Built for speed and scalability. By partially bypassing Eloquent's overhead for direct database queries, this package delivers lightning-fast performance with minimal memory usage, even when handling millions of translations.
---

<div style="max-width: 960px; margin: 4rem auto 0; padding: 0 24px;">

## Why another Laravel translation package?

While packages like `spatie/laravel-translatable` and `astrotomic/laravel-translatable` are powerful, they all make a trade-off. This package is built for raw performance and a clean, focused developer experience by using direct, indexed database queries instead of relying on JSON columns or Eloquent model hydration.

<div class="comparison-table">

|                 | aaix/eloquent-translatable     | spatie/laravel-translatable                    | astrotomic/laravel-translatable        |
| --------------- | ------------------------------ | ---------------------------------------------- | -------------------------------------- |
| **Storage**     | Dedicated Table                | JSON Column                                    | Dedicated Table                        |
| **Performance** | High (Scalable)                | Fastest (Single Record Reads)                  | High (Eloquent)                        |
| **Simplicity**  | Minimal API, Zero-Config Logic | Requires managing JSON paths & complex queries | Requires extra Translation Model class |

</div>

<div class="perf-block">

```text
======================================================================================================
                                    🚀 Performance Comparison 🚀
======================================================================================================
| Test Case                       | aaix         | astrotomic              | spatie                  |
|---------------------------------|--------------|-------------------------|-------------------------|
| Read: Access 1st Translation    | 1.70 ms      | 1.65 ms (-3.1%)         | 0.55 ms (-68.0%)        |
| Read: Find by Translation       | 1.01 ms      | 1.20 ms (+18.6%)        | 0.89 ms (-12.6%)        |
| Read: Eager Load 50 Products    | 1.97 ms      | 2.82 ms (+43.3%)        | 1.16 ms (-40.9%)        |
| Write: Create + 1 Translation   | 1.27 ms      | 1.73 ms (+36.0%)        | 1.04 ms (-17.7%)        |
| Write: Create + All Transl.     | 1.45 ms      | 3.80 ms (+162.8%)       | 1.03 ms (-28.6%)        |
| Write: Update 1 Translation     | 0.77 ms      | 0.82 ms (+7.0%)         | 0.41 ms (-46.2%)        |
======================================================================================================
```

</div>

<div class="feature-comparison">

#### <span class="badge">SUPERIOR</span> Database Performance

By using direct, indexed database queries instead of parsing JSON or hydrating countless Eloquent models, operations are significantly faster and use a fraction of the memory. This is not a minor improvement; it's a fundamental architectural advantage for applications at scale.

</div>

<div class="feature-comparison">

#### <span class="badge">BETTER</span> Data Integrity

Translations are stored in a clean, normalized, and dedicated table. This provides better data integrity and structure than a single JSON column and avoids the performance pitfalls of a full Eloquent-relation approach.

</div>

<div class="feature-comparison">

#### <span class="badge">SIMPLER</span> Developer Experience

The API is designed to be minimal, intuitive, and predictable. With a single command to set up your migrations and a fluent, easy-to-understand set of methods, you get the power you need without the complexity you don't. No magic, just performance.

</div>

</div>
