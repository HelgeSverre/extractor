<?php

declare(strict_types=1);

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;
use HelgeSverre\Extractor\Text\ImageContent;

// Skip all tests in this file if no OpenAI API key is set
beforeEach(function () {
    if (empty(env('OPENAI_API_KEY'))) {
        $this->markTestSkipped('OPENAI_API_KEY not set');
    }
});

describe('GPT-4o family', function () {
    it('can extract fields using gpt-4o-mini', function () {
        $sample = Text::text('John Doe, john@example.com, Software Engineer at Acme Corp');

        $data = Extractor::fields($sample,
            fields: ['name', 'email', 'job_title', 'company'],
            model: Engine::GPT_4O_MINI,
            maxTokens: 500,
        );

        expect($data)->toBeArray()
            ->and($data['name'])->toContain('John')
            ->and($data['email'])->toBe('john@example.com')
            ->and($data['company'])->toContain('Acme');
    });

    it('can extract fields using gpt-4o', function () {
        $sample = Text::text('Jane Smith, jane@example.com, CTO');

        $data = Extractor::fields($sample,
            fields: ['name', 'email', 'job_title'],
            model: Engine::GPT_4O,
            maxTokens: 500,
        );

        expect($data)->toBeArray()
            ->and($data['name'])->toContain('Jane')
            ->and($data['email'])->toBe('jane@example.com');
    });
})->group('openai');

describe('O-series reasoning models', function () {
    it('can extract fields using o4-mini', function () {
        $sample = Text::text('Invoice #12345 from Acme Corp, dated 2025-01-15, total $1,234.56 USD');

        $data = Extractor::fields($sample,
            fields: [
                'invoice_number',
                'company',
                'date' => 'in YYYY-MM-DD format',
                'total' => 'as a number',
                'currency',
            ],
            model: Engine::O4_MINI,
            maxTokens: 2000,
        );

        expect($data)->toBeArray()
            ->and($data['invoice_number'])->toContain('12345')
            ->and($data['company'])->toContain('Acme')
            ->and($data['date'])->toBe('2025-01-15')
            ->and($data['currency'])->toBe('USD');
    });

    it('can extract fields using o3-mini', function () {
        $sample = Text::text('Meeting: Project Review, Date: March 5 2026, Attendees: Alice, Bob, Charlie');

        $data = Extractor::fields($sample,
            fields: [
                'meeting_name',
                'date' => 'in YYYY-MM-DD format',
                'attendees' => 'list of attendee names',
            ],
            model: Engine::O3_MINI,
            maxTokens: 1000,
        );

        expect($data)->toBeArray()
            ->and($data['meeting_name'])->toContain('Project Review')
            ->and($data['date'])->toBe('2026-03-05')
            ->and($data['attendees'])->toBeArray()
            ->and($data['attendees'])->toContain('Alice');
    });
})->group('openai', 'reasoning');

describe('GPT-5 family', function () {
    it('can extract fields using gpt-5-mini', function () {
        $sample = Text::text('Product: MacBook Pro 16", Price: $2,499.00, SKU: MBP16-M4-2025, In Stock: Yes');

        $data = Extractor::fields($sample,
            fields: [
                'product_name',
                'price' => 'as a number',
                'sku',
                'in_stock' => 'boolean',
            ],
            model: Engine::GPT_5_MINI,
            maxTokens: 1000,
        );

        expect($data)->toBeArray()
            ->and($data['product_name'])->toContain('MacBook')
            ->and($data['sku'])->toBe('MBP16-M4-2025')
            ->and($data['in_stock'])->toBeTrue();
    });

    it('can extract fields using gpt-5', function () {
        $sample = Text::text('Patient: John Smith, DOB: 1985-03-15, Blood Type: O+, Allergies: Penicillin, Shellfish');

        $data = Extractor::fields($sample,
            fields: [
                'patient_name',
                'date_of_birth' => 'in YYYY-MM-DD format',
                'blood_type',
                'allergies' => 'list of allergies',
            ],
            model: Engine::GPT_5,
            maxTokens: 1000,
        );

        expect($data)->toBeArray()
            ->and($data['patient_name'])->toContain('John Smith')
            ->and($data['date_of_birth'])->toBe('1985-03-15')
            ->and($data['blood_type'])->toBe('O+')
            ->and($data['allergies'])->toBeArray()
            ->and($data['allergies'])->toContain('Penicillin');
    });

    it('can extract fields using gpt-5-nano', function () {
        $sample = Text::text('Restaurant: Sushi Palace, Rating: 4.5/5, Cuisine: Japanese, Price Range: $$$');

        $data = Extractor::fields($sample,
            fields: [
                'restaurant_name',
                'rating' => 'as a number',
                'cuisine',
                'price_range',
            ],
            model: Engine::GPT_5_NANO,
            maxTokens: 500,
        );

        expect($data)->toBeArray()
            ->and($data['restaurant_name'])->toContain('Sushi')
            ->and($data['cuisine'])->toContain('Japanese');
    });

    it('can extract fields using gpt-5.1', function () {
        $sample = Text::text('Flight: SK4601, From: Oslo (OSL), To: Stockholm (ARN), Departure: 2026-04-15 08:30, Gate: B12');

        $data = Extractor::fields($sample,
            fields: [
                'flight_number',
                'origin_city',
                'origin_code',
                'destination_city',
                'departure_date' => 'YYYY-MM-DD',
                'gate',
            ],
            model: Engine::GPT_5_1,
            maxTokens: 1000,
        );

        expect($data)->toBeArray()
            ->and($data['flight_number'])->toContain('SK4601')
            ->and($data['origin_code'])->toBe('OSL')
            ->and($data['destination_city'])->toContain('Stockholm')
            ->and($data['gate'])->toBe('B12');
    });

    it('can extract fields using gpt-5.2', function () {
        $sample = Text::text('Book: "The Great Gatsby" by F. Scott Fitzgerald, Published: 1925, Genre: Fiction, Pages: 180');

        $data = Extractor::fields($sample,
            fields: [
                'title',
                'author',
                'year' => 'as a number',
                'genre',
                'pages' => 'as a number',
            ],
            model: Engine::GPT_5_2,
            maxTokens: 1000,
        );

        expect($data)->toBeArray()
            ->and($data['title'])->toContain('Great Gatsby')
            ->and($data['author'])->toContain('Fitzgerald')
            ->and($data['year'])->toBe(1925)
            ->and($data['pages'])->toBe(180);
    });

    it('can extract fields using gpt-5.4', function () {
        $sample = Text::text('Company: Stripe Inc., CEO: Patrick Collison, Founded: 2010, HQ: San Francisco, Employees: 8000+');

        $data = Extractor::fields($sample,
            fields: [
                'company_name',
                'ceo',
                'founded_year' => 'as a number',
                'headquarters',
            ],
            model: Engine::GPT_5_4,
            maxTokens: 1000,
        );

        expect($data)->toBeArray()
            ->and($data['company_name'])->toContain('Stripe')
            ->and($data['ceo'])->toContain('Collison')
            ->and($data['founded_year'])->toBe(2010)
            ->and($data['headquarters'])->toContain('San Francisco');
    });
})->group('openai', 'gpt5');

describe('O1 reasoning models', function () {
    it('can extract fields using o1', function () {
        $sample = Text::text('Server: web-prod-03, CPU: 87%, Memory: 12.4GB/16GB, Disk: 450GB/500GB, Status: Warning');

        $data = Extractor::fields($sample,
            fields: [
                'server_name',
                'cpu_percent' => 'as a number',
                'memory_used_gb' => 'as a number',
                'memory_total_gb' => 'as a number',
                'status',
            ],
            model: Engine::O1,
            maxTokens: 1000,
        );

        expect($data)->toBeArray()
            ->and($data['server_name'])->toBe('web-prod-03')
            ->and($data['cpu_percent'])->toBe(87)
            ->and($data['status'])->toContain('Warning');
    });
})->group('openai', 'reasoning');

describe('GPT-5 vision', function () {
    it('can extract data from an image using gpt-5-mini', function () {
        $image = ImageContent::file(__DIR__.'/../samples/grocery-receipt-norwegian-spar.jpg');

        $data = Extractor::fields($image,
            fields: [
                'store_name' => 'name of the grocery store',
                'total' => 'total amount as a number',
                'currency' => 'currency code',
            ],
            model: Engine::GPT_5_MINI,
            maxTokens: 1000,
        );

        expect($data)->toBeArray()
            ->and($data)->toHaveKey('store_name')
            ->and($data)->toHaveKey('total')
            ->and($data['currency'])->toBe('NOK');
    });
})->group('openai', 'gpt5', 'vision');
