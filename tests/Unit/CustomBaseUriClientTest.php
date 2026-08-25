<?php

declare(strict_types=1);

use HelgeSverre\Extractor\ExtractorServiceProvider;
use OpenAI\Client as OpenAIClient;
use OpenAI\Contracts\ClientContract;

/**
 * Re-boots the package so `packageBooted()` re-evaluates the custom base URI
 * branch against the config set inside the test.
 */
function rebootWithBaseUri(array $config): OpenAIClient
{
    foreach ($config as $key => $value) {
        config()->set($key, $value);
    }

    $provider = new ExtractorServiceProvider(app());
    $provider->register();
    $provider->boot();

    app()->forgetInstance(ClientContract::class);

    return app(ClientContract::class);
}

function transporterHeaders(OpenAIClient $client): array
{
    $transporter = (new ReflectionProperty($client, 'transporter'))->getValue($client);
    $headers = (new ReflectionProperty($transporter, 'headers'))->getValue($transporter);

    return array_change_key_case($headers->toArray());
}

it('builds a client for a keyless local provider without authorization', function () {
    $client = rebootWithBaseUri([
        'extractor.openai_base_uri' => 'http://localhost:11434/v1',
        'openai.api_key' => null,
        'openai.organization' => null,
        'openai.project' => null,
    ]);

    expect($client)->toBeInstanceOf(OpenAIClient::class)
        ->and(transporterHeaders($client))->not->toHaveKey('authorization');
});

it('does not send the removed Assistants API beta header', function () {
    $client = rebootWithBaseUri([
        'extractor.openai_base_uri' => 'http://localhost:11434/v1',
        'openai.api_key' => 'sk-test',
    ]);

    expect(transporterHeaders($client))->not->toHaveKey('openai-beta');
});

it('forwards the configured OpenAI project', function () {
    $client = rebootWithBaseUri([
        'extractor.openai_base_uri' => 'https://example.test/v1',
        'openai.api_key' => 'sk-test',
        'openai.project' => 'proj_123',
    ]);

    expect(transporterHeaders($client))->toHaveKey('openai-project', 'proj_123');
});
