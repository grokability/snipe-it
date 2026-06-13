<?php

namespace Tests\Feature\Components\Api;

use App\Models\Component;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class ComponentBusinessRulesTest extends TestCase
{
    public function test_num_checked_out_returns_cached_sum_unconstrained_assets()
    {
        // Arrange
        $component = new Component();

        $component->sum_unconstrained_assets = 5;

        // Act
        $result = $component->numCheckedOut();

        // Assert
        $this->assertSame(5, $result);
    }

    public function test_num_checked_out_returns_zero_when_no_assignments_exist()
    {
        // Arrange
        $component = new Component();

        $component->sum_unconstrained_assets = 0;

        // Act
        $result = $component->numCheckedOut();

        // Assert
        $this->assertSame(0, $result);
    }

    public function test_unconstrained_assets_returns_belongs_to_many_relationship()
    {
        // Arrange
        $component = new Component();

        // Act
        $relationship = $component->unconstrainedAssets();

        // Assert
        $this->assertInstanceOf(
            BelongsToMany::class,
            $relationship
        );
    }

    public function test_company_returns_belongs_to_relationship()
    {
        // Arrange
        $component = new Component();

        // Act
        $relationship = $component->company();

        // Assert
        $this->assertInstanceOf(
            BelongsTo::class,
            $relationship
        );
    }

    public function test_purchase_cost_validation_accepts_zero()
    {
        // Arrange
        $component = new Component();

        // Act
        $rules = $component->rules;

        // Assert
        $this->assertStringContainsString(
            'gte:0',
            $rules['purchase_cost']
        );
    }

    public function test_purchase_cost_validation_rejects_negative_values()
    {
        // Arrange
        $component = new Component();

        // Act
        $rules = $component->rules;

        // Assert
        $this->assertStringContainsString(
            'gte:0',
            $rules['purchase_cost']
        );
    }
}