<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\TestResponse;
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
    }
}
