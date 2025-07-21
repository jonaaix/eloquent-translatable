---
sidebar_position: 6
---

# Locale Enum

For convenience and type-safety, the package ships with a comprehensive `Locale` enum that covers most common languages. Using the
enum instead of raw strings helps prevent typos and makes your code more readable and self-documenting.

## Usage Example

You can import the enum and use it directly in any of the package's methods.

```php
use Aaix\EloquentTranslatable\Enums\Locale;

// Set a persistent locale
$product->setLocale(Locale::JAPANESE);

// Get a specific translation
$name = $product->translate('name', Locale::SPANISH);

// Stage a translation for saving
$product->setTranslation('name', Locale::FRENCH, 'Nouveau Produit');
```

## Creating a Custom Enum

If the provided `Locale` enum is not sufficient for your needs (e.g., you require regional locales like `en-US`), you can easily create your own.

Your custom enum must be a **string-backed enum**. When using it with this package's methods, you can simply pass the enum case directly, as the methods also accept raw strings.

Here is an example of a custom enum for regional English locales:

**`app/Enums/RegionalLocale.php`**

```php
<?php

namespace App\Enums;

enum RegionalLocale: string
{
   case US_ENGLISH = 'en-US';
   case BRITISH_ENGLISH = 'en-GB';
   case AUSTRALIAN_ENGLISH = 'en-AU';
}
```

You can then use it in your application like this:

```php
use App\Enums\RegionalLocale;

// The package methods will correctly use the string value 'en-GB'.
$product->setTranslation('name', RegionalLocale::BRITISH_ENGLISH, 'A British Name');
```

## Available Locales

The following locales are available out of the box:

```php
enum Locale: string
{
   case AFRIKAANS = 'af';
   case ALBANIAN = 'sq';
   case ARABIC = 'ar';
   case ARMENIAN = 'hy';
   case AZERBAIJANI = 'az';
   case BASQUE = 'eu';
   case BELARUSIAN = 'be';
   case BENGALI = 'bn';
   case BOSNIAN = 'bs';
   case BULGARIAN = 'bg';
   case CATALAN = 'ca';
   case CHINESE = 'zh';
   case CROATIAN = 'hr';
   case CZECH = 'cs';
   case DANISH = 'da';
   case DUTCH = 'nl';
   case ENGLISH = 'en';
   case ESTONIAN = 'et';
   case FINNISH = 'fi';
   case FRENCH = 'fr';
   case GALICIAN = 'gl';
   case GEORGIAN = 'ka';
   case GERMAN = 'de';
   case GREEK = 'el';
   case HEBREW = 'he';
   case HINDI = 'hi';
   case HUNGARIAN = 'hu';
   case ICELANDIC = 'is';
   case INDONESIAN = 'id';
   case IRISH = 'ga';
   case ITALIAN = 'it';
   case JAPANESE = 'ja';
   case KAZAKH = 'kk';
   case KOREAN = 'ko';
   case LATVIAN = 'lv';
   case LITHUANIAN = 'lt';
   case MACEDONIAN = 'mk';
   case MALAY = 'ms';
   case MALTESE = 'mt';
   case NORWEGIAN = 'no';
   case PERSIAN = 'fa';
   case POLISH = 'pl';
   case PORTUGUESE = 'pt';
   case ROMANIAN = 'ro';
   case RUSSIAN = 'ru';
   case SERBIAN = 'sr';
   case SLOVAK = 'sk';
   case SLOVENIAN = 'sl';
   case SPANISH = 'es';
   case SWAHILI = 'sw';
   case SWEDISH = 'sv';
   case THAI = 'th';
   case TURKISH = 'tr';
   case UKRAINIAN = 'uk';
   case URDU = 'ur';
   case UZBEK = 'uz';
   case VIETNAMESE = 'vi';
   case WELSH = 'cy';
}
```
