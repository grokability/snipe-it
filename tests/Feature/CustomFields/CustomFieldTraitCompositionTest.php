<?php

namespace Tests\Feature\CustomFields;

use App\Models\Asset;
use App\Models\Contracts\HasCustomFields;
use App\Models\CustomFieldset;
use App\Models\Traits\HasAndValidatesCustomFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Watson\Validating\ValidatingTrait;

class CustomFieldTraitCompositionTest extends TestCase
{
    public function test_the_traits_own_watson_inclusion_and_inherited_custom_fields_are_allowed(): void
    {
        $asset = new Asset;
        $inherited = new CustomFieldCompositionInheritedAsset;

        $this->assertTrue($asset->getValidating());
        $this->assertTrue($inherited->getValidating());
        $this->assertInstanceOf(HasCustomFields::class, $asset);
        $this->assertInstanceOf(HasCustomFields::class, $inherited);
    }

    public function test_using_the_trait_without_the_interface_is_rejected_at_boot(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(CustomFieldCompositionMissingInterface::class.' must implement '.HasCustomFields::class);

        new CustomFieldCompositionMissingInterface;
    }

    /** @return array<string, array{class-string<Model>}> */
    public static function conflictingModels(): array
    {
        return [
            'direct inclusion' => [CustomFieldCompositionDirectModel::class],
            'another trait' => [CustomFieldCompositionWrappedModel::class],
            'parent class' => [CustomFieldCompositionChildModel::class],
        ];
    }

    /** @param class-string<Model> $modelClass */
    #[DataProvider('conflictingModels')]
    public function test_separate_watson_validation_is_rejected_with_a_clear_message(string $modelClass): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($modelClass.' must use '.HasAndValidatesCustomFields::class.' without separately using '.ValidatingTrait::class);

        new $modelClass;
    }
}

class CustomFieldCompositionInheritedAsset extends Asset {}

abstract class CustomFieldCompositionModel extends Model
{
    public function custom_fieldset(): Relation
    {
        return $this->belongsTo(CustomFieldset::class, 'fieldset_id');
    }
}

/**
 * Resolve PHP's method conflicts so model boot can exercise the additional guard.
 * Without these adaptations, PHP rejects the declaration before Laravel boots.
 */
class CustomFieldCompositionDirectModel extends CustomFieldCompositionModel implements HasCustomFields
{
    use HasAndValidatesCustomFields, ValidatingTrait {
        HasAndValidatesCustomFields::getRules insteadof ValidatingTrait;
        HasAndValidatesCustomFields::getValidationAttributeNames insteadof ValidatingTrait;
    }
}

trait SeparateWatsonValidation
{
    use ValidatingTrait;
}

class CustomFieldCompositionWrappedModel extends CustomFieldCompositionModel implements HasCustomFields
{
    use HasAndValidatesCustomFields, SeparateWatsonValidation {
        HasAndValidatesCustomFields::getRules insteadof SeparateWatsonValidation;
        HasAndValidatesCustomFields::getValidationAttributeNames insteadof SeparateWatsonValidation;
    }
}

class CustomFieldCompositionWatsonParent extends CustomFieldCompositionModel
{
    use ValidatingTrait;
}

class CustomFieldCompositionChildModel extends CustomFieldCompositionWatsonParent implements HasCustomFields
{
    use HasAndValidatesCustomFields;
}

class CustomFieldCompositionMissingInterface extends CustomFieldCompositionModel
{
    use HasAndValidatesCustomFields;
}
