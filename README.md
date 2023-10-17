<p align="center"><img src=".github/header.png"></p>

# Extractor: AI-Powered Data Extraction for your Laravel application.

![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/extractor.svg?style=flat-square)
![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/extractor.svg?style=flat-square)

Effortlessly extract structured data from various sources, including images, PDFs, and emails, using OpenAI within your
Laravel application.

## Features

- A convenient wrapper around OpenAI Chat and Completion endpoints.
- Takes text as input and provides structured information.
- Includes a finely tuned prompt for parsing data.
- Supports multiple input formats such as Plain Text, PDF, Images, Word documents, and Web content.
- Integrates with [Textract](https://aws.amazon.com/textract/) for OCR functionality.

## Installation

Install the package via composer:

```bash
composer require helgesverre/extractor
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="extractor-config"
```

You can find all the configuration options in the configuration file.

Since this package relies on the [OpenAI Laravel Package](https://github.com/openai-php/laravel), you also need to
publish
their configuration and add the `OPENAI_API_KEY` to your `.env` file:

```dotenv
OPENAI_API_KEY="your-key-here"
```

## Usage

### Extracting data from Plain Text

Plain text extraction is useful when you already have textual data that needs to be processed.

Here's an example using plain text data:

```php
$text = <<<DATA
Your structured data goes here.
Replace this with your text.
DATA;

Extractor::extract($text);
```

### Loading text content with various file formats

```php
use HelgeSverre\Extractor\Facades\Text;

$textPlainText = Text::text(file_get_contents('./data.txt'));
$textPdf = Text::pdf(file_get_contents('./data.pdf'));
$textImageOcr = Text::textract(file_get_contents('./data.jpg'));
$textPdfOcr = Text::textractUsingS3Upload(file_get_contents('./data.pdf'));
$textWord = Text::word(file_get_contents('./data.doc'));
$textWeb = Text::web('https://example.com');
$textHtml = Text::html(file_get_contents('./data.html'));
```

| Description                                                                                                                                                                                                   | Method                        |
|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------|
| Extract text from a plain text, useful if you need trim/normalize whitespace in a string.                                                                                                                     | `Text::text`                  |
| Extract text from a PDF file, uses [smalot/pdfparser](https://github.com/smalot/pdfparser)                                                                                                                    | `Text::pdf`                   |
| Extract text with [AWS Textract](https://aws.amazon.com/textract/) by sending the content as a base64 encoded string (faster, but has [limitations](https://docs.aws.amazon.com/textract/latest/dg/sync.html) | `Text::textract`              |
| Extract text with [AWS Textract](https://aws.amazon.com/textract/) by uploading file to S3 and polling for completion (handles larger files and multi-page PDFs)                                              | `Text::textractUsingS3Upload` | 
| Extract plain text from a Word document (Uses simple xml parsing and unzipping)                                                                                                                               | `Text::word`                  |
| Fetches HTML from an URL via HTTP, strip all HTML tags, squish and trim all whitespace.                                                                                                                       | `Text::web`                   |
| Extract text from an HTML file (same, but for HTML content)                                                                                                                                                   | `Text::html`                  |

## Included Extractors

The `Extractor` package includes a set of pre-built extractors designed to simplify the extraction of structured data
from various types of text. Each extractor is optimized for specific data formats, making it easy to process different
types of information. Below is a list of the included extractors along with brief descriptions and convenient shortened
methods for each:

| Example                                       | Extractor    | Description                                                                       |
|-----------------------------------------------|--------------|-----------------------------------------------------------------------------------|
| `Extractor::extract("receipt", $text);`       | Receipt      | Extracts structured data from receipts and invoices.                              |
| `Extractor::extract("payslip", $text);`       | Payslip      | Extracts data from payslips, including salary details.                            |
| `Extractor::extract("recipe", $text);`        | Recipe       | Extracts ingredients and cooking instructions from recipes.                       |
| `Extractor::extract("contacts", $text);`      | Contacts     | Extracts contact information such as names, addresses, and phone numbers.         |
| `Extractor::extract("emails", $text);`        | Emails       | Identifies and extracts email addresses from text.                                |
| `Extractor::extract("phone numbers", $text);` | PhoneNumbers | Extracts phone numbers from text and returns an array of strings in E.164 format. |

These extractors are provided out of the box and offer a convenient way to extract specific types of structured data
from text. You can use the shortened methods to easily access the functionality of each extractor.

## How it works

Extractor is a package that uses the power of LLMs, prompts and cleverness to turn unstructured text data into
structured JSON.

This is done by first turning whatever unstructured data you have into plain text, injecting that text into a text
prompt that describes what data we want to extract, then sending that prompt to OpenAI, the response is then converted
to JSON and is optionally passed through validation, DTO-casting and other modification steps inside the extractor.

A good use-case for this would be a "Contact List" extractor, where you have the following requirements:

- You need to extract a list of people from a document
- Each item needds to include a valid email, a name and optionally a phone number and job title.
- At the end, the data should be turned into a "Contact" DTO Collection
- We need to validate each phone number with our own internal business logic (eg: look them up in our internal CRM,
  perform an API call to check the white-pages, or cross-reference a CSV file, whatever)

The Extractor package provides a consistent, wel thought out and structured way to define and house this logic.

## Creating Extractors

### Basic extractor

```php
```

### Adding Validation

```php
<?php

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\Extraction\Concerns\HasValidation;
use HelgeSverre\Extractor\Extraction\Extractor;

class Contacts extends Extractor
{
    use HasValidation;

    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string'],
            '*.title' => ['required', 'string'],
            '*.email' => ['required', 'email'],
            '*.phone' => ['required'],
        ];
    }

}

```

### Extracting data into a DTO

Extractor integrates with spatie/data to cast the extracted data into a DTO of your choosing, add the `HasDto` trait to
your Extractor, and return the class-string from the `dataClass` method.

If you are extracting a collection, add the  `isCollection` method and have it return `true`, this will
call `YourDato::collection()` on the extracted data.

```php
<?php

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\ContactDto;
use HelgeSverre\Extractor\Extraction\Concerns\HasDto;
use HelgeSverre\Extractor\Extraction\Extractor;
use Spatie\LaravelData\Data;

class ContactDto extends Data
{
    public function __construct(
        public string $name,
        public string $title,
        public string $phone,
        public string $email,
    ) {
    }
}

class Contacts extends Extractor
{
    use HasDto;

    public function dataClass(): string
    {
        return ContactDto::class;
    }

    public function isCollection(): bool
    {
        return true;
    }
}

```

### Making your Extractors configurable

The extractor class takes a config array in the constructor, this config array is avaialble on the extractor instance
and is both injected into the prompt as a variable, and can also be accessed from any method on the extractot instance (
duh).

This is useful for cases where you have a farily generic prompt where some specifics like a desired date format, list of
fields or additional context can be injected to make the extractor reusable in many different scenarios.

Here is an example using a fairly naive "contact list" extractor where we can configure the fields we want by providing
them

```php
Engine::extend("contact-list", fn() => ContactListExtractor([
    "fields" = [
        "name",
        "phone",
        "email",
    ]
));
```

Then we can use that configuration inside the prompt like so:

```blade
Extract the following fields from the document below:

@foreach($config["fields"] as $field)
- {{ $field }}
@endforeach

DOCUMENT:

{{ $input }}

OUTPUT AS JSON:
```

Or you could use it to specify additional context like this:

```php
Engine::extend("dateExtractor", fn() => new  DateExtractor([
    "format" => "Y-m-d"
]);
```

Then we can use that configuration inside the prompt like so:

```blade
Given a text snippet, extract any mentioned date, adhering to a specified format. 

SNIPPET:
"The event is scheduled for 23/10/2023 at the city park."

DATE FORMAT:  
"{{ $config["format"] }}"

Output:
```

You may also override or set any config when calling the extractor, which is more flexible as it allows you to pass in
dynamic data as needed.

```php
Extractor::extend("contacts", fn() => Extractors\Payslip::class);
Extractor::extract(
    extractor: "contacts", 
    input: Text::web("https://example.com/contact-us"),
    config: [
        "fields" => ["name", "email"],
        "additionalContext" => "Only extract contact details for employees, not generic contact@, noreply@ or email@ addresses"
        "domain" => "example.com"
    ]
]);
```

Use your creativity, I'm sure you will discover vastly more interesting use cases than this contrived "contact list"
example, the contact list example is used because it is fairly straight forward to understand and makes for a
beginner-friendly use-case.

### Registering Custom Extractors

When you have made your own Extractor class, you want to register it with the main Extractor class, this is done by
calling the `extend()` method.

This method takes two arguments: the extractor name (a string) and a closure that returns the extractor class.

Here's an example of how to register custom extractors:

```php


// Register using closure
Extractor::extend("receipt", fn() => Extractors\Receipt::class);

// Register using Instance
Extractor::extend("rundown", fn() => new Extractors\Rundown(["columns" => ["#", "time", "notes", "sound", "presentation"]]));
```

## All Parameters and Their Functions

**`$text` (TextContent|string)**

The input text or data that needs to be processed. It accepts either a `TextContent` object or a
string.

**`$model` (Model)**

This parameter specifies the OpenAI model used for the extraction process.

It accepts a `Model` enum value. The default model is `Model::TURBO_INSTRUCT`. Different models have different
speed/accuracy characteristics.

Available Models:

- `Model::TURBO_INSTRUCT` – Uses the `gpt-3.5-turbo-instruct` model, Fastest with 7/10 accuracy.
- `Model::TURBO_16K` – Uses the `gpt-3.5-turbo-16k` model, Fast with 8/10 accuracy, accepts longer input.
- `Model::TURBO` – Uses the `gpt-3.5-turbo` model, Same as TURBO_16K, but accepts shorter input.
- `Model::GPT4` – Uses the `gpt-4` model, Slower but with 9.5/10 accuracy.
- `Model::GPT4_32K` – Uses the `gpt-4-32k` model, Same as GPT4, but accepts longer input.

**`$maxTokens` (int)**

The maximum number of tokens that the model will process.
The default value is `2000`, and adjusting this value may be necessary for very long text. A value of 2000 is usually
sufficient.

**`$temperature` (float)**

Controls the randomness/creativity of the model's output.

A higher value (e.g., 0.8) makes the output more random, which is usually not desired in this context. A recommended
value is 0.1 or 0.2; anything over 0.5 tends to be less useful. The default is `0.1`.
