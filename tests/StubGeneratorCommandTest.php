<?php

namespace Tests;

use Illuminate\Contracts\Filesystem\Filesystem;
use Tests\TestCase;

class StubGeneratorCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_general()
    {
        $command = $this->artisan('make:a...');

        $command->expectsChoice(
            '選擇要使用的 Stub 檔案',
            'stub_generator_command_test_sub.php',
            ['stub_generator_command_test_sub.php']
        );

        $command->expectsQuestion('請輸入要注入的名稱', 'FooBar');

        $command->expectsOutput(
            sprintf('已建立 "%s"', __DIR__)
        );
    }
}
