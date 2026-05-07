<?php

namespace Tests\Unit\Models\Labels;

use App\Models\Labels\DefaultLabel;
use Exception;
use Tests\TestCase;

class DefaultLabelTest extends TestCase
{
    /**
     * @link https://app.shortcut.com/grokability/story/29281
     */
    public function test_handles_zero_values_for_columns_gracefully()
    {
        $this->settings->set([
            'labels_width' => 0.00000,
            'labels_display_sgutter' => 0.00000,
        ]);

        // simply ensuring constructor didn't throw exception...
        $this->assertInstanceOf(DefaultLabel::class, new DefaultLabel);
    }

    /**
     * @link https://app.shortcut.com/grokability/story/29281
     */
    public function test_handles_zero_values_for_rows_gracefully()
    {
        $this->settings->set([
            'labels_height' => 0.00000,
            'labels_display_bgutter' => 0.00000,
        ]);

        // simply ensuring constructor didn't throw exception...
        $this->assertInstanceOf(DefaultLabel::class, new DefaultLabel);
    }

    public function test_handles_column_denominator_that_resolves_to_zero_gracefully()
    {
        $this->settings->set([
            'labels_width' => 0.1,
            'labels_display_sgutter' => -0.1,
        ]);

        $label = new DefaultLabel;

        $this->assertSame(1, $label->getColumns());
    }

    public function test_handles_row_denominator_that_resolves_to_zero_gracefully()
    {
        $this->settings->set([
            'labels_height' => 0.1,
            'labels_display_bgutter' => -0.1,
        ]);

        $label = new DefaultLabel;

        $this->assertSame(1, $label->getRows());
    }
}
