<?php

use HelgeSverre\Extractor\ExtractorServiceProvider;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;

beforeEach(function () {
    // Configure the custom OpenAI base URI for Ollama
    config()->set('extractor.openai_base_uri', 'http://localhost:11434/v1');
    config()->set('openai.api_key', 'ollama'); // Ollama doesn't need a real API key

    // Re-register the service provider to pick up the new config
    $this->app->register(ExtractorServiceProvider::class, true);
});

it('can extract fields using Ollama with mistral-nemo', function () {
    $sample = Text::text('John Doe, john@example.com, +1 555-1234');

    $data = Extractor::fields($sample,
        fields: ['name', 'email', 'phone'],
        model: 'mistral-nemo:latest',
        maxTokens: 500,
    );

    expect($data)->toBeArray()
        ->and($data['name'])->toContain('John')
        ->and($data['email'])->toBe('john@example.com')
        ->and($data['phone'])->toContain('555');
})->group('ollama');

it('can extract fields using Ollama with qwen2.5-coder', function () {
    $sample = Text::text('Jane Smith works at Acme Corp in New York. Her email is jane.smith@acme.com');

    $data = Extractor::fields($sample,
        fields: [
            'name' => 'full name of the person',
            'company' => 'company name',
            'location' => 'city or location',
            'email' => 'email address',
        ],
        model: 'qwen2.5-coder:7b',
        maxTokens: 500,
    );

    expect($data)->toBeArray()
        ->and($data['name'])->toContain('Jane')
        ->and($data['company'])->toContain('Acme')
        ->and($data['email'])->toBe('jane.smith@acme.com');
})->group('ollama');

it('can extract structured data from a receipt using Ollama', function () {
    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/electronics.pdf'));

    $data = Extractor::fields($sample,
        fields: [
            'merchant' => 'name of the store or merchant',
            'date' => 'date of purchase in YYYY-MM-DD format',
            'total' => 'total amount as a number',
            'currency' => 'currency code (NOK, USD, EUR, etc)',
        ],
        model: 'mistral-nemo:latest',
        maxTokens: 1000,
    );

    expect($data)->toBeArray()
        ->and($data)->toHaveKey('merchant')
        ->and($data)->toHaveKey('date')
        ->and($data)->toHaveKey('total')
        ->and($data['currency'])->toBe('NOK');
})->group('ollama');
