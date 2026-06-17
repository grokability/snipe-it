<?php

namespace Tests\Unit\Models;

use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StatuslabelLogicTest extends TestCase
{
    public static function typeProvider(): array
    {
        return [
            'pending'      => [['pending' => 1, 'archived' => 0, 'deployable' => 0], 'pending'],
            'archived'     => [['pending' => 0, 'archived' => 1, 'deployable' => 0], 'archived'],
            'undeployable' => [['pending' => 0, 'archived' => 0, 'deployable' => 0], 'undeployable'],
            'deployable'   => [['pending' => 0, 'archived' => 0, 'deployable' => 1], 'deployable'],
        ];
    }

    #[DataProvider('typeProvider')]
    public function test_get_statuslabel_type(array $flags, string $expected): void
    {
        $status = Statuslabel::factory()->create($flags);

        $this->assertEquals($expected, $status->getStatuslabelType());
    }

    public static function dbTypeProvider(): array
    {
        return [
            ['pending', 'pending'],
            ['deployable', 'deployable'],
            ['archived', 'archived'],
        ];
    }

    #[DataProvider('dbTypeProvider')]
    public function test_get_statuslabel_types_for_db(string $type, string $expectedKey): void
    {
        $result = Statuslabel::getStatuslabelTypesForDB($type);

        $this->assertEquals(1, $result[$expectedKey]);
    }

    public function test_scopes_filter_by_type(): void
    {
        Statuslabel::factory()->create(['pending' => 1, 'archived' => 0, 'deployable' => 0]);
        Statuslabel::factory()->create(['pending' => 0, 'archived' => 1, 'deployable' => 0]);
        Statuslabel::factory()->create(['pending' => 0, 'archived' => 0, 'deployable' => 1]);
        Statuslabel::factory()->create(['pending' => 0, 'archived' => 0, 'deployable' => 0]);

        $this->assertGreaterThanOrEqual(1, Statuslabel::pending()->count());
        $this->assertGreaterThanOrEqual(1, Statuslabel::archived()->count());
        $this->assertGreaterThanOrEqual(1, Statuslabel::deployable()->count());
        $this->assertGreaterThanOrEqual(1, Statuslabel::undeployable()->count());
    }

    public function test_is_deletable(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $status = Statuslabel::factory()->create();

        $this->assertIsBool($status->isDeletable());
    }
}
