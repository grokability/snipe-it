<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Tools\FindSimilarModelsTool;
use App\Mcp\Tools\GetCurrentUserTool;
use App\Mcp\Tools\GetUserAssetsTool;
use App\Mcp\Tools\ShowAssetModelTool;
use App\Mcp\Tools\ShowAssetTool;
use App\Mcp\Tools\ShowCategoryTool;
use App\Mcp\Tools\ShowCompanyTool;
use App\Mcp\Tools\ShowConsumableTool;
use App\Mcp\Tools\ShowDepreciationTool;
use App\Mcp\Tools\ShowGroupTool;
use App\Mcp\Tools\ShowLicenseTool;
use App\Mcp\Tools\ShowLocationTool;
use App\Mcp\Tools\ShowManufacturerTool;
use App\Mcp\Tools\ShowStatusLabelTool;
use App\Mcp\Tools\ShowSupplierTool;
use App\Mcp\Tools\ShowUserPermissionsTool;
use App\Mcp\Tools\ShowUserTool;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Depreciation;
use App\Models\Group;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tool;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every MCP tool sets an outputSchema() that MCP clients use to validate
 * structuredContent.
 *
 * When the handler emits a field as null BUT the schema declares it
 * non-null, clients (Claude Code, Codex, etc) reject the whole response. The
 * existing per-tool tests only assert known-populated fields, so
 * nullability drift goes unnoticed until a real client hits it.
 *
 * This test runs each tool against a minimally-populated setup
 * (every optional field left null) and validates the resulting
 * structuredContent against the tool's own outputSchema.
 *
 * Scope: READ tools (Show / Get / Find). Write-tool coverage isn't handled
 * here just yet.
 */
class McpOutputSchemaContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->superuser()->create());
    }

    /**
     * @return iterable<string, array{class-string<Tool>, callable(): array<string, mixed>}>
     */
    public static function readToolProvider(): iterable
    {
        yield 'get_current_user' => [GetCurrentUserTool::class, fn () => []];

        yield 'show_asset' => [ShowAssetTool::class, function () {
            $asset = Asset::factory()->create(['name' => null, 'serial' => null, 'notes' => null, 'purchase_cost' => null]);

            return ['id' => $asset->id];
        }];

        yield 'show_asset_model' => [ShowAssetModelTool::class, function () {
            $model = AssetModel::factory()->create(['model_number' => null, 'notes' => null, 'eol' => null, 'min_amt' => null]);

            return ['id' => $model->id];
        }];

        yield 'show_category' => [ShowCategoryTool::class, function () {
            $category = Category::factory()->create(['notes' => null]);

            return ['id' => $category->id];
        }];

        yield 'show_company' => [ShowCompanyTool::class, function () {
            $company = Company::factory()->create(['phone' => null, 'fax' => null, 'email' => null, 'notes' => null]);

            return ['id' => $company->id];
        }];

        yield 'show_consumable' => [ShowConsumableTool::class, function () {
            $consumable = Consumable::factory()->create([
                'location_id' => null,
                'manufacturer_id' => null,
                'company_id' => null,
                'model_number' => null,
                'notes' => null,
                'min_amt' => null,
                'default_purchase_cost' => null,
            ]);

            return ['id' => $consumable->id];
        }];

        yield 'show_depreciation' => [ShowDepreciationTool::class, function () {
            $depreciation = Depreciation::factory()->create();

            return ['id' => $depreciation->id];
        }];

        yield 'show_group' => [ShowGroupTool::class, function () {
            $group = Group::factory()->create(['notes' => null]);

            return ['id' => $group->id];
        }];

        yield 'show_license' => [ShowLicenseTool::class, function () {
            $license = License::factory()->create();

            return ['id' => $license->id];
        }];

        yield 'show_location' => [ShowLocationTool::class, function () {
            $location = Location::factory()->create([
                'address' => null,
                'address2' => null,
                'city' => null,
                'state' => null,
                'country' => null,
                'zip' => null,
                'phone' => null,
                'fax' => null,
                'parent_id' => null,
            ]);

            return ['id' => $location->id];
        }];

        yield 'show_manufacturer' => [ShowManufacturerTool::class, function () {
            $manufacturer = Manufacturer::factory()->create([
                'url' => null,
                'support_url' => null,
                'support_email' => null,
                'support_phone' => null,
                'warranty_lookup_url' => null,
                'notes' => null,
            ]);

            return ['id' => $manufacturer->id];
        }];

        yield 'show_supplier' => [ShowSupplierTool::class, function () {
            $supplier = Supplier::factory()->create([
                'address' => null,
                'address2' => null,
                'city' => null,
                'state' => null,
                'country' => null,
                'zip' => null,
                'phone' => null,
                'fax' => null,
                'email' => null,
                'url' => null,
                'contact' => null,
                'notes' => null,
            ]);

            return ['id' => $supplier->id];
        }];

        yield 'show_status_label' => [ShowStatusLabelTool::class, function () {
            $label = Statuslabel::factory()->create(['color' => null, 'notes' => null]);

            return ['id' => $label->id];
        }];

        yield 'show_user' => [ShowUserTool::class, function () {
            // first_name stays populated because SaveUserRequest and the
            // model's $rules both treat it as required in practice. The
            // other three (last_name / email / employee_num) are the
            // optional fields the reporter specifically saw returned as
            // null.
            $user = User::factory()->create(['last_name' => null, 'email' => null, 'employee_num' => null]);

            return ['id' => $user->id];
        }];

        yield 'show_user_permissions' => [ShowUserPermissionsTool::class, function () {
            $user = User::factory()->create();

            return ['id' => $user->id];
        }];

        yield 'find_similar_models' => [FindSimilarModelsTool::class, function () {
            $model = AssetModel::factory()->create();

            return ['model_id' => $model->id];
        }];

        yield 'get_user_assets' => [GetUserAssetsTool::class, function () {
            $user = User::factory()->create();

            return ['id' => $user->id];
        }];
    }

    #[DataProvider('readToolProvider')]
    public function test_structured_content_matches_declared_output_schema(string $toolClass, callable $makeFixture): void
    {
        $args = $makeFixture();

        /** @var Tool $tool */
        $tool = new $toolClass;
        $response = $tool->handle(new Request($args));

        $structuredContent = $response->getStructuredContent();
        $this->assertIsArray($structuredContent, "Tool {$toolClass} returned no structuredContent. The fixture may need adjustment to hit the success branch.");

        $schema = $this->compileSchemaFromTool($tool);

        $errors = [];
        $this->validateAgainstSchema($structuredContent, $schema, '', $errors);

        $this->assertEmpty(
            $errors,
            "Tool {$toolClass} returned a structuredContent that fails its declared outputSchema.\n".
            "This is the exact failure mode of issue #20068: MCP clients that validate against outputSchema reject such responses.\n\n".
            implode("\n", $errors)
        );
    }

    /**
     * Build a JSON Schema document from a tool's outputSchema() return value.
     *
     * @return array<string, mixed>
     */
    private function compileSchemaFromTool(Tool $tool): array
    {
        $factory = new JsonSchemaTypeFactory;
        $fields = $tool->outputSchema($factory);

        $properties = [];
        $required = [];
        foreach ($fields as $name => $type) {
            $properties[$name] = $type->toArray();
            $arr = $type->toArray();
            if (($arr['required'] ?? false) === true || (isset($arr['x-required']) && $arr['x-required'] === true)) {
                $required[] = $name;
            }
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ];
    }

    /**
     * Minimal subset-of-JSON-Schema validator covering what MCP tool
     * outputSchemas declare: type (scalar or [type, "null"]),
     * properties, required, array/object shapes. Writes any mismatch
     * into $errors and returns.
     *
     * @param  array<int, string>  $errors
     */
    private function validateAgainstSchema(mixed $value, array $schema, string $path, array &$errors): void
    {
        $type = $schema['type'] ?? null;

        if ($type === null) {
            return;
        }

        $allowedTypes = is_array($type) ? $type : [$type];
        $actualType = $this->jsonTypeOf($value);

        if (! $this->typeMatches($actualType, $allowedTypes, $value)) {
            $errors[] = sprintf(
                '%s: got %s, schema allows [%s]. Value: %s',
                $path === '' ? '(root)' : $path,
                $actualType,
                implode(', ', $allowedTypes),
                $this->describeValue($value),
            );

            return;
        }

        if (in_array('object', $allowedTypes, true) && is_array($value) && ! array_is_list($value)) {
            $properties = $schema['properties'] ?? [];
            $required = $schema['required'] ?? [];

            foreach ($required as $requiredKey) {
                if (! array_key_exists($requiredKey, $value)) {
                    $errors[] = sprintf(
                        '%s: missing required field "%s"',
                        $path === '' ? '(root)' : $path,
                        $requiredKey,
                    );
                }
            }

            foreach ($properties as $propName => $propSchema) {
                if (! array_key_exists($propName, $value)) {
                    continue;
                }
                $propPath = $path === '' ? $propName : $path.'.'.$propName;
                $this->validateAgainstSchema($value[$propName], $propSchema, $propPath, $errors);
            }
        }
    }

    /**
     * JSON Schema "number" accepts integer and float. An empty PHP
     * array is ambiguous: it serializes as [] in JSON regardless of
     * whether it was intended as an empty list or an empty object, so
     * allow it to match either. These are the only two shape
     * considerations the MCP schema subset exercises.
     *
     * @param  array<int, string>  $allowed
     */
    private function typeMatches(string $actual, array $allowed, mixed $value): bool
    {
        if (in_array($actual, $allowed, true)) {
            return true;
        }
        if ($actual === 'integer' && in_array('number', $allowed, true)) {
            return true;
        }
        if ($actual === 'array' && $value === [] && in_array('object', $allowed, true)) {
            return true;
        }

        return false;
    }

    private function jsonTypeOf(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_float($value)) {
            return 'number';
        }
        if (is_string($value)) {
            return 'string';
        }
        if (is_array($value)) {
            return array_is_list($value) ? 'array' : 'object';
        }

        return gettype($value);
    }

    private function describeValue(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_scalar($value)) {
            return json_encode($value);
        }

        return gettype($value);
    }
}
