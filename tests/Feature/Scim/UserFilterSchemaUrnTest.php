<?php

namespace Tests\Feature\Scim;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * Regression for #19347: Azure Entra ID sends filter expressions with
 * the fully-qualified schema URN prefixed onto the attribute name
 * (RFC 7644 section 3.4.2.2), e.g.
 *
 *   GET /scim/v2/Users?filter=
 *     urn:ietf:params:scim:schemas:extension:enterprise:2.0:User:employeeNumber
 *     eq "1234567"
 *
 * The upstream library's Complex::applyComparison drops the schema URN
 * on the floor and always dispatches to the FIRST schema node (core),
 * which reports "Unknown path" for any attribute defined on a non-core
 * schema. SnipeRootComplex now intercepts these and routes to the
 * correct schema node based on the URN.
 *
 * Note: filtering by relationship-backed extension attributes
 * (department, location, company) is separately blocked by the
 * library's default Attribute::applyComparison throwing "Comparison is
 * not implemented" for MappedTable subclasses. That's a distinct gap
 * from #19347 and is not covered here.
 */
class UserFilterSchemaUrnTest extends TestCase
{
    private const ENTERPRISE_URN = 'urn:ietf:params:scim:schemas:extension:enterprise:2.0:User';

    private const CORE_URN = 'urn:ietf:params:scim:schemas:core:2.0:User';

    public function test_filter_by_enterprise_employee_number_with_urn_prefix_finds_user(): void
    {
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create(['employee_num' => 'EMP-19347']);
        User::factory()->create(['employee_num' => 'other']);

        $filter = self::ENTERPRISE_URN.':employeeNumber eq "EMP-19347"';

        $response = $this->getJson('/scim/v2/Users?filter='.urlencode($filter));

        $response->assertOk();
        $response->assertJsonPath('totalResults', 1);
        $response->assertJsonPath('Resources.0.id', (string) $target->id);
    }

    public function test_filter_by_core_username_still_works_after_override(): void
    {
        // Sanity check that we haven't regressed the ordinary (no-URN,
        // core-schema-implicit) filter path.
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create(['username' => 'scim-filter-target']);
        User::factory()->create();

        $response = $this->getJson('/scim/v2/Users?filter='.urlencode('userName eq "scim-filter-target"'));

        $response->assertOk();
        $response->assertJsonPath('totalResults', 1);
        $response->assertJsonPath('Resources.0.id', (string) $target->id);
    }

    public function test_filter_by_core_username_with_explicit_core_urn_prefix_works(): void
    {
        // Some clients qualify core-schema attributes with the core URN.
        // Route via the same code path as enterprise.
        Passport::actingAs(User::factory()->superuser()->create());
        $target = User::factory()->create(['username' => 'scim-core-target']);
        User::factory()->create();

        $filter = self::CORE_URN.':userName eq "scim-core-target"';

        $response = $this->getJson('/scim/v2/Users?filter='.urlencode($filter));

        $response->assertOk();
        $response->assertJsonPath('totalResults', 1);
        $response->assertJsonPath('Resources.0.id', (string) $target->id);
    }
}
