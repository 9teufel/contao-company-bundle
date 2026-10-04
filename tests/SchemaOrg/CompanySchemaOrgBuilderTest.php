<?php

declare(strict_types=1);

namespace Oveleon\ContaoCompanyBundle\Tests\SchemaOrg;

use Oveleon\ContaoCompanyBundle\SchemaOrg\CompanySchemaOrgBuilder;
use PHPUnit\Framework\TestCase;

class CompanySchemaOrgBuilderTest extends TestCase
{
    private CompanySchemaOrgBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new CompanySchemaOrgBuilder();
    }

    public function testBuildsOrganizationFromCompanyData(): void
    {
        $schema = $this->builder->build([
            'name' => 'Example GmbH',
            'street' => 'Example Street 1',
            'postal' => '1010',
            'city' => 'Vienna',
            'state' => 'Vienna',
            'country' => 'at',
            'phone' => '+43 1 234567',
            'phone2' => '+43 1 765432',
            'fax' => '+43 1 111111',
            'email' => 'hello@example.com',
            'email2' => 'office@example.com',
            'socialmedia' => [
                ['type' => 'linkedin', 'url' => 'https://www.linkedin.com/company/example'],
                ['type' => 'instagram', 'url' => 'https://www.instagram.com/example'],
            ],
        ], 'https://example.com', 'https://example.com/files/logo.svg');

        self::assertSame([
            '@type' => 'Organization',
            '@id' => 'https://example.com/#/schema/organization',
            'name' => 'Example GmbH',
            'url' => 'https://example.com/',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Example Street 1',
                'postalCode' => '1010',
                'addressLocality' => 'Vienna',
                'addressRegion' => 'Vienna',
                'addressCountry' => 'AT',
            ],
            'telephone' => ['+43 1 234567', '+43 1 765432'],
            'faxNumber' => '+43 1 111111',
            'email' => ['hello@example.com', 'office@example.com'],
            'logo' => 'https://example.com/files/logo.svg',
            'sameAs' => [
                'https://www.linkedin.com/company/example',
                'https://www.instagram.com/example',
            ],
        ], $schema);
    }

    public function testReturnsNullWithoutCompanyName(): void
    {
        self::assertNull($this->builder->build([
            'city' => 'Vienna',
        ], 'https://example.com'));
    }

    public function testSkipsInvalidAndDuplicateOptionalValues(): void
    {
        $schema = $this->builder->build([
            'name' => ' Example GmbH ',
            'phone' => '+43 1 234567',
            'phone2' => '+43 1 234567',
            'email' => 'invalid',
            'socialmedia' => [
                ['type' => 'custom', 'url' => 'not-a-url'],
                ['url' => 'https://example.com/ignored'],
                ['type' => 'custom', 'url' => 'https://example.com/social'],
                ['type' => 'custom', 'url' => 'https://example.com/social'],
            ],
        ], 'https://example.com/');

        self::assertSame([
            '@type' => 'Organization',
            '@id' => 'https://example.com/#/schema/organization',
            'name' => 'Example GmbH',
            'url' => 'https://example.com/',
            'telephone' => '+43 1 234567',
            'sameAs' => ['https://example.com/social'],
        ], $schema);
    }
}
