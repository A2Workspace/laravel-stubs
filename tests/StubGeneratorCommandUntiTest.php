<?php

namespace Tests;

use A2Workspace\Stubs\Commands\StubGeneratorCommand;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;
use Mockery as m;
use Closure;

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
}
