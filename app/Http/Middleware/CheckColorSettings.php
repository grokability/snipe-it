<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;

class CheckColorSettings
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($settings = Setting::getSettings()) {
            $nav_color = $settings->nav_link_color;
            $link_dark_color = $settings->link_dark_color;
            $link_light_color = $settings->link_light_color;
        }


        // Override system settings
        if ($request->user()) {

            if ($request->user()->nav_color) {
                $nav_color = $request->user()->nav_color;
            }
            if ($request->user()->link_dark_color) {
                $link_dark_color = $request->user()->link_dark_color;
            }
            if ($request->user()->nav_color) {
                $link_light_color = $request->user()->link_light_color;
            }
        }


        view()->share('nav_link_color', $nav_color);
        view()->share('link_dark_color', $link_dark_color);
        view()->share('link_light_color', $link_light_color);

        return $next($request);

    }
}
