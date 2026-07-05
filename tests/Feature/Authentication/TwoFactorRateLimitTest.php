<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TwoFactorRateLimitTest extends TestCase
{
    #[Test]
    public function post_two_factor_is_rate_limited(): void
    {
        config(['auth.two_factor.max_attempts_per_min' => 3]);

        $user = User::factory()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_enrolled' => 1,
        ]);

        $this->actingAs($user);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/two-factor', ['two_factor_secret' => '000000'])
                ->assertRedirect();
        }

        $this->post('/two-factor', ['two_factor_secret' => '000000'])
            ->assertStatus(429);
    }
}
