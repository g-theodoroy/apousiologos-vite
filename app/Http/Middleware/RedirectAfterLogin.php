<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use Illuminate\Http\Request;

class RedirectAfterLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    /**
     * Ελέγχω σε ποιά αρχική σελίδα θα ανακατευθυνθεί ο χρήστης
     * με βάση την επιλογή του Διαχειριστή στον πίνακα settings
     */
    public function handle(Request $request, Closure $next)
    {
        // Αν έχουν επιλέξει Διαγωνίσματα $route = 'exams'; και δεν είναι μαθητές
        $route = Setting::getValueOf('landingPage');
        if ($route == 'exams' && basename(request()->headers->get('referer')) == 'login' && request()->user()->permissions['teacherOrAdmin']) {
            return redirect()->route($route);
        }
        // αλλιώς στον απουσιολόγο
        return $next($request);
    }
}
