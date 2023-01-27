<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use Illuminate\Http\Request;

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
        if (request()->user()->permissions['student']) return abort(403, "ΔΕΝ ΕΠΙΤΡΕΠΕΤΑΙ Η ΠΡΟΣΒΑΣΗ ΣΕ ΧΡΗΣΤΕΣ ΜΕ ΡΟΛΟ ''STUDENT''.");

        // αν ο χρήστης είναι καθηγητής
        if (request()->user()->permissions['teacher']){

            // βρίσκω τη διεύθυνση
            $route = request()->getPathInfo();

            // διαγωνίσματα
            if ($route == '/exams') {

                // επιτρέπονται τα διαγωνίσματα;
                $allowExams = Setting::getValueOf('allowExams') == 1 ?? false;

                // αν οχι βγάζω λάθος
                if (!$allowExams) return abort(403, "ΔΕΝ ΕΠΙΤΡΕΠΕΤΑΙ Η ΚΑΤΑΧΩΡΙΣΗ ΔΙΑΓΩΝΙΣΜΑΤΩΝ.");

            }

            // Βαθμολογία
            if ($route == '/grades') {

                // υπάρχει ενεργή Βαθμ. περίοδος;
                $activeGradePeriod = Setting::getValueOf('activeGradePeriod') <> 0 ?? false;

                // αν οχι βγάζω λάθος
                if (!$activeGradePeriod) return abort(403, "ΔΕΝ ΕΠΙΤΡΕΠΕΤΑΙ Η ΚΑΤΑΧΩΡΙΣΗ ΒΑΘΜΟΛΟΓΙΑΣ.");

            }

        }

        return $next($request);

    }

}
