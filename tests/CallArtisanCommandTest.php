<?php

namespace Tests;

use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class CallArtisanCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        @unlink(app_path(static::resolvePath('Models/Category.php')));
        @unlink(app_path(static::resolvePath('Http/Resources/CategoryResource.php')));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink(app_path(static::resolvePath('Models/Category.php')));
        @unlink(app_path(static::resolvePath('Http/Resources/CategoryResource.php')));
    }

    private function expectsCommandChoice(PendingCommand $command, $answer): PendingCommand
    {
        $options = [
            'model.stub.php',
            static::resolvePath('pack/'),
        ];

        $command->expectsChoice(
            '選擇要使用的 Stub 檔案',
            $answer,
            $options,
        );

        return $command;
    }

    private static function resolvePath(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    // =========================================================================
    // = Tests
    // =========================================================================

    public function test_call_artisan_command()
    {
        $expectedPutPath = app_path(static::resolvePath('Models/Category.php'));

        $command = $this->artisan('make:a...');
        $command = $this->expectsCommandChoice($command, 'model.stub.php');

        $command->expectsQuestion('請輸入要注入的名稱', 'Category');

        $command->expectsOutput(sprintf('已建立 "%s"', $expectedPutPath));
        $command->assertExitCode(0);
        $command->run();

        $this->assertFileExists($expectedPutPath);
        $this->assertFileEquals(
            __DIR__ . '/fixtures/category_model.php',
            $expectedPutPath,
        );
    }

    public function test_call_artisan_command_in_already_exists_and_overwrite()
    {
        $expectedPutPath = app_path(static::resolvePath('Models/Category.php'));

        file_put_contents($expectedPutPath, '__EMPTY__');

        $command = $this->artisan('make:a...');
        $command = $this->expectsCommandChoice($command, 'model.stub.php');

        $command->expectsQuestion('請輸入要注入的名稱', 'Category');
        $command->expectsConfirmation(
            sprintf('%s 檔案已存在，是否要覆蓋?', $expectedPutPath),
            'yes'
        );

        $command->expectsOutput(sprintf('已建立 "%s"', $expectedPutPath));
        $command->assertExitCode(0);
        $command->run();

        $this->assertFileExists($expectedPutPath);
        $this->assertFileEquals(
            __DIR__ . '/fixtures/category_model.php',
            $expectedPutPath,
        );
    }

    public function test_call_artisan_command_in_already_exists_and_without_overwrite()
    {
        $expectedPutPath = app_path(static::resolvePath('Models/Category.php'));

        file_put_contents($expectedPutPath, '__EMPTY__');

        $command = $this->artisan('make:a...');
        $command = $this->expectsCommandChoice($command, 'model.stub.php');

        $command->expectsQuestion('請輸入要注入的名稱', 'Category');
        $command->expectsConfirmation(
            sprintf('%s 檔案已存在，是否要覆蓋?', $expectedPutPath),
            'no'
        );

        $command->expectsOutput(
            sprintf('略過處理 %s', $expectedPutPath)
        );

        $command->assertExitCode(0);
        $command->run();

        $this->assertFileExists($expectedPutPath);
        $this->assertEquals(
            '__EMPTY__',
            file_get_contents($expectedPutPath),
        );
    }

    public function test_call_artisan_command_with_filter()
    {
        $expectedPutPath = app_path(static::resolvePath('Models/Category.php'));

        $command = $this->artisan('make:a...', [
            'filter' => 'model',
        ]);

        $command->expectsChoice(
            '選擇要使用的 Stub 檔案',
            'model.stub.php',
            ['model.stub.php'],
        );

        $command->expectsQuestion('請輸入要注入的名稱', 'Category');

        $command->expectsOutput(sprintf('已建立 "%s"', $expectedPutPath));
        $command->assertExitCode(0);
        $command->run();

        $this->assertFileExists($expectedPutPath);
        $this->assertFileEquals(
            __DIR__ . '/fixtures/category_model.php',
            $expectedPutPath,
        );
    }

    public function test_call_artisan_command_with_filter_and_not_found()
    {
        $command = $this->artisan('make:a...', [
            'filter' => 'foobar',
        ]);

        $command->expectsOutput('找不到符合的 Stub 檔案');
        $command->assertExitCode(0);
        $command->run();
    }

    public function test_call_artisan_command_with_directroy()
    {
        $expectedPutPath1 = app_path(static::resolvePath('Models/Category.php'));
        $expectedPutPath2 = app_path(static::resolvePath('Http/Resources/CategoryResource.php'));

        $command = $this->artisan('make:a...');
        $command = $this->expectsCommandChoice(
            $command,
            static::resolvePath('pack/')
        );

        $command->expectsQuestion('請輸入要注入的名稱', 'Category');

        $command->expectsOutput(sprintf('已建立 "%s"', $expectedPutPath1));
        $command->expectsOutput(sprintf('已建立 "%s"', $expectedPutPath2));
        $command->assertExitCode(0);
        $command->run();

        $this->assertFileExists($expectedPutPath1);
        $this->assertFileEquals(
            __DIR__ . '/fixtures/category_model.php',
            $expectedPutPath1,
        );

        $this->assertFileExists($expectedPutPath2);
        $this->assertFileEquals(
            __DIR__ . '/fixtures/category_resource.php',
            $expectedPutPath2,
        );
    }
}
