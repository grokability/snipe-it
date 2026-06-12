<?php

namespace Tests\Unit;

use App\Models\Depreciable;
use App\Models\Depreciation;
use App\Models\Setting;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Pruebas unitarias del modelo base App\Models\Depreciable.
 *
 * Integrante 2 (DevOps / CI-CD) — Sprint 2.
 *
 * Estrategia:
 *  - Patron AAA (Arrange-Act-Assert), un comportamiento por test.
 *  - Logica pura: se trabaja con instancias en memoria (new + setRelation),
 *    SIN escribir ni consultar la base de datos. La relacion `depreciation`
 *    se inyecta con setRelation() para no disparar queries.
 *  - Determinismo temporal: Carbon::setTestNow() para los metodos basados en now(),
 *    y el seam getDateTime() (sobrescrito en FakeDepreciable) para el modelo de medio anio.
 *  - Los unicos tests que tocan BD son los de getDepreciatedValue() que enruta segun
 *    Setting::getSettings()->depreciation_method (el metodo realmente requiere Setting).
 *
 * Nota: la cobertura lineal "amount"/"percent" ya existe en tests/Unit/DepreciationTest.php;
 * aqui se cubren los bordes y ramas no cubiertas (full/half period, half-year, nulos, clamps).
 */
class DepreciableTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow(); // limpia el reloj fijo
        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // Helpers de construccion (sin BD)
    // ------------------------------------------------------------------

    /**
     * Construye una depreciacion en memoria. Se fijan los atributos de forma
     * directa porque Depreciation::$fillable solo expone ['name','months'].
     */
    private function makeDepreciation(int $months, $min = 0, string $type = 'amount'): Depreciation
    {
        $depreciation = new Depreciation();
        $depreciation->months = $months;
        $depreciation->depreciation_min = $min;
        $depreciation->depreciation_type = $type;

        return $depreciation;
    }

    /**
     * Construye una entidad depreciable en memoria con la relacion ya cargada.
     */
    private function makeDepreciable(float $cost, ?Carbon $purchaseDate, ?Depreciation $depreciation): FakeDepreciable
    {
        $model = new FakeDepreciable();
        $model->purchase_cost = $cost;
        $model->purchase_date = $purchaseDate;
        $model->setRelation('depreciation', $depreciation);

        return $model;
    }

    // ------------------------------------------------------------------
    // getLinearDepreciatedValue()
    // ------------------------------------------------------------------

    public function test_get_linear_depreciated_value_returns_full_cost_when_no_time_passed()
    {
        // Arrange
        $now = Carbon::parse('2020-01-01');
        Carbon::setTestNow($now);
        $model = $this->makeDepreciable(4000, $now->copy(), $this->makeDepreciation(36, 0, 'amount'));

        // Act
        $value = $model->getLinearDepreciatedValue();

        // Assert
        $this->assertEquals(4000, $value);
    }

    public function test_get_linear_depreciated_value_at_half_period()
    {
        // Arrange — 18 de 36 meses transcurridos, sin piso
        $purchase = Carbon::parse('2020-01-01');
        Carbon::setTestNow($purchase->copy()->addMonths(18));
        $model = $this->makeDepreciable(4000, $purchase, $this->makeDepreciation(36, 0, 'amount'));

        // Act
        $value = $model->getLinearDepreciatedValue();

        // Assert
        $this->assertEquals(2000, $value);
    }

    public function test_get_linear_depreciated_value_at_full_period_returns_amount_floor()
    {
        // Arrange — periodo cumplido, piso por monto
        $purchase = Carbon::parse('2020-01-01');
        Carbon::setTestNow($purchase->copy()->addYears(5));
        $model = $this->makeDepreciable(4000, $purchase, $this->makeDepreciation(36, 1000, 'amount'));

        // Act / Assert
        $this->assertEquals(1000, $model->getLinearDepreciatedValue());
    }

    public function test_get_linear_depreciated_value_at_full_period_returns_percent_floor()
    {
        // Arrange — periodo cumplido, piso por porcentaje (50% de 4000)
        $purchase = Carbon::parse('2020-01-01');
        Carbon::setTestNow($purchase->copy()->addYears(5));
        $model = $this->makeDepreciable(4000, $purchase, $this->makeDepreciation(36, 50, 'percent'));

        // Act / Assert
        $this->assertEquals(2000, $model->getLinearDepreciatedValue());
    }

    public function test_get_linear_depreciated_value_at_full_period_without_floor_returns_zero()
    {
        // Arrange — periodo cumplido y sin piso => 0
        $purchase = Carbon::parse('2020-01-01');
        Carbon::setTestNow($purchase->copy()->addYears(5));
        $model = $this->makeDepreciable(4000, $purchase, $this->makeDepreciation(36, 0, 'amount'));

        // Act / Assert
        $this->assertEquals(0, $model->getLinearDepreciatedValue());
    }

    public function test_get_linear_depreciated_value_returns_null_without_depreciation()
    {
        // Arrange — sin depreciacion asociada
        $model = $this->makeDepreciable(4000, Carbon::parse('2020-01-01'), null);

        // Act / Assert
        $this->assertNull($model->getLinearDepreciatedValue());
    }

    public function test_get_linear_depreciated_value_returns_null_without_purchase_date()
    {
        // Arrange — sin fecha de compra
        $model = $this->makeDepreciable(4000, null, $this->makeDepreciation(36, 0, 'amount'));

        // Act / Assert
        $this->assertNull($model->getLinearDepreciatedValue());
    }

    // ------------------------------------------------------------------
    // getMonthlyDepreciation()
    // ------------------------------------------------------------------

    public function test_get_monthly_depreciation_amount()
    {
        // Arrange — (4000 - 1000) / 36
        $model = $this->makeDepreciable(4000, Carbon::parse('2020-01-01'), $this->makeDepreciation(36, 1000, 'amount'));

        // Act / Assert
        $this->assertEqualsWithDelta(83.33, $model->getMonthlyDepreciation(), 0.01);
    }

    // ------------------------------------------------------------------
    // getDepreciatedValue()  (atajos puros + enrutamiento por Setting)
    // ------------------------------------------------------------------

    public function test_get_depreciated_value_returns_cost_when_no_depreciation()
    {
        // Arrange — get_depreciation() nulo => devuelve purchase_cost (sin tocar Setting/BD)
        $model = $this->makeDepreciable(4000, Carbon::parse('2020-01-01'), null);

        // Act / Assert
        $this->assertEquals(4000, $model->getDepreciatedValue());
    }

    public function test_get_depreciated_value_returns_cost_when_months_is_zero()
    {
        // Arrange — months <= 0 => devuelve purchase_cost (sin tocar Setting/BD)
        $model = $this->makeDepreciable(4000, Carbon::parse('2020-01-01'), $this->makeDepreciation(0, 0, 'amount'));

        // Act / Assert
        $this->assertEquals(4000, $model->getDepreciatedValue());
    }

    public function test_get_depreciated_value_uses_linear_method_by_default()
    {
        // Arrange — cualquier metodo distinto de half_1/half_2 cae en la rama lineal
        $settings = Setting::getSettings();
        $settings->depreciation_method = 'default';
        $settings->save();

        $purchase = Carbon::parse('2020-01-01');
        Carbon::setTestNow($purchase->copy()->addMonths(18));
        $model = $this->makeDepreciable(4000, $purchase, $this->makeDepreciation(36, 0, 'amount'));

        // Act / Assert
        $this->assertEquals($model->getLinearDepreciatedValue(), $model->getDepreciatedValue());
    }

    public function test_get_depreciated_value_routes_to_half_year_first_half()
    {
        // Arrange — Setting depreciation_method = half_1 => getHalfYearDepreciatedValue(true)
        $settings = Setting::getSettings();
        $settings->depreciation_method = 'half_1';
        $settings->save();

        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), $this->makeDepreciation(24, 0, 'amount'));
        $model->fakeNow = new \DateTime('2022-01-01');

        // Act / Assert
        $this->assertEquals($model->getHalfYearDepreciatedValue(true), $model->getDepreciatedValue());
    }

    public function test_get_depreciated_value_routes_to_half_year_second_half()
    {
        // Arrange — Setting depreciation_method = half_2 => getHalfYearDepreciatedValue(false)
        $settings = Setting::getSettings();
        $settings->depreciation_method = 'half_2';
        $settings->save();

        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), $this->makeDepreciation(24, 0, 'amount'));
        $model->fakeNow = new \DateTime('2022-01-01');

        // Act / Assert
        $this->assertEquals($model->getHalfYearDepreciatedValue(false), $model->getDepreciatedValue());
    }

    // ------------------------------------------------------------------
    // getHalfYearDepreciatedValue()
    // ------------------------------------------------------------------

    public function test_get_half_year_value_only_first_half()
    {
        // Arrange — cost 1000, 24 meses (2 anios fiscales), compra 2020 / "ahora" 2022
        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), $this->makeDepreciation(24, 0, 'amount'));
        $model->fakeNow = new \DateTime('2022-01-01');

        // Act / Assert — yearsPast 2 - 0.5 = 1.5 => 1000 - 750 = 250
        $this->assertEquals(250, $model->getHalfYearDepreciatedValue(true));
    }

    public function test_get_half_year_value_second_half()
    {
        // Arrange
        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), $this->makeDepreciation(24, 0, 'amount'));
        $model->fakeNow = new \DateTime('2022-01-01');

        // Act / Assert — yearsPast 2 => 1000 - 1000 = 0
        $this->assertEquals(0, $model->getHalfYearDepreciatedValue(false));
    }

    public function test_get_half_year_value_clamps_future_purchase_to_full_cost()
    {
        // Arrange — compra en el futuro => yearsPast < 0 se fija a 0
        $model = $this->makeDepreciable(1000, Carbon::parse('2030-01-01'), $this->makeDepreciation(24, 0, 'amount'));
        $model->fakeNow = new \DateTime('2022-01-01');

        // Act / Assert
        $this->assertEquals(1000, $model->getHalfYearDepreciatedValue(false));
    }

    public function test_get_half_year_value_clamps_to_full_depreciation()
    {
        // Arrange — "ahora" muy posterior => yearsPast se fija a deprecationYears
        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), $this->makeDepreciation(24, 0, 'amount'));
        $model->fakeNow = new \DateTime('2030-01-01');

        // Act / Assert
        $this->assertEquals(0, $model->getHalfYearDepreciatedValue(false));
    }

    // ------------------------------------------------------------------
    // get_fiscal_year() / is_first_half_of_year()  (via FakeDepreciable)
    // ------------------------------------------------------------------

    public function test_get_fiscal_year_returns_same_year_on_december_31()
    {
        $model = new FakeDepreciable();
        $this->assertEquals(2020, $model->exposedFiscalYear(new \DateTime('2020-12-31')));
    }

    public function test_get_fiscal_year_returns_previous_year_otherwise()
    {
        $model = new FakeDepreciable();
        $this->assertEquals(2019, $model->exposedFiscalYear(new \DateTime('2020-06-15')));
    }

    public function test_is_first_half_of_year_true_before_june()
    {
        $model = new FakeDepreciable();
        $this->assertTrue($model->exposedIsFirstHalfOfYear(new \DateTime('2020-03-10')));
    }

    public function test_is_first_half_of_year_false_after_june()
    {
        $model = new FakeDepreciable();
        $this->assertFalse($model->exposedIsFirstHalfOfYear(new \DateTime('2020-08-10')));
    }

    // ------------------------------------------------------------------
    // depreciated_date()
    // ------------------------------------------------------------------

    public function test_depreciated_date_adds_months_to_purchase_date()
    {
        // Arrange — compra 2020-01-01 + 24 meses
        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), $this->makeDepreciation(24, 0, 'amount'));

        // Act
        $date = $model->depreciated_date();

        // Assert
        $this->assertEquals('2022-01-01', $date->format('Y-m-d'));
    }

    public function test_depreciated_date_returns_null_without_purchase_date()
    {
        // Arrange
        $model = $this->makeDepreciable(1000, null, $this->makeDepreciation(24, 0, 'amount'));

        // Act / Assert
        $this->assertNull($model->depreciated_date());
    }

    // ------------------------------------------------------------------
    // time_until_depreciated()
    // ------------------------------------------------------------------

    public function test_time_until_depreciated_returns_interval_when_in_future()
    {
        // Arrange — fecha de depreciacion lejana en el futuro
        $model = $this->makeDepreciable(1000, Carbon::parse('2099-01-01'), $this->makeDepreciation(12, 0, 'amount'));

        // Act
        $interval = $model->time_until_depreciated();

        // Assert
        $this->assertInstanceOf(\DateInterval::class, $interval);
        $this->assertSame(0, $interval->invert);
    }

    public function test_time_until_depreciated_returns_zero_interval_when_past()
    {
        // Arrange — ya depreciado => intervalo nulo (PT0S)
        $model = $this->makeDepreciable(1000, Carbon::parse('2000-01-01'), $this->makeDepreciation(12, 0, 'amount'));

        // Act
        $interval = $model->time_until_depreciated();

        // Assert
        $this->assertInstanceOf(\DateInterval::class, $interval);
        $this->assertSame(0, $interval->y + $interval->m + $interval->d + $interval->h + $interval->i + $interval->s);
    }

    public function test_time_until_depreciated_returns_false_without_depreciation()
    {
        // Arrange — sin depreciacion => depreciated_date() null => false
        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), null);

        // Act / Assert
        $this->assertFalse($model->time_until_depreciated());
    }

    // ------------------------------------------------------------------
    // depreciationProgressPercent() / calculateProgressPercent()
    // ------------------------------------------------------------------

    public function test_depreciation_progress_percent_returns_zero_without_purchase_date()
    {
        // Arrange
        $model = $this->makeDepreciable(1000, null, $this->makeDepreciation(24, 0, 'amount'));

        // Act / Assert
        $this->assertEquals(0.0, $model->depreciationProgressPercent());
    }

    public function test_depreciation_progress_percent_midway()
    {
        // Arrange — periodo 2020-01-01 .. 2022-01-01, "ahora" 2021-01-01 => ~50%
        Carbon::setTestNow(Carbon::parse('2021-01-01'));
        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), $this->makeDepreciation(24, 0, 'amount'));

        // Act / Assert
        $this->assertEqualsWithDelta(50.0, $model->depreciationProgressPercent(), 2.0);
    }

    public function test_depreciation_progress_percent_clamped_to_100()
    {
        // Arrange — "ahora" muy posterior al fin de la depreciacion => 100
        Carbon::setTestNow(Carbon::parse('2030-01-01'));
        $model = $this->makeDepreciable(1000, Carbon::parse('2020-01-01'), $this->makeDepreciation(24, 0, 'amount'));

        // Act / Assert
        $this->assertEquals(100.0, $model->depreciationProgressPercent());
    }

    public function test_calculate_progress_percent_returns_zero_when_total_months_not_positive()
    {
        // Arrange — start == end => totalMonths 0 => 0.0
        $model = new FakeDepreciable();
        $instant = Carbon::parse('2020-01-01');

        // Act / Assert
        $this->assertEquals(0.0, $model->exposedCalculateProgressPercent($instant, $instant->copy()));
    }
}

/**
 * Doble de prueba concreto de la clase abstracta de comportamiento Depreciable.
 *
 * - Sobrescribe getDateTime() (seam dejado expresamente por el autor original
 *   "it's necessary for unit tests") para fijar la fecha "actual" del modelo de medio anio.
 * - Expone los metodos protegidos para poder probarlos de forma aislada.
 *
 * No es una clase de test (no extiende TestCase) — PHPUnit la ignora.
 */
class FakeDepreciable extends Depreciable
{
    /** Fecha fija usada por getDateTime() cuando no se pasa argumento. */
    public ?\DateTime $fakeNow = null;

    protected function getDateTime($time = null)
    {
        if ($time === null && $this->fakeNow !== null) {
            return clone $this->fakeNow;
        }

        return new \DateTime($time ?? 'now');
    }

    public function exposedFiscalYear(\DateTime $date): int
    {
        return $this->get_fiscal_year($date);
    }

    public function exposedIsFirstHalfOfYear(\DateTime $date): bool
    {
        return $this->is_first_half_of_year($date);
    }

    public function exposedCalculateProgressPercent(Carbon $start, Carbon $end): float
    {
        return $this->calculateProgressPercent($start, $end);
    }
}
