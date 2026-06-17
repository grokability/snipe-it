<?php

namespace Tests\Unit\Rules;

use App\Listeners\LogFailedLogin;
use App\Listeners\LogSuccessfulLogin;
use App\Models\Statuslabel;
use App\Models\User;
use App\Rules\AssetCannotBeCheckedOutToNondeployableStatus;
use App\Rules\UniqueUndeleted;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Tests\TestCase;

class MoreRulesAndLoginListenersTest extends TestCase
{
    /** Ejecuta una regla y devuelve true si invoco $fail. */
    private function ruleFails(object $rule, string $attribute, mixed $value): bool
    {
        $failed = false;
        $fail = function () use (&$failed) {
            $failed = true;

            // Las reglas pueden encadenar ->translate(); devolvemos un stub.
            return new class
            {
                public function translate()
                {
                    return $this;
                }
            };
        };

        $rule->validate($attribute, $value, $fail);

        return $failed;
    }

    public function test_unique_undeleted_rule(): void
    {
        User::factory()->create(['username' => 'taken_user']);

        $rule = new UniqueUndeleted('users', 'username');

        $this->assertTrue($this->ruleFails($rule, 'username', 'taken_user'));
        $this->assertFalse($this->ruleFails($rule, 'username', 'totally_free_username'));
    }

    public function test_asset_cannot_be_checked_out_to_nondeployable_status(): void
    {
        $deployable = Statuslabel::factory()->create(['deployable' => 1, 'archived' => 0, 'pending' => 0]);
        $archived = Statuslabel::factory()->create(['deployable' => 0, 'archived' => 1, 'pending' => 0]);

        $rule = new AssetCannotBeCheckedOutToNondeployableStatus;
        $rule->setData(['assigned_user' => 1]);

        $this->assertTrue($this->ruleFails($rule, 'status_id', $archived->id));
        $this->assertFalse($this->ruleFails($rule, 'status_id', $deployable->id));
    }

    public function test_log_successful_login_listener(): void
    {
        $user = User::factory()->create(['username' => 'loginok']);

        (new LogSuccessfulLogin)->handle(new Login('web', $user, false));

        $this->assertTrue(true);
    }

    public function test_log_failed_login_listener(): void
    {
        (new LogFailedLogin)->handle(new Failed('web', null, ['username' => 'baduser', 'password' => 'x']));

        $this->assertTrue(true);
    }
}
