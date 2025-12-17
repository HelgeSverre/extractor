<?php

use HelgeSverre\Extractor\Text\ImageContent;

describe('ImageContent', function () {
    describe('factory methods', function () {
        it('creates URL type via url() method', function () {
            $image = ImageContent::url('https://example.com/image.jpg');

            expect($image)->toBeInstanceOf(ImageContent::class);
            expect($image->isUrl())->toBeTrue();
            expect($image->isFile())->toBeFalse();
            expect($image->isRaw())->toBeFalse();
            expect($image->type())->toBe(ImageContent::TYPE_URL);
            expect($image->content())->toBe('https://example.com/image.jpg');
        });

        it('creates FILE type via file() method', function () {
            $image = ImageContent::file('/path/to/image.jpg');

            expect($image)->toBeInstanceOf(ImageContent::class);
            expect($image->isFile())->toBeTrue();
            expect($image->isUrl())->toBeFalse();
            expect($image->isRaw())->toBeFalse();
            expect($image->type())->toBe(ImageContent::TYPE_FILE);
        });

        it('creates RAW type via raw() method', function () {
            $image = ImageContent::raw('raw image bytes');

            expect($image)->toBeInstanceOf(ImageContent::class);
            expect($image->isRaw())->toBeTrue();
            expect($image->isUrl())->toBeFalse();
            expect($image->isFile())->toBeFalse();
            expect($image->type())->toBe(ImageContent::TYPE_RAW);
        });

        it('accepts custom mime type for file', function () {
            $image = ImageContent::file('/path/to/image.webp', 'image/webp');

            expect($image->mime())->toBe('image/webp');
        });

        it('accepts custom mime type for raw', function () {
            $image = ImageContent::raw('raw bytes', 'image/png');

            expect($image->mime())->toBe('image/png');
        });
    });

    describe('isBase64able', function () {
        it('returns true for file type', function () {
            $image = ImageContent::file('/path/to/image.jpg');
            expect($image->isBase64able())->toBeTrue();
        });

        it('returns true for raw type', function () {
            $image = ImageContent::raw('raw bytes');
            expect($image->isBase64able())->toBeTrue();
        });

        it('returns false for url type', function () {
            $image = ImageContent::url('https://example.com/image.jpg');
            expect($image->isBase64able())->toBeFalse();
        });
    });

    describe('type checking uses strict equality', function () {
        it('isUrl uses strict comparison', function () {
            $image = ImageContent::url('https://example.com/image.jpg');
            expect($image->isUrl())->toBeTrue();

            $fileImage = ImageContent::file('/path/to/file.jpg');
            expect($fileImage->isUrl())->toBeFalse();
        });

        it('isFile uses strict comparison', function () {
            $image = ImageContent::file('/path/to/image.jpg');
            expect($image->isFile())->toBeTrue();

            $urlImage = ImageContent::url('https://example.com/image.jpg');
            expect($urlImage->isFile())->toBeFalse();
        });

        it('isRaw uses strict comparison', function () {
            $image = ImageContent::raw('raw bytes');
            expect($image->isRaw())->toBeTrue();

            $urlImage = ImageContent::url('https://example.com/image.jpg');
            expect($urlImage->isRaw())->toBeFalse();
        });
    });

    describe('base64 encoding', function () {
        it('encodes raw content to base64', function () {
            $image = ImageContent::raw('Hello World', 'text/plain');

            expect($image->toBase64())->toBe(base64_encode('Hello World'));
        });

        it('creates base64 URL with mime type', function () {
            $image = ImageContent::raw('Hello World', 'image/png');

            expect($image->toBase64Url())->toBe('data:image/png;base64,'.base64_encode('Hello World'));
        });
    });
});
