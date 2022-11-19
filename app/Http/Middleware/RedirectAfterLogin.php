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
        $route = Setting::getValueOf('landingPage');
        $activeGradePeriod = Setting::getValueOf('activeGradePeriod');
        $allowExams = Setting::getValueOf('allowExams');

        // μετά από login
        if (basename(request()->headers->get('referer')) == 'login') {

            // αν είναι admin ελεύθερα
            if(request()->user()->permissions['admin']){
                return redirect()->route($route);
            }

            // αν είναι δάσκαλος
            if (request()->user()->permissions['teacher']) {
                // αν επιτρέπεται η καταχώριση διαγωνισμάτων
                if ($route == 'exams' &&  $allowExams == 1 ) {
                    return redirect()->route($route);
                }
                // αν υπάρχει ενεργή περίοδος
                if ($route == 'grades' && $activeGradePeriod <> 0 ) {
                    return redirect()->route($route);
                }

            }
   
        }

        // αλλιώς στον απουσιολόγο
        return $next($request);
    }
}
