<?php

use HelgeSverre\Extractor\Drivers\OllamaDriver;
use HelgeSverre\Extractor\Extraction\Builtins\Contacts;

it('can extract contact list from text sample with ollama mistral instruct model', function () {
    $sample = file_get_contents(__DIR__.'/../samples/contacts.txt');

    $ollama = new OllamaDriver();

    $data = $ollama->run(
        new Contacts,
        $sample,
        model: 'mistral:7b-instruct',
        maxTokens: 2000,
        temperature: 0.1,
    );

    dump($data);
});
