<p align="center"><img src=".github/header.png"></p>

# Extractor: AI-Powered Data Extraction Library for Laravel.

![Latest Version on Packagist](https://img.shields.io/packagist/v/helgesverre/extractor.svg?style=flat-square)
![Total Downloads](https://img.shields.io/packagist/dt/helgesverre/extractor.svg?style=flat-square)

Effortlessly extract structured data from various sources, including images, PDFs, and emails, using OpenAI within your
Laravel application.

## Features

- A convenient wrapper around OpenAI Chat and Completion endpoints.
- Takes text as input and returns an array, or spatie/data in return,.
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

## OCR Configuration with AWS Textract

To use AWS Textract for extracting text from large images and multi-page PDFs,
the package needs to upload the file to S3 and pass the s3 object location along to the textract service.

So you need to configure your AWS Credentials in the `config/extractor.php` file as follows:

```dotenv
TEXTRACT_KEY="your-aws-access-key"
TEXTRACT_SECRET="your-aws-security"
TEXTRACT_REGION="your-textract-region"

# Can be omitted
TEXTRACT_VERSION="2018-06-27"
```

You also need to configure a seperate Textract disk where the files will be stored,
open your  `config/filesystems.php` configuration file and add the following:

```php
'textract' => [
    'driver' => 's3',
    'key' => env('TEXTRACT_KEY'),
    'secret' => env('TEXTRACT_SECRET'),
    'region' => env('TEXTRACT_REGION'),
    'bucket' => env('TEXTRACT_BUCKET'),
],
```

Ensure the `textract_disk` setting in `config/extractor.php` is the same as your disk name in
the `filesystems.php`
config, you can change it with the .env value `TEXTRACT_DISK`.

```php
return [
    "textract_disk" => env("TEXTRACT_DISK")
];
```

`.env`

```dotenv
TEXTRACT_DISK="uploads"
```

**Note**

Textract is not available in all regions:

> Q: In which AWS regions is Amazon Textract available?
> Amazon Textract is currently available in the US East (Northern Virginia), US East (Ohio), US West (Oregon), US West (
> N. California), AWS GovCloud (US-West), AWS GovCloud (US-East), Canada (Central), EU (Ireland), EU (London), EU (
> Frankfurt), EU (Paris), Asia Pacific (Singapore), Asia Pacific (Sydney), Asia Pacific (Seoul), and Asia Pacific (
> Mumbai)
> Regions.

See: https://aws.amazon.com/textract/faqs/

## Usage

### Extracting plain text from documents

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

## Extracting structured data

The `Extractor` package includes a set of pre-built extractors designed to simplify the extraction of structured data
from various types of text. Each extractor is optimized for specific data formats, making it easy to process different
types of information. Below is a list of the included extractors along with brief descriptions and convenient shortened
methods for each:

| Example                                                          | Extractor | Description                                                                                                         |
|------------------------------------------------------------------|-----------|---------------------------------------------------------------------------------------------------------------------|
| `Extractor::extract(Contacts::class, $text);`                    | Contacts  | Extracts a list of contacts (name, title, email, phone).                                                            |
| `Extractor::extract(Receipt::class, $text);`                     | Receipt   | Extracts common Receipt data, See [receipt-scanner](https://github.com/HelgeSverre/receipt-scanner) for details.    |
| `Extractor::fields($text, fields: ["name","address", "phone"]);` | Fields    | Extracts arbitrary fields provided as an array of output key, and optional description, also supports nested fields |

These extractors are provided out of the box and offer a convenient way to extract specific types of structured data
from text. You can use the shortened methods to easily access the functionality of each extractor.

## Using the Field extractor

The field extractor is great if you don't need much custom logic or validation and just want to extract out some
structured data from a piece of text.

Here is an example of extracting information from a CV, note that providing a description to guide the AI model is
supported, as well as nested items (which is useful for lists of sub-items, like work history, line items, comments on a
product etc )

```php
$sample = Text::pdf(file_get_contents(__DIR__.'/../samples/helge-cv.pdf'));

$data = Extractor::fields($sample,
    fields: [
        'name' => 'the name of the candidate',
        'email',
        'certifications' => 'list of certifications, if any',
        'workHistory' => [
            'companyName',
            'from' => 'Y-m-d if available, Year only if not, null if missing',
            'to' => 'Y-m-d if available, Year only if not, null if missing',
            'text',
        ],
    ],
    model: Engine::GPT_3_TURBO_1106,
);
```

## Creating Custom Extractors

Custom extractors in Extractor allow for tailored data extraction to meet specific needs. Here's how you can create and
use a custom extractor, using the example of a Job Posting Extractor.

### Implementing a Custom Extractor

Create a new class for your custom extractor by extending the `Extractor` class. In this example, we'll create
a `JobPostingExtractor` to extract key information from job postings:

```php
<?php

namespace App\Extractors;

use HelgeSverre\Extractor\Extraction\Extractor;use HelgeSverre\Extractor\Text\TextContent;

class JobPostingExtractor extends Extractor
{
    public function prompt(string|TextContent $input): string
    {
        $outputKey = $this->expectedOutputKey();

        return "Extract the following fields from the job posting below:"
            . "\n- jobTitle: The title or designation of the job."
            . "\n- companyName: The name of the company or organization posting the job."
            . "\n- location: The geographical location or workplace where the job is based."
            . "\n- jobType: The nature of employment (e.g., Full-time, Part-time, Contract)."
            . "\n- description: A brief summary or detailed description of the job."
            . "\n- applicationDeadline: The closing date for applications, if specified."
            . "\n\nThe output should be a JSON object under the key '{$outputKey}'."
            . "\n\nINPUT STARTS HERE\n\n$input\n\nOUTPUT IN JSON:\n";
    }

    public function expectedOutputKey(): string
    {
        return 'extractedData';
    }
}
```

**Note**: Adding an instruction on which `$outputKey` key to nest the data under is recommended, as the JsonMode
response from OpenAI end to want to put everything under a root key, by overriding the   `expectedOutputKey()` method,
it will tell the base Extractor class which key to pull the data from.

### Registering the Custom Extractor

After defining your custom extractor, register it with the main Extractor class using the `extend` method:

```php
use HelgeSverre\Extractor\Extractor;

Extractor::extend("job-posting", fn() => new JobPostingExtractor());
```

### Using the Custom Extractor

Once registered, you can use your custom extractor just like the built-in ones. Here's an example of how to use
the `JobPostingExtractor`:

```php
use HelgeSverre\Extractor\Facades\Text;
use HelgeSverre\Extractor\Extractor;

$jobPostingContent = Text::web("https://www.finn.no/job/fulltime/ad.html?finnkode=329443482");

$extractedData = Extractor::extract('job-posting', $jobPostingContent);
// Or you can specify the class-string instead
// ex: Extractor::extract(JobPostingExtractor::class, $jobPostingContent);

// $extractedData now contains structured information from the job posting
```

With the `JobPostingExtractor`, you can efficiently parse and extract key information from job postings, structuring it
in a way that's easy to manage and use within your Laravel application.

### Adding Validation to the Job Posting Extractor

To ensure the integrity of the extracted data, you can add validation rules to your Job Posting Extractor. This is done
by using the `HasValidation` trait and defining validation rules in the `rules` method:

```php
<?php

namespace App\Extractors;

use HelgeSverre\Extractor\Extraction\Concerns\HasValidation;
use HelgeSverre\Extractor\Extraction\Extractor;

class JobPostingExtractor extends Extractor
{
    use HasValidation;

    public function rules(): array
    {
        return [
            'jobTitle' => ['required', 'string'],
            'companyName' => ['required', 'string'],
            'location' => ['required', 'string'],
            'jobType' => ['required', 'string'],
            'salary' => ['required', 'numeric'],
            'description' => ['required', 'string'],
            'applicationDeadline' => ['required', 'date']
        ];
    }
}
```

This will ensure that each key field in the job posting data meets the specified criteria, enhancing the reliability of
your data extraction.

### Extracting Data into a DTO

Extractor can integrate with `spatie/data` to cast the extracted data into a Data Transfer Object (DTO) of your
choosing. To do this, add the `HasDto` trait to your extractor and specify the DTO class in the `dataClass` method:

```php
<?php

namespace App\Extractors;

use DateTime;
use App\Extractors\JobPostingDto;
use HelgeSverre\Extractor\Extraction\Concerns\HasDto;
use HelgeSverre\Extractor\Extraction\Extractor;
use Spatie\LaravelData\Data;

class JobPostingDto extends Data
{
    public function __construct(
        public string $jobTitle,
        public string $companyName,
        public string $location,
        public string $jobType,
        public int|float $salary,
        public string $description,
        public DateTime $applicationDeadline
    ) {
    }
}

class JobPostingExtractor extends Extractor
{
    use HasDto;

    public function dataClass(): string
    {
        return JobPostingDto::class;
    }

    public function isCollection(): bool
    {
        return false; 
    }
}
```

## All Parameters and Their Functions

**`$input` (TextContent|string)**

The input text or data that needs to be processed. It accepts either a `TextContent` object or a string.

**`$model` (Model)**

This parameter specifies the OpenAI model used for the extraction process.

It accepts a `string` value. Different models have different speed/accuracy characteristics and use cases, for
convenience, most of the accepted models are provided as constants on the `Engine` class.

Available Models:

| Model Identifier               | Model                    | Note                                                                                                                                                                                           |
|--------------------------------|--------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Engine::GPT_4_1106_PREVIEW`   | 'gpt-4-1106-preview'     | GPT-4 Turbo, featuring improved instruction following, JSON mode, reproducible outputs, parallel function calling. Maximum 4,096 output tokens. Preview model, not yet for production traffic. |
| `Engine::GPT_3_TURBO_1106`     | 'gpt-3.5-turbo-1106'     | Updated GPT-3.5 Turbo, with improvements similar to GPT-4 Turbo. Returns up to 4,096 output tokens.                                                                                            |
| `Engine::GPT_4`                | 'gpt-4'                  | Large multimodal model, capable of solving complex problems with greater accuracy. Suited for both chat and traditional completions tasks.                                                     |
| `Engine::GPT4_32K`             | 'gpt-4-32k'              | Extended version of GPT-4 with a larger context window of 32,768 tokens.                                                                                                                       |
| `Engine::GPT_3_TURBO_INSTRUCT` | 'gpt-3.5-turbo-instruct' | Similar to `text-davinci-003`, optimized for legacy Completions endpoint, not for Chat Completions.                                                                                            |
| `Engine::GPT_3_TURBO_16K`      | 'gpt-3.5-turbo-16k'      | Extended version of GPT-3.5 Turbo, supporting a larger context window of 16,385 tokens.                                                                                                        |
| `Engine::GPT_3_TURBO`          | 'gpt-3.5-turbo'          | Optimized for chat using the Chat Completions API, suitable for traditional completion tasks.                                                                                                  |
| `Engine::TEXT_DAVINCI_003`     | 'text-davinci-003'       | Legacy model, better quality and consistency for language tasks. To be deprecated on Jan 4, 2024.                                                                                              |
| `Engine::TEXT_DAVINCI_002`     | 'text-davinci-002'       | Similar to `text-davinci-003` but trained with supervised fine-tuning. To be deprecated on Jan 4, 2024.                                                                                        |

**`$maxTokens` (int)**

The maximum number of tokens that the model will process.
The default value is `2000`, and adjusting this value may be necessary for very long text. A value of 2000 is usually
sufficient.

**`$temperature` (float)**

Controls the randomness/creativity of the model's output.

A higher value (e.g., 0.8) makes the output more random, which is usually not desired in this context. A recommended
value is 0.1 or 0.2; anything over 0.5 tends to be less useful. The default is `0.1`.

## License

This package is licensed under the MIT License. For more details, refer to the [License File](LICENSE.md).
