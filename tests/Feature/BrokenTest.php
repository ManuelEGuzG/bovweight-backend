<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrokenTest extends TestCase
{
    public function test_pipeline_fails_intentionally(): void
    {
        $this->assertTrue(false);
    }
}