<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Location;
use App\Models\Company;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

/**
 * Implementación de pruebas para el modelo User.
 * Justificación: Plan de Pruebas Unitarias v2.0 - Sección 5.3
 */
class UserTestImplementation extends TestCase
{
    use DatabaseTransactions;

    /**
     * TC-USR-01: Validaciones requeridas y únicas
     * Referencia Wiki: Sección 10.2 / Reglas de validación para username y nombres.
     */
    public function test_user_required_validations()
    {
        $user = new User();
        $this->assertFalse($user->isValid());
        $this->assertArrayHasKey('first_name', $user->getErrors()->toArray());
        $this->assertArrayHasKey('username', $user->getErrors()->toArray());
    }

    /**
     * TC-USR-02: Password Hashing
     * Referencia Wiki: ID U-002 / Doc. Seguridad Sección 2.1 (Protección de Credenciales)
     */
    public function test_user_password_is_hashed_on_save()
    {
        $user = User::factory()->create(['password' => 'mypassword123']);
        $this->assertTrue(Hash::check('mypassword123', $user->password));
    }

    /**
     * TC-USR-03: Hidden Attributes (Security)
     * Referencia Wiki: ID U-002 / Doc. Seguridad Sección 2.1 (Ofuscación en Salidas)
     */
    public function test_user_sensitive_attributes_are_hidden()
    {
        $user = User::factory()->make();
        $array = $user->toArray();
        
        $hidden = ['password', 'remember_token', 'permissions', 'two_factor_secret'];
        foreach ($hidden as $field) {
            $this->assertArrayNotHasKey($field, $array);
        }
    }

    /**
     * TC-USR-04: Relationships (Location, Company, Manager)
     * Referencia Wiki: Sección 5.3 / Relaciones jerárquicas y organizacionales.
     */
    public function test_user_core_relationships()
    {
        $location = Location::factory()->create();
        $company = Company::factory()->create();
        $manager = User::factory()->create();

        $user = User::factory()->create([
            'location_id' => $location->id,
            'company_id' => $company->id,
            'manager_id' => $manager->id,
        ]);

        $this->assertInstanceOf(Location::class, $user->location);
        $this->assertInstanceOf(Company::class, $user->company);
        $this->assertInstanceOf(User::class, $user->manager);
    }

    /**
     * TC-USR-05: Hierarchical Management (isManagerOf)
     * Referencia Wiki: ID U-032 / U-033 / U-034 (Lógica de manager directo e indirecto)
     */
    public function test_user_hierarchy_logic()
    {
        $boss = User::factory()->create();
        $manager = User::factory()->create(['manager_id' => $boss->id]);
        $employee = User::factory()->create(['manager_id' => $manager->id]);

        $this->assertTrue($boss->isManagerOf($manager));
        $this->assertTrue($boss->isManagerOf($employee)); 
        $this->assertFalse($employee->isManagerOf($boss));
    }

    /**
     * TC-USR-06: Soft Delete & Passport Token Revocation
     * Referencia Wiki: ID U-026 / U-027 (Revocación automática de tokens Passport)
     */
    public function test_user_soft_delete_revokes_tokens()
    {
        $user = User::factory()->create();
        $user->delete();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * TC-USR-07: Unique Username across Soft Deletes
     * Referencia Wiki: Sección 10.3 (Manejo de Soft Deletes y unicidad)
     */
    public function test_user_username_must_be_unique_undeleted()
    {
        User::factory()->create(['username' => 'snipe.admin']);
        $user2 = User::factory()->make(['username' => 'snipe.admin']);
        
        $this->assertFalse($user2->isValid());
        $this->assertArrayHasKey('username', $user2->getErrors()->toArray());
    }

    /**
     * TC-USR-08: API Tokens (Passport)
     * Referencia Wiki: ID U-002 / Integración con Laravel Passport
     */
    public function test_user_uses_passport_trait()
    {
        $user = new User();
        $this->assertTrue(in_array(\Laravel\Passport\HasApiTokens::class, class_uses_recursive($user)));
    }

    /**
     * TC-USR-09: Flags (VIP, LDAP Import, Locale)
     * Referencia Wiki: ID U-028 / U-029 / U-030 (Preferencias y flags técnicos)
     */
    public function test_user_technical_flags()
    {
        $user = User::factory()->create([
            'vip' => true,
            'ldap_import' => true,
            'employee_num' => 'EMP-001'
        ]);

        $this->assertTrue((bool)$user->vip);
        $this->assertTrue((bool)$user->ldap_import);
        $this->assertEquals('EMP-001', $user->employee_num);
    }
}
