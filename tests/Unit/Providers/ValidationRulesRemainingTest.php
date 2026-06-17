<?php

namespace Tests\Unit\Providers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Cubre las reglas custom de ValidationServiceProvider que dependen de BD/datos:
 * unique_undeleted, two_column_unique_undeleted (+ replacer), disallow_same_pwd_as_user_fields,
 * cant_manage_self, is_unique_across_company_and_location, fmcs_location y la rama circular
 * de non_circular.
 */
class ValidationRulesRemainingTest extends TestCase
{
    public function test_unique_undeleted_passes_for_new_value(): void
    {
        $location = Location::factory()->create(['name' => 'Bodega Norte']);

        // id distinto (0 = creacion) y nombre distinto -> pasa.
        $this->assertTrue(
            Validator::make(['name' => 'Bodega Sur'], ['name' => 'unique_undeleted:locations,0'])->passes()
        );

        // mismo nombre que un registro vivo -> falla.
        $this->assertFalse(
            Validator::make(['name' => $location->name], ['name' => 'unique_undeleted:locations,0'])->passes()
        );
    }

    public function test_unique_undeleted_ignores_same_id(): void
    {
        $location = Location::factory()->create(['name' => 'Bodega Central']);

        // editando el mismo registro (id coincide) -> el propio registro se excluye, pasa.
        $this->assertTrue(
            Validator::make(['name' => $location->name], ['name' => "unique_undeleted:locations,{$location->id}"])->passes()
        );
    }

    public function test_unique_undeleted_serial_shim(): void
    {
        // assets + serial + unique_serial != 1 -> retorna true sin consultar BD.
        $this->settings->set(['unique_serial' => 0]);

        $this->assertTrue(
            Validator::make(['serial' => 'ABC123'], ['serial' => 'unique_undeleted:assets,0'])->passes()
        );
    }

    public function test_two_column_unique_undeleted(): void
    {
        $category = Category::factory()->create(['name' => 'Laptops', 'category_type' => 'asset']);

        // mismo name + mismo category_type -> falla (duplicado).
        $this->assertFalse(
            Validator::make(
                ['name' => $category->name],
                ['name' => "two_column_unique_undeleted:categories,0,category_type,{$category->category_type}"]
            )->passes()
        );

        // mismo name pero distinto category_type -> el filtro extra no aplica el match -> pasa.
        $this->assertTrue(
            Validator::make(
                ['name' => $category->name],
                ['name' => 'two_column_unique_undeleted:categories,0,category_type,accessory']
            )->passes()
        );
    }

    public function test_two_column_unique_undeleted_replacer_message(): void
    {
        $category = Category::factory()->create(['name' => 'Servidores', 'category_type' => 'asset']);

        $validator = Validator::make(
            ['name' => $category->name],
            ['name' => "two_column_unique_undeleted:categories,0,category_type,{$category->category_type}"]
        );

        $this->assertTrue($validator->fails());
        // El replacer sustituye :table1/:table2 y cambia guiones bajos por espacios.
        $message = $validator->errors()->first('name');
        $this->assertIsString($message);
        $this->assertStringNotContainsString('_', $message);
    }

    public function test_disallow_same_pwd_as_user_fields(): void
    {
        $rules = ['password' => 'disallow_same_pwd_as_user_fields'];

        // password igual al username -> falla.
        $this->assertFalse(
            Validator::make(['username' => 'jdoe', 'password' => 'jdoe'], $rules)->passes()
        );
        // password igual al email -> falla.
        $this->assertFalse(
            Validator::make(['email' => 'a@b.com', 'password' => 'a@b.com'], $rules)->passes()
        );
        // password igual al first_name -> falla.
        $this->assertFalse(
            Validator::make(['first_name' => 'Ana', 'password' => 'Ana'], $rules)->passes()
        );
        // password igual al last_name -> falla.
        $this->assertFalse(
            Validator::make(['last_name' => 'Lopez', 'password' => 'Lopez'], $rules)->passes()
        );
        // password distinto de todos -> pasa.
        $this->assertTrue(
            Validator::make(
                ['username' => 'jdoe', 'email' => 'a@b.com', 'first_name' => 'Ana', 'last_name' => 'Lopez', 'password' => 'S3cret!'],
                $rules
            )->passes()
        );
    }

    public function test_cant_manage_self(): void
    {
        $rules = ['manager_id' => 'cant_manage_self'];

        // usuario existente que se asigna a si mismo como manager -> falla.
        $this->assertFalse(
            Validator::make(['id' => 5, 'manager_id' => 5], $rules)->passes()
        );
        // usuario existente con otro manager -> pasa.
        $this->assertTrue(
            Validator::make(['id' => 5, 'manager_id' => 9], $rules)->passes()
        );
        // sin 'id' (usuario nuevo) -> pasa automaticamente.
        $this->assertTrue(
            Validator::make(['manager_id' => 5], $rules)->passes()
        );
    }

    public function test_is_unique_across_company_and_location(): void
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create();
        $dept = Department::factory()->create([
            'name' => 'Soporte',
            'company_id' => $company->id,
            'location_id' => $location->id,
        ]);

        $rules = ['name' => 'is_unique_across_company_and_location:departments'];

        // mismo name + misma company + misma location -> duplicado -> falla.
        $this->assertFalse(
            Validator::make(
                ['name' => $dept->name, 'company_id' => $company->id, 'location_id' => $location->id],
                $rules
            )->passes()
        );

        // mismo name pero otra location -> pasa.
        $this->assertTrue(
            Validator::make(
                ['name' => $dept->name, 'company_id' => $company->id, 'location_id' => Location::factory()->create()->id],
                $rules
            )->passes()
        );
    }

    public function test_fmcs_location_blocks_mismatched_company(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $this->settings->set(['scope_locations_fmcs' => 1]);

        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $companyB->id]);

        // la location pertenece a otra company -> falla.
        $this->assertFalse(
            Validator::make(
                ['location_id' => $location->id, 'company_id' => $companyA->id],
                ['location_id' => 'fmcs_location']
            )->passes()
        );

        // misma company -> pasa.
        $this->assertTrue(
            Validator::make(
                ['location_id' => $location->id, 'company_id' => $companyB->id],
                ['location_id' => 'fmcs_location']
            )->passes()
        );
    }

    public function test_fmcs_location_passes_when_disabled(): void
    {
        // FMCS desactivado (default) -> la regla siempre pasa sin tocar la location.
        $this->assertTrue(
            Validator::make(
                ['location_id' => 99999, 'company_id' => 1],
                ['location_id' => 'fmcs_location']
            )->passes()
        );
    }

    public function test_non_circular_detects_self_reference(): void
    {
        $location = Location::factory()->create();

        // El registro se referencia a si mismo como parent -> circular -> falla.
        $this->assertFalse(
            Validator::make(
                ['id' => $location->id, 'parent_id' => $location->id],
                ['parent_id' => 'non_circular:locations,id']
            )->passes()
        );
    }
}
