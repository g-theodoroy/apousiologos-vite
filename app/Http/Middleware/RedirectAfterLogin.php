<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Services\CheckUpdates;

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
        $landingPage = Setting::getValueOf('landingPage');
        $activeGradePeriod = Setting::getValueOf('activeGradePeriod');
        $allowExams = Setting::getValueOf('allowExams');

        // μετά από login
        if (basename(request()->headers->get('referer')) == 'login') {


            // αν είναι Διαχειριστής
            if(request()->user()->permissions['admin']){

                // ελέγχω για ενημερώσεις
                if (config('gth.check_updates')){ 
                    (new CheckUpdates)->chkForUpdates();
                }

                // αν υπάρχουν ενημερώσεις γίνεται redirect με μήνυμα checkUpdates
                // εφόσον υπάρχει μήνυμα checkUpdates ο διαχειριστής ανακατευθύνεται μόνο στον απουσιολόγο
                // αλλιώς όπως έχει οριστεί η πρώτη σελίδα
                $message = $request->session()->get('message')['checkUpdates'] ?? null;

                if(! $message){
                    if ($landingPage == 'exams') {
                        return redirect()->route($landingPage);
                    }
                    // αν υπάρχει ενεργή περίοδος
                    if ($landingPage == 'grades') {
                        return redirect()->route($landingPage);
                    }
                }
            }

            // αν είναι δάσκαλος
            if (request()->user()->permissions['teacher']) {
                // αν επιτρέπεται η καταχώριση διαγωνισμάτων
                if ($landingPage == 'exams' &&  $allowExams == 1 ) {
                    return redirect()->route($landingPage);
                }
                // αν υπάρχει ενεργή βαθμολογική περίοδος
                if ($landingPage == 'grades' && $activeGradePeriod <> 0 ) {
                    return redirect()->route($landingPage);
                }

            }
   
        }

        // αλλιώς στον απουσιολόγο
        return $next($request);
    }
}
