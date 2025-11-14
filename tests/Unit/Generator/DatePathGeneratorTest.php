<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Generator;

use PHPUnit\Framework\TestCase;
use SyliusDigitalProductPlugin\Generator\DatePathGenerator;

final class DatePathGeneratorTest extends TestCase
{
    private DatePathGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new DatePathGenerator();
    }

    public function testGenerateReturnsString(): void
    {
        $result = $this->generator->generate();

        $this->assertIsString($result);
    }

    public function testGenerateReturnsDatePathFormat(): void
    {
        $result = $this->generator->generate();

        $this->assertMatchesRegularExpression('/^\d{4}\/\d{2}\/\d{2}$/', $result);
    }

    public function testGenerateReturnsCurrentDate(): void
    {
        $expectedDate = (new \DateTimeImmutable())->format('Y/m/d');
        $result = $this->generator->generate();

        $this->assertSame($expectedDate, $result);
    }

    public function testGenerateContainsValidYear(): void
    {
        $result = $this->generator->generate();
        $parts = explode('/', $result);

        $year = (int) $parts[0];
        $currentYear = (int) (new \DateTimeImmutable())->format('Y');

        $this->assertSame($currentYear, $year);
    }

    public function testGenerateContainsValidMonth(): void
    {
        $result = $this->generator->generate();
        $parts = explode('/', $result);

        $month = (int) $parts[1];

        $this->assertGreaterThanOrEqual(1, $month);
        $this->assertLessThanOrEqual(12, $month);
    }

    public function testGenerateContainsValidDay(): void
    {
        $result = $this->generator->generate();
        $parts = explode('/', $result);

        $day = (int) $parts[2];

        $this->assertGreaterThanOrEqual(1, $day);
        $this->assertLessThanOrEqual(31, $day);
    }

    public function testGenerateReturnsSameValueWhenCalledMultipleTimesInSameSecond(): void
    {
        $result1 = $this->generator->generate();
        $result2 = $this->generator->generate();

        $this->assertSame($result1, $result2);
    }

    public function testGenerateHasCorrectNumberOfSegments(): void
    {
        $result = $this->generator->generate();
        $segments = explode('/', $result);

        $this->assertCount(3, $segments);
    }

    public function testGeneratePadsMonthAndDayWithLeadingZeros(): void
    {
        $result = $this->generator->generate();
        $parts = explode('/', $result);

        $this->assertSame(2, strlen($parts[1]));
        $this->assertSame(2, strlen($parts[2]));
    }
}
