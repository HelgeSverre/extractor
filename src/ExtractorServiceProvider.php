<?php

namespace HelgeSverre\Extractor;

use Aws\Textract\TextractClient;
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
            $this->package->basePath('/../resources/prompts') => base_path("resources/views/vendor/{$this->packageView($this->package->viewNamespace)}"),
        ], "{$this->packageView($this->package->viewNamespace)}-prompts");

        $this->app->singleton(TextLoaderFactory::class, fn ($app) => new TextLoaderFactory($app));

        $this->app->bind(TextractClient::class, fn () => new TextractClient([
            'region' => config('extractor.textract_region'),
            'version' => config('extractor.textract_version'),
            'credentials' => [
                'key' => config('extractor.textract_key'),
                'secret' => config('extractor.textract_secret'),
            ],
        ]));
    }
}
