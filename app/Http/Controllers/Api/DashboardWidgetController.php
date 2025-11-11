<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardWidgetController extends Controller
{
    /**
     * Get user's dashboard widget configuration
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get user's widgets or create default if none exist
        $widgets = DashboardWidget::where('user_id', $user->id)->get();
        
        if ($widgets->isEmpty()) {
            // Create default widgets for user
            $defaultWidgets = DashboardWidget::getDefaultWidgets();
            foreach ($defaultWidgets as $widget) {
                $widget['user_id'] = $user->id;
                DashboardWidget::create($widget);
            }
            $widgets = DashboardWidget::where('user_id', $user->id)->get();
        }
        
        return response()->json([
            'success' => true,
            'widgets' => $widgets,
        ]);
    }

    /**
     * Update widget configuration
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'widgets' => 'required|array',
                'widgets.*.widget_id' => 'required|string',
                'widgets.*.is_visible' => 'nullable|in:0,1,true,false',
                'widgets.*.grid_x' => 'integer|min:0',
                'widgets.*.grid_y' => 'integer|min:0',
                'widgets.*.grid_width' => 'integer|min:1|max:12',
                'widgets.*.grid_height' => 'integer|min:1',
                'widgets.*.sort_order' => 'integer|min:0',
            ]);

            foreach ($validated['widgets'] as $widgetData) {
                DashboardWidget::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'widget_id' => $widgetData['widget_id'],
                    ],
                    [
                        'is_visible' => isset($widgetData['is_visible']) ? (bool)$widgetData['is_visible'] : true,
                        'grid_x' => $widgetData['grid_x'] ?? 0,
                        'grid_y' => $widgetData['grid_y'] ?? 0,
                        'grid_width' => $widgetData['grid_width'] ?? 2,
                        'grid_height' => $widgetData['grid_height'] ?? 1,
                        'sort_order' => $widgetData['sort_order'] ?? 0,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Dashboard configuration saved successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset to default configuration
     */
    public function reset(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Delete all user's widgets
        DashboardWidget::where('user_id', $user->id)->delete();
        
        // Create default widgets
        $defaultWidgets = DashboardWidget::getDefaultWidgets();
        foreach ($defaultWidgets as $widget) {
            $widget['user_id'] = $user->id;
            DashboardWidget::create($widget);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Dashboard reset to default configuration',
        ]);
    }

    /**
     * Toggle widget visibility
     */
    public function toggleVisibility(Request $request, string $widgetId): JsonResponse
    {
        $user = Auth::user();
        
        $widget = DashboardWidget::where('user_id', $user->id)
            ->where('widget_id', $widgetId)
            ->first();
            
        if (!$widget) {
            return response()->json([
                'success' => false,
                'message' => 'Widget not found',
            ], 404);
        }
        
        $widget->is_visible = !$widget->is_visible;
        $widget->save();
        
        return response()->json([
            'success' => true,
            'is_visible' => $widget->is_visible,
        ]);
    }
}
