<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    protected $fillable = [
        'user_id',
        'widget_id',
        'is_visible',
        'grid_x',
        'grid_y',
        'grid_width',
        'grid_height',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'grid_x' => 'integer',
        'grid_y' => 'integer',
        'grid_width' => 'integer',
        'grid_height' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the user that owns the widget configuration
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get default widgets configuration
     */
    public static function getDefaultWidgets()
    {
        return [
            // Asset widgets row
            ['widget_id' => 'assets', 'is_visible' => true, 'grid_x' => 0, 'grid_y' => 0, 'grid_width' => 2, 'grid_height' => 1, 'sort_order' => 1],
            ['widget_id' => 'licenses', 'is_visible' => true, 'grid_x' => 2, 'grid_y' => 0, 'grid_width' => 2, 'grid_height' => 1, 'sort_order' => 2],
            ['widget_id' => 'accessories', 'is_visible' => true, 'grid_x' => 4, 'grid_y' => 0, 'grid_width' => 2, 'grid_height' => 1, 'sort_order' => 3],
            ['widget_id' => 'consumables', 'is_visible' => true, 'grid_x' => 6, 'grid_y' => 0, 'grid_width' => 2, 'grid_height' => 1, 'sort_order' => 4],
            ['widget_id' => 'components', 'is_visible' => true, 'grid_x' => 8, 'grid_y' => 0, 'grid_width' => 2, 'grid_height' => 1, 'sort_order' => 5],
            ['widget_id' => 'users', 'is_visible' => true, 'grid_x' => 10, 'grid_y' => 0, 'grid_width' => 2, 'grid_height' => 1, 'sort_order' => 6],
            
            // Maintenance widgets (requires kits.view permission) - MERGED
            ['widget_id' => 'workorders_summary', 'is_visible' => true, 'grid_x' => 0, 'grid_y' => 1, 'grid_width' => 12, 'grid_height' => 2, 'sort_order' => 7],
            ['widget_id' => 'maintenance_todo', 'is_visible' => true, 'grid_x' => 0, 'grid_y' => 3, 'grid_width' => 12, 'grid_height' => 2, 'sort_order' => 8],
            
            // Activity and reports
            ['widget_id' => 'recent_activity', 'is_visible' => true, 'grid_x' => 0, 'grid_y' => 5, 'grid_width' => 8, 'grid_height' => 2, 'sort_order' => 9],
            ['widget_id' => 'asset_chart', 'is_visible' => true, 'grid_x' => 8, 'grid_y' => 5, 'grid_width' => 4, 'grid_height' => 2, 'sort_order' => 10],
            
            // Companies/Locations and Categories
            ['widget_id' => 'companies', 'is_visible' => true, 'grid_x' => 0, 'grid_y' => 7, 'grid_width' => 6, 'grid_height' => 2, 'sort_order' => 11],
            ['widget_id' => 'locations', 'is_visible' => true, 'grid_x' => 0, 'grid_y' => 7, 'grid_width' => 6, 'grid_height' => 2, 'sort_order' => 12],
            ['widget_id' => 'categories', 'is_visible' => true, 'grid_x' => 6, 'grid_y' => 7, 'grid_width' => 6, 'grid_height' => 2, 'sort_order' => 13],
        ];
    }
}
