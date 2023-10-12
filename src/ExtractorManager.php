<?php

namespace HelgeSverre\Extractor;

use HelgeSverre\Extractor\Enums\Model;
use HelgeSverre\Extractor\Extractors\Contacts;
use HelgeSverre\Extractor\Extractors\Emails;
use HelgeSverre\Extractor\Extractors\Payslip;
use HelgeSverre\Extractor\Extractors\PhoneNumbers;
use HelgeSverre\Extractor\Extractors\Receipt;
use HelgeSverre\Extractor\Extractors\Rundown;
use Illuminate\Support\Manager;

// TODO: rename Engine
class ExtractorManager extends Manager
{
    public function getDefaultDriver()
    {
        return $this->config->get('extractor.default');
    }

    public function extract(
        $extractor,
        TextContent|string $input,
        Model $model = null,
        int $maxTokens = null,
        float $temperature = null,
    ): ?array {
        $driver = self::driver($extractor);
        $engine = new Engine($driver);

        return $engine->run(
            input: $input,
            model: $model,
            maxTokens: $maxTokens,
            temperature: $temperature
        );
    }

    public function createContactsDriver()
    {
        return new Contacts(
            $this->config->get('extractor.extractors.contacts'),
        );
    }

    public function createEmailsDriver()
    {
        return new Emails;
    }

    public function createPayslipDriver()
    {
        return new Payslip;

    }

    public function createPhoneNumbersDriver()
    {
        return new PhoneNumbers;

    }

    public function createReceiptDriver()
    {
        return new Receipt;

    }

    public function createRundownDriver()
    {
        return new Rundown;

    }
}
