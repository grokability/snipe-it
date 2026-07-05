<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\TestResponse;
use League\Csv\Reader;
use PHPUnit\Framework\Assert;
use RuntimeException;

trait CustomTestMacros
{
    protected function registerCustomMacros()
    {
        $guardAgainstNullProperty = function (Model $model, string $property) {
            if (is_null($model->{$property})) {
                throw new RuntimeException(
                    "The property ({$property}) either does not exist or is null on the model which isn't helpful for comparison."
                );
            }
        };

        TestResponse::macro(
            'assertResponseContainsInRows',
            function (iterable|Model $models, string $property = 'name') use ($guardAgainstNullProperty) {
                if ($models instanceof Model) {
                    $models = [$models];
                }

                foreach ($models as $model) {
                    $guardAgainstNullProperty($model, $property);

                    Assert::assertTrue(
                        collect($this['rows'])->pluck($property)->contains(e($model->{$property})),
                        "Response did not contain the expected value: {$model->{$property}}"
                    );
                }

                return $this;
            }
        );

        TestResponse::macro(
            'assertResponseDoesNotContainInRows',
            function (Model $model, string $property = 'name') use ($guardAgainstNullProperty) {
                $guardAgainstNullProperty($model, $property);

                Assert::assertFalse(
                    collect($this['rows'])->pluck($property)->contains(e($model->{$property})),
                    "Response contained unexpected value: {$model->{$property}}"
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertResponseContainsInResults',
            function (Model $model, string $property = 'id') use ($guardAgainstNullProperty) {
                $guardAgainstNullProperty($model, $property);

                Assert::assertTrue(
                    collect($this->json('results'))->pluck('id')->contains(e($model->{$property})),
                    "Response did not contain the expected value: {$model->{$property}}"
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertResponseDoesNotContainInResults',
            function (Model $model, string $property = 'id') use ($guardAgainstNullProperty) {
                $guardAgainstNullProperty($model, $property);

                Assert::assertFalse(
                    collect($this->json('results'))->pluck('id')->contains(e($model->{$property})),
                    "Response contained unexpected value: {$model->{$property}}"
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertStatusMessageIs',
            function (string $message) {
                Assert::assertEquals(
                    $message,
                    $this['status'],
                    "Response status message was not {$message}"
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertMessagesAre',
            function (string $message) {
                Assert::assertEquals(
                    $message,
                    $this['messages'],
                    "Response messages was not {$message}"
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertMessagesContains',
            function (array|string $keys) {
                Assert::assertArrayHasKey('messages', $this, 'Response did not contain any messages');

                if (is_string($keys)) {
                    $keys = [$keys];
                }

                foreach ($keys as $key) {
                    Assert::assertArrayHasKey(
                        $key,
                        $this['messages'],
                        "Response messages did not contain the key: {$key}"
                    );
                }

                return $this;
            }
        );

        TestResponse::macro(
            'assertPayloadContains',
            function (array|string $keys) {
                Assert::assertArrayHasKey('payload', $this, 'Response did not contain a payload');

                if (is_string($keys)) {
                    $keys = [$keys];
                }

                foreach ($keys as $key) {
                    Assert::assertArrayHasKey(
                        $key,
                        $this['payload'],
                        "Response messages did not contain the key: {$key}"
                    );
                }

                return $this;
            }
        );

        TestResponse::macro(
            'assertSeeTextInStreamedResponse',
            function (array|string $needles): self {
                if (! is_array($needles)) {
                    $needles = [$needles];
                }

                $records = collect(Reader::createFromString($this->streamedContent())->getRecords())->flatten();

                foreach ($needles as $needle) {
                    Assert::assertTrue(
                        $records->contains($needle),
                        "Response did not contain the expected value: {$needle}"
                    );
                }

                return $this;
            }
        );

        TestResponse::macro(
            'assertDontSeeTextInStreamedResponse',
            function (array|string $needles): self {
                if (! is_array($needles)) {
                    $needles = [$needles];
                }

                $records = collect(Reader::createFromString($this->streamedContent())->getRecords())->flatten();

                foreach ($needles as $needle) {
                    Assert::assertFalse(
                        $records->contains($needle),
                        "Response contained unexpected value: {$needle}"
                    );
                }

                return $this;
            }
        );

        /**
         * Assert that the streamed CSV response contains a row where all given header→value pairs match simultaneously.
         *
         * The first row of the CSV is treated as headers. Each subsequent row is combined with those headers
         * to produce an associative map, and the assertion passes if at least one row satisfies every pair.
         *
         * Unlike assertSeeTextInStreamedResponse, this verifies that the values appear together in the same
         * row under the correct column — not just anywhere in the file.
         *
         * Usage:
         *   ->assertSeePairsInStreamedResponse([
         *       'First Name' => 'Luke',
         *       'Last Name'  => 'Skywalker',
         *       'Username'   => 'lskywalker',
         *   ])
         */
        TestResponse::macro(
            'assertSeePairsInStreamedResponse',
            function (array $pair): self {
                $records = collect(Reader::createFromString($this->streamedContent())->getRecords());

                $headers = collect($records->shift());

                $combined = $records->map(fn ($record) => $headers->combine($record));

                Assert::assertTrue(
                    $combined->contains(function ($row) use ($pair) {
                        foreach ($pair as $key => $value) {
                            if (($row[$key] ?? null) !== $value) {
                                return false;
                            }
                        }

                        return true;
                    }),
                    'Response did not contain a row matching the expected pairs: '.json_encode($pair)
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertRowCountInStreamedResponse',
            function (int $expectedCount) {
                Assert::assertCount(
                    $expectedCount,
                    collect(Reader::fromString($this->streamedContent())->getRecords()),
                    "Response did not contain the expected number of rows: {$expectedCount}"
                );

                return $this;
            }
        );

        TestResponse::macro(
            'assertCsvHeader',
            function () {
                $this->assertHeader('content-type', 'text/csv; charset=utf-8');

                return $this;
            }
        );
    }
}
