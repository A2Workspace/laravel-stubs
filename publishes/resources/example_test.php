<?php

namespace Tests\Feature;

use App\Models\Dummy;
use Database\Factories\DummyFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DummyApiTest extends TestCase
{
    /**
     * Base path of API.
     *
     * @var string
     */
    protected string $base = '/api/dummies';

    /**
     * @param  int|null  $count
     * @return \Database\Factories\DummyFactory
     */
    protected function factory($count = null): DummyFactory
    {
        return Dummy::factory($count);
    }

    // =========================================================================
    // = BREAD Specs
    // =========================================================================

    /**
     * @return void
     */
    public function test_browse_records()
    {
        $this->factory(3)->create();

        $response = $this->getJson($this->base);

        $response->assertOk();

        $response->assertJsonStructure([
            'meta',
            'links',
            'data' => [
                '*' => [
                    'id',

                    // ... Here is where you should put the expected schema of response records.
                ]
            ]
        ]);
    }

    /**
     * @return void
     */
    public function test_read_specified_record()
    {
        $record = $this->factory()->create();

        $response = $this->getJson("{$this->base}/{$record->getKey()}");

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',

                // ... Here is where you should put the expected schema of response record.
            ]
        ]);
    }

    /**
     * @return void
     */
    public function test_edit_specified_record()
    {
        $record = $this->factory()->create();

        $response = $this->putJson(
            "{$this->base}/{$record->getKey()}",
            $data = [

                // ... Here is where you should put the form data for updating.

            ]
        );

        $response->assertNoContent();

        $this->assertDatabaseHas('dummies', $data);
    }

    /**
     * @return void
     */
    public function test_add_new_record()
    {
        $response = $this->postJson(
            "{$this->base}",
            $data = [

                // ... Here is where you should put the form data for creating.

            ]
        );

        $response->assertCreated();

        $this->assertDatabaseHas('dummies', [
            ...$data,

            // ... extra checks
        ]);
    }

    /**
     * @return void
     */
    public function test_delete_specified_record()
    {
        $record = $this->factory()->create();

        $response = $this->deleteJson(
            "{$this->base}/{$record->getKey()}"
        );

        $response->assertNoContent();

        $this->assertDatabaseCount('dummies', 0);
    }

    // =========================================================================
    // = Extra Specs
    // =========================================================================

    // ...
}
