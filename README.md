<p align="center"><img src=".github/header.png"></p>

# Extractor: AI-Powered Data Extraction for your Laravel applicaiton.

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

### Extracting data from other formats

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

After loading the data, you can pass the `TextContent` or the plain text (which can be obtained by
calling `->toString()`) to
the `Extractor::extract()` method.

```php
use HelgeSverre\Extractor\Facades\Extractor;

Extractor::extract($textPlainText);
Extractor::extract($textPdf);
Extractor::extract($textImageOcr);
Extractor::extract($textPdfOcr);
Extractor::extract($textWord);
Extractor::extract($textWeb);
Extractor::extract($textHtml);
```

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

## Registering Custom Extractors

You can extend the functionality of the `Extractor` by registering custom extractors for specific data types or formats.
This allows you to tailor the extraction process to your specific needs.

To register a custom extractor, use the `Extractor::extend` method. This method takes two arguments: the extractor
name (a string) and a closure that returns the extractor class.

Here's an example of how to register custom extractors:

```php
use HelgeSverre\Extractor\Engine;

// Register a custom receipt extractor
Engine::extend("receipt", fn() => Extractors\Receipt::class);

// Register a custom rundown extractor
Engine::extend("rundown", fn() => Extractors\Rundown::class);

// Register a custom payslip extractor
Engine::extend("payslip", fn() => Extractors\Payslip::class);
```

In the code above:

- `"receipt"`, `"rundown"`, and `"payslip"` are the names you assign to your custom extractors. You can use these names
  later when calling the `Extractor::extract` method to specify which custom extractor to use.
- `fn() => Extractors\Receipt::class`, `fn() => Extractors\Rundown::class`, and `fn() => Extractors\Payslip::class` are
  closures that return the class names of your custom extractors.

Once you have registered a custom extractor, you can use it as follows:

```php
use HelgeSverre\Extractor\Facades\Extractor;

// Extract data using the custom "receipt" extractor
$receiptData = Extractor::extract($text, extractor: "receipt");

// Extract data using the custom "rundown" extractor
$rundownData = Extractor::extract($text, extractor: "rundown");

// Extract data using the custom "payslip" extractor
$payslipData = Extractor::extract($text, extractor: "payslip");
```

By registering custom extractors, you can easily expand the capabilities of the `Extractor` to handle various data types
and formats specific to your application.

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

**`$template` (string)**

This parameter specifies the template used for the prompt.

The default template is `'data'`. You can create and use
additional templates by adding new blade files in the `resources/views/vendor/extractor/` directory and specifying
the file name (without extension) as the `$template` value (e.g., `"minimal_data"`).

**`$asArray` (bool)**

If set to true, the response from the AI model is returned as an array instead of the default data structure. This is
useful if you want to modify the default structure or convert the response into your own data structure. The default
is `false`.

### Example Usage:

```php
use HelgeSverre\Extractor\Facades\Extractor;

$extractedData = Extractor::extract(
    $textInput,
    model: Model::TURBO_INSTRUCT,
    maxTokens: 500,
    temperature: 0.2,
    template: 'minimal_data',
    asArray: true
);
```

### List of Supported Models

| Enum Value     | Model Name             | Endpoint   |
|----------------|------------------------|------------|
| TURBO_INSTRUCT | gpt-3.5-turbo-instruct | Completion |
| TURBO_16K      | gpt-3.5-turbo-16k      | Chat       |
| TURBO          | gpt-3.5-turbo          | Chat       |
| GPT4           | gpt-4                  | Chat       |
| GPT4_32K       | gpt-4-32               | Chat       |

## OCR Configuration with AWS Textract

To use AWS Textract for extracting text from large images and multi-page PDFs,
the package uploads the
