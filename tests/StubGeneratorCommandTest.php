<?php

namespace Tests;

use A2Workspace\Stubs\Commands\StubGeneratorCommand;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;
use Mockery as m;
use Closure;

class StubGeneratorCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    private function refMethod($name, $scope): Closure
    {
        $ref = function () use ($name) {
            return $this->$name(...func_get_args());
        };

        return $ref->bindTo($scope, $scope);
    }

    private function expectsCommandChoice(PendingCommand $command): PendingCommand
    {
        return tap($command, function ($command) {
            $command->expectsChoice(
                '選擇要使用的 Stub 檔案',
                'model.stub.php',
                ['model.stub.php']
            );
        });
    }

    // =========================================================================
    // = Tests
    // =========================================================================

    public function test_resolveStubNamespace_method()
    {
        /** @var \Illuminate\Filesystem\Filesystem $fs */
        $fs = m::mock(Filesystem::class);
        $command = new StubGeneratorCommand($fs);

        $ref = $this->refMethod('resolveStubNamespace', $command);

        $this->assertEquals('Foo', $ref('namespace Foo;'));
        $this->assertEquals('Foo\Bar', $ref('namespace Foo\Bar;'));
        $this->assertFalse($ref('namespace Foo\Bar'));
        $this->assertFalse($ref('namespace Foo\bar;'));
    }

    public function test_resolveStubClassname_method()
    {
        /** @var \Illuminate\Filesystem\Filesystem $fs */
        $fs = m::mock(Filesystem::class);
        $command = new StubGeneratorCommand($fs);

        $ref = $this->refMethod('resolveStubClassname', $command);

        $this->assertEquals('Foobar', $ref('class Foobar'));
        $this->assertEquals('Foobar', $ref('class Foobar extends BaseFoobar'));
    }

    public function test_call_artisan_command()
    {
        $expectedPutPath = app_path('Models' . DIRECTORY_SEPARATOR . 'Category.php');

        $command = $this->artisan('make:a...');
        $command = $this->expectsCommandChoice($command);

        $command->expectsQuestion('請輸入要注入的名稱', 'Category');
        $command->expectsOutput(
            sprintf('已建立 "%s"', $expectedPutPath)
        );

        $command->assertExitCode(0);
        $command->run();

        $this->assertFileEquals(
            __DIR__ . '/fixtures/category_model.php',
            $expectedPutPath,
        );

        @unlink($expectedPutPath);
    }

    public function test_call_artisan_command_in_already_exists_and_overwrite()
    {
        $expectedPutPath = app_path('Models' . DIRECTORY_SEPARATOR . 'Category.php');

        file_put_contents($expectedPutPath, '__EMPTY__');

        $command = $this->artisan('make:a...');
        $command = $this->expectsCommandChoice($command);

        $command->expectsQuestion('請輸入要注入的名稱', 'Category');
        $command->expectsConfirmation(
            sprintf('%s 檔案已存在，是否要覆蓋?', $expectedPutPath),
            'yes'
        );

        $command->expectsOutput(
            sprintf('已建立 "%s"', $expectedPutPath)
        );

        $command->assertExitCode(0);
        $command->run();

        $this->assertFileEquals(
            __DIR__ . '/fixtures/category_model.php',
            $expectedPutPath,
        );

        @unlink($expectedPutPath);
    }

    public function test_call_artisan_command_in_already_exists_and_without_overwrite()
    {
        $expectedPutPath = app_path('Models' . DIRECTORY_SEPARATOR . 'Category.php');

        file_put_contents($expectedPutPath, '__EMPTY__');

        $command = $this->artisan('make:a...');
        $command = $this->expectsCommandChoice($command);

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

        $this->assertEquals(
            '__EMPTY__',
            file_get_contents($expectedPutPath),
        );

        @unlink($expectedPutPath);
    }
}
