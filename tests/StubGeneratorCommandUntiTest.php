<?php

namespace Tests;

use A2Workspace\Stubs\Commands\StubGeneratorCommand;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;
use Mockery as m;
use Closure;
use Mockery;

class StubGeneratorCommandUntiTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    private function makeCommand(): StubGeneratorCommand
    {
        /** @var \Illuminate\Filesystem\Filesystem $fs */
        $fs = m::mock(Filesystem::class);

        $command = new StubGeneratorCommand($fs);
        $command->setLaravel($this->app);

        return $command;
    }

    private function refMethod($name, $scope): Closure
    {
        $ref = function () use ($name) {
            return $this->$name(...func_get_args());
        };

        return $ref->bindTo($scope, $scope);
    }

    // =========================================================================
    // = Tests
    // =========================================================================

    public function test_resolveStubNamespace_method()
    {
        $command = $this->makeCommand();

        $ref = $this->refMethod('resolveStubNamespace', $command);

        $this->assertEquals('Foo', $ref('namespace Foo;'));
        $this->assertEquals('Foo\Bar', $ref('namespace Foo\Bar;'));
        $this->assertFalse($ref('namespace Foo\Bar'));
        $this->assertFalse($ref('namespace Foo\bar;'));
    }

    public function test_resolveStubClassname_method()
    {
        $command = $this->makeCommand();

        $ref = $this->refMethod('resolveStubClassname', $command);

        $this->assertEquals('Foobar', $ref('class Foobar'));
        $this->assertEquals('Foobar', $ref('class Foobar extends BaseFoobar'));
        $this->assertEquals('Foobar', $ref('abstract class Foobar extends BaseFoobar'));
        $this->assertEquals('Foobar', $ref('class Foobar implements BaseFoobar'));
        $this->assertFalse($ref('foobar'));
    }

    public function test_replaceNamespace_method()
    {
        $command = $this->makeCommand();

        $ref = $this->refMethod('replaceNamespace', $command);

        $this->assertEquals(
            'namespace App\Models\Admin;',
            $ref('namespace App\Models;', 'Admin')
        );

        $this->assertEquals(
            'namespace App\Models\Admin\Dummy;',
            $ref('namespace App\Models\Dummy;', 'Admin')
        );
    }

    // =========================================================================
    // = Test getDestinationPath()
    // =========================================================================

    public function test_getDestinationPath_method()
    {
        $command = $this->makeCommand();

        $ref = $this->refMethod('getDestinationPath', $command);

        $this->assertEquals(
            base_path(static::resolvePath('app/Dummy.php')),
            $ref("
namespace App;
class Dummy {}
            ")
        );

        $this->assertEquals(
            base_path(static::resolvePath('app/Models/Dummy.php')),
            $ref("
namespace App\Models;
class Dummy {}
            ")
        );

        $this->assertEquals(
            base_path(static::resolvePath('tests/Dummy.php')),
            $ref("
namespace Tests;
class Dummy {}
            ")
        );

        $this->assertEquals(
            base_path(static::resolvePath('tests/Unit/Dummy.php')),
            $ref("
namespace Tests\Unit;
class Dummy {}
            ")
        );

        $this->assertEquals(
            database_path(static::resolvePath('factories/DummySeeder.php')),
            $ref("
namespace Database\Factories;
class DummySeeder {}
            ")
        );

        $this->assertEquals(
            database_path(static::resolvePath('seeders/DummySeeder.php')),
            $ref("
namespace Database\Seeders;
class DummySeeder {}
            ")
        );

        $this->assertFalse($ref(''));
        $this->assertFalse($ref("
namespace Unspported;
class DummySeeder {}
            ")
        );
    }

    // =========================================================================
    // = Test putFile()
    // =========================================================================

    public function test_putFile_method()
    {
        /** @var \Illuminate\Filesystem\Filesystem $fs */
        $fs = m::mock(Filesystem::class, function (Mockery\MockInterface $mock) {
            $mock->shouldReceive('isDirectory')
                ->once()
                ->with('dir')
                ->andReturn(false);

            $mock->shouldReceive('makeDirectory')
                ->once()
                ->with('dir', 0777, true, true);

            $mock->shouldReceive('put')
                ->once()
                ->with('dir/sub', 'content')
                ->andReturn(true);
        });

        $command = new StubGeneratorCommand($fs);

        $ref = $this->refMethod('putFile', $command);
        $ref('dir/sub', 'content');
    }
}
