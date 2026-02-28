<?php

use HelgeSverre\Extractor\ContactDto;
use HelgeSverre\Extractor\Extraction\Builtins\Contacts;

describe('Contacts Extractor', function () {
    it('has the correct name', function () {
        $extractor = new Contacts;

        expect($extractor->name())->toBe('contacts');
    });

    it('has validation rules', function () {
        $extractor = new Contacts;

        $rules = $extractor->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('*.name');
        expect($rules)->toHaveKey('*.email');
        expect($rules)->toHaveKey('*.phone');
        expect($rules)->toHaveKey('*.title');
    });

    it('is a collection DTO extractor', function () {
        $extractor = new Contacts;

        expect($extractor->isCollection())->toBeTrue();
    });

    it('has correct data class', function () {
        $extractor = new Contacts;

        expect($extractor->dataClass())->toBe(ContactDto::class);
    });
});
