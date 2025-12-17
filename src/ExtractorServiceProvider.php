<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor;

use Aws\Textract\TextractClient;
use GuzzleHttp\Client as GuzzleClient;
use HelgeSverre\Extractor\Text\Factory;
use OpenAI;
use OpenAI\Client as OpenAIClient;
use OpenAI\Contracts\ClientContract;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ExtractorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('extractor')->hasConfigFile();
    }

    public function packageBooted()
    {
        $this->loadViewsFrom($this->package->basePath('/../resources/prompts'), 'extractor');

        $this->publishes([
            $this->package->basePath('/../resources/prompts') => base_path('resources/views/vendor/extractor'),
        ], 'extractor-prompts');

        $this->app->singleton(Factory::class, fn ($app) => new Factory($app));

        $this->app->bind(TextractClient::class, fn () => new TextractClient([
            'region' => config('extractor.textract_region'),
            'version' => config('extractor.textract_version'),
            'credentials' => [
                'key' => config('extractor.textract_key'),
                'secret' => config('extractor.textract_secret'),
            ],
        ]));

        // Override OpenAI client if custom base URI is configured
        if (config('extractor.openai_base_uri')) {
            $this->app->singleton(ClientContract::class, fn (): OpenAIClient => OpenAI::factory()
                ->withApiKey(config('openai.api_key'))
                ->withOrganization(config('openai.organization'))
                ->withBaseUri(config('extractor.openai_base_uri'))
                ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
                ->withHttpClient(new GuzzleClient(['timeout' => config('openai.request_timeout', 30)]))
                ->make());
        }
    }
}
