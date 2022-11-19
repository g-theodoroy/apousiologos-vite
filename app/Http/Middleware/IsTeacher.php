<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Providers\RouteServiceProvider;

class IsTeacher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        // αν ο χρήστης είναι μαθητής
        if (request()->user()->permissions['student']) return abort(403);

        // αν ο χρήστης είναι καθηγητής
        if (request()->user()->permissions['teacher']){

            // βρίσκω τη διεύθυνση
            $route = request()->getPathInfo();

            // διαγωνίσματα
            if ($route == '/exams') {

                // επιτρέπονται τα διαγωνίσματα;
                $allowExams = Setting::getValueOf('allowExams') == 1 ?? false;

                // αν οχι βγάζω λάθος
                if (!$allowExams) return abort(403);

            }

            // Βαθμολογία
            if ($route == '/grades') {

                // υπάρχει ενεργή Βαθμ. περίοδος;
                $activeGradePeriod = Setting::getValueOf('activeGradePeriod') <> 0 ?? false;

                // αν οχι βγάζω λάθος
                if (!$activeGradePeriod) return abort(403);

            }

        }

        return $next($request);

    }

}
