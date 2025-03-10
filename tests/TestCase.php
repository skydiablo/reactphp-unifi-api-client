<?php

declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient\Tests;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 