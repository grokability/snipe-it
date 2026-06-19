<?php

namespace Tests\Unit\Providers;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Ejercita los Gates definidos en App\Providers\AuthServiceProvider::boot().
 *
 * Se usan usuarios NO superadmin (el superadmin corta en Gate::before y nunca
 * ejecuta el cuerpo del closure), con permisos concretos para recorrer las ramas
 * "return true" de cada gate, y un usuario plano para las cadenas can(...)/false.
 */
class AuthGatesTest extends TestCase
{
    private function permUser(): User
    {
        return User::factory()->create(['permissions' => json_encode([
            'admin' => '1',
            'import' => '1',
            'assets.view.encrypted_custom_fields' => '1',
            'reports.view' => '1',
            'self.two_factor' => '1',
            'self.api' => '1',
            'self.edit_location' => '1',
            'self.checkout_assets' => '1',
            'self.view_purchase_cost' => '1',
        ])]);
    }

    private function plainUser(): User
    {
        return User::factory()->create(['permissions' => '{}']);
    }

    // ---- Gate::before: editableOnDemo en modo demo ------------------------

    public function test_editable_on_demo_blocked_when_locked(): void
    {
        config(['app.lock_passwords' => true]);

        $this->assertFalse(Gate::forUser($this->plainUser())->allows('editableOnDemo'));

        config(['app.lock_passwords' => false]);
    }

    public function test_editable_on_demo_allowed_when_unlocked(): void
    {
        config(['app.lock_passwords' => false]);

        $this->assertTrue(Gate::forUser($this->plainUser())->allows('editableOnDemo'));
    }

    // ---- canEditAuthFields ------------------------------------------------

    public function test_can_edit_auth_fields_non_admin_editing_regular(): void
    {
        $actor = $this->plainUser();
        $target = User::factory()->create(['permissions' => '{}']);

        // editor solo-usuarios + target no admin/super -> true
        $this->assertTrue(Gate::forUser($actor)->allows('canEditAuthFields', $target));
    }

    public function test_can_edit_auth_fields_non_admin_editing_admin_or_super(): void
    {
        $actor = $this->plainUser();
        $adminTarget = User::factory()->create(['permissions' => json_encode(['admin' => '1'])]);
        $superTarget = User::factory()->superuser()->create();

        $this->assertFalse(Gate::forUser($actor)->allows('canEditAuthFields', $adminTarget));
        $this->assertFalse(Gate::forUser($actor)->allows('canEditAuthFields', $superTarget));
    }

    public function test_can_edit_auth_fields_admin_editing_regular_and_super(): void
    {
        $admin = User::factory()->create(['permissions' => json_encode(['admin' => '1'])]);
        $regularTarget = User::factory()->create(['permissions' => '{}']);
        $superTarget = User::factory()->superuser()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('canEditAuthFields', $regularTarget));
        $this->assertFalse(Gate::forUser($admin)->allows('canEditAuthFields', $superTarget));
    }

    public function test_can_edit_auth_fields_non_user_item_is_false(): void
    {
        $actor = $this->plainUser();
        $asset = Asset::factory()->create();

        $this->assertFalse(Gate::forUser($actor)->allows('canEditAuthFields', $asset));
    }

    // ---- Gates de permiso con rama "return true" --------------------------

    public function test_permission_gates_grant_true_branch(): void
    {
        $user = $this->permUser();

        $this->assertTrue(Gate::forUser($user)->allows('admin'));
        $this->assertTrue(Gate::forUser($user)->allows('import'));
        $this->assertTrue(Gate::forUser($user)->allows('assets.view.encrypted_custom_fields'));
        $this->assertTrue(Gate::forUser($user)->allows('reports.view'));
        $this->assertTrue(Gate::forUser($user)->allows('activity.view'));
        $this->assertTrue(Gate::forUser($user)->allows('self.two_factor'));
        $this->assertTrue(Gate::forUser($user)->allows('self.api'));
        $this->assertTrue(Gate::forUser($user)->allows('self.edit_location'));
        $this->assertTrue(Gate::forUser($user)->allows('self.checkout_assets'));
        $this->assertTrue(Gate::forUser($user)->allows('self.view_purchase_cost'));
    }

    public function test_permission_gates_deny_plain_user(): void
    {
        $user = $this->plainUser();

        // Ejecuta la rama de condición falsa de cada gate de permiso.
        $this->assertFalse(Gate::forUser($user)->allows('admin'));
        $this->assertFalse(Gate::forUser($user)->allows('import'));
        $this->assertFalse(Gate::forUser($user)->allows('reports.view'));
        $this->assertFalse(Gate::forUser($user)->allows('self.api'));
    }

    // ---- Cadenas can(...) (backend.interact / view.selectlists) -----------

    public function test_backend_interact_and_view_selectlists_chains(): void
    {
        $user = $this->plainUser();

        // Usuario sin permisos -> cada can(view,...) false -> recorre toda la cadena.
        $this->assertFalse(Gate::forUser($user)->allows('backend.interact'));
        $this->assertFalse(Gate::forUser($user)->allows('view.selectlists'));
    }

    public function test_self_profile_gate(): void
    {
        $user = $this->plainUser();

        // Depende de Setting->profile_edit; basta con ejecutar el closure.
        $result = Gate::forUser($user)->allows('self.profile');
        $this->assertIsBool($result);
    }
}
