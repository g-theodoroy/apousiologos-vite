<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Inertia\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        if (!Schema::hasTable('settings')) return [];
        $settings = Setting::getValues();
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
            ],
            'schoolName' => $settings['schoolName'] ?? null,
            'activeGradesPeriod' => $settings['activeGradePeriod'] !== '0' ?? false,
            'allowExams' => isset($settings['allowExams']) ? $settings['allowExams'] == '1' : false
        ]);
    }
}
