<?php

namespace HelgeSverre\Extractor\Tests;

use Dotenv\Dotenv;
use HelgeSverre\Extractor\ExtractorServiceProvider;
use OpenAI\Laravel\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ExtractorServiceProvider::class,
            ServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }

    /** @noinspection LaravelFunctionsInspection */
    public function getEnvironmentSetUp($app)
    {
        // Load .env.test into the environment.
        if (file_exists(dirname(__DIR__).'/.env')) {
            (Dotenv::createImmutable(dirname(__DIR__), '.env'))->load();
        }

        config()->set('database.default', 'testing');

        config()->set('openai.api_key', env('OPENAI_API_KEY'));

        config()->set('extractor.textract_timeout', 30);
        config()->set('extractor.textract_polling_interval', 2);

        config()->set('extractor.textract_disk', 'textract');
        config()->set('extractor.textract_region', env('TEXTRACT_REGION'));
        config()->set('extractor.textract_version', env('TEXTRACT_VERSION'));
        config()->set('extractor.textract_key', env('TEXTRACT_KEY'));
        config()->set('extractor.textract_secret', env('TEXTRACT_SECRET'));

        // Use same config as the textract bucket, for testing.
        config()->set('filesystems.disks.textract.driver', 's3');
        config()->set('filesystems.disks.textract.key', env('TEXTRACT_KEY'));
        config()->set('filesystems.disks.textract.secret', env('TEXTRACT_SECRET'));
        config()->set('filesystems.disks.textract.region', env('TEXTRACT_REGION'));
        config()->set('filesystems.disks.textract.bucket', env('TEXTRACT_BUCKET'));

    }
}
