<?php

namespace Humi\StructuredLogger\Tests\Laravel;

use Humi\StructuredLogger\Laravel\LogsDataChanges;
use PHPUnit\Framework\TestCase;

class LogsDataChangesTest extends TestCase
{
    // We are testing this trait by using it on the test class.
    use LogsDataChanges;

    private array $attributes = [
        'id' => 123,
        'name' => 'Harriet',
        'favoriteFood' => 'lasagna',
        'age' => 33,
        'weight' => 150,
        'created_at' => 'yesterday',
        'updated_at' => 'today',
        'deleted_at' => null,
    ];

    /** @test */
    public function it_returns_an_empty_when_no_attributes_to_obfuscate_are_set(): void
    {
        $this->assertCount(0, $this->getAttributeNamesToObfuscateForLogging());
    }

    /** @test */
    public function it_returns_attributes_to_obfuscate_when_set(): void
    {
        $this->attributesToObfuscateForLogging = ['age', 'weight'];

        $this->assertSame(['age', 'weight'], $this->getAttributeNamesToObfuscateForLogging());
    }

    /** @test */
    public function it_does_not_obfuscate_certain_attributes_even_when_asked_to(): void
    {
        $this->attributesToObfuscateForLogging = $this->attributesToNeverObfuscate;
        $this->attributesToObfuscateForLogging[] = 'age';

        $this->assertSame(['age'], $this->getAttributeNamesToObfuscateForLogging());
    }

    /** @test */
    public function it_returns_attributes_to_obfuscate_when_attributes_not_to_obfuscate_are_set(): void
    {
        $this->attributesNotToObfuscateForLogging = ['name'];

        $this->assertSame(['favoriteFood', 'age', 'weight'], $this->getAttributeNamesToObfuscateForLogging());
    }

    /** @test */
    public function it_returns_attributes_to_obfuscate_when_attributes_not_to_obfuscate_is_set_to_an_empty_array(): void
    {
        $this->attributesNotToObfuscateForLogging = [];

        $this->assertSame(['name', 'favoriteFood', 'age', 'weight'], $this->getAttributeNamesToObfuscateForLogging());
    }

    /** @test */
    public function when_both_attributes_to_obfuscate_and_attributes_not_to_obfuscate_are_set_it_obfuscates_both(): void
    {
        $this->attributesToObfuscateForLogging = ['age', 'weight'];
        $this->attributesNotToObfuscateForLogging = ['favoriteFood'];

        $this->assertSame(['age', 'weight', 'name'], $this->getAttributeNamesToObfuscateForLogging());
    }

    /**
     * getAttributes is used by the trait
     */
    protected function getAttributes()
    {
        return $this->attributes;
    }
}
