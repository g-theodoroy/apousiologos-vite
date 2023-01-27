<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use App\Models\Setting;
use Illuminate\Http\Request;

class DateBackAllowed
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
        // αν η ρύθμιση το επιτρέπει
        // έλεγχος ότι ο μη διαχειριστής δεν μπορεί να πάει
        // σε μελλοντική ημερομηνία ή παρελθούσα πριν την μέγιστη επιτρεπόμενη

        if (!$request->date) return $next($request);
        if (!preg_match("/^\d{4}\-\d{2}-\d{2}$/", $request->date)) return abort( 403, "ΛΑΝΘΑΣΜΕΝΗ ΜΟΡΦΗ ΗΜΕΡΟΜΗΝΙΑΣ");
        if (auth()->user()->role_id == 1) return $next($request);

        $date =   Carbon::createFromFormat("Y-m-d", $request->date)->format("Ymd");;
        $today = Carbon::now()->format("Ymd");
        $selectedTmima = $request->selectedTmima ?? '0';

        $daysBack = Setting::getValueOf('pastDaysInsertApousies');
        $setCustomDate = Setting::getValueOf('setCustomDate');

        // αν έχει οριστεί ημνια setcustomdate δεν επιτρέπεται άλλη
        if ($setCustomDate) {
            $customDate = Carbon::createFromFormat("Y-m-d", $setCustomDate)->format("Ymd");
            if ($date !== $customDate) return abort(403, "ΜΗ ΕΠΙΤΡΕΠΤΗ ΗΜΕΡΟΜΗΝΙΑ");
        } else {
            // αν δεν επιτρέπεται πλοήγηση σε προηγούμενες ημέρες
            // επιτρέπεται μόνο σήμερα
            if (!$daysBack && $date !== $today) return abort(403, "ΜΗ ΕΠΙΤΡΕΠΤΗ ΗΜΕΡΟΜΗΝΙΑ");
        }

        // αν έχουν οριστεί πλοήγηση σε προγενέστερες ημνιες
        if ($daysBack) {
            // βρίσκω την τελευταία επιτρεπόμενη
            $lastPreviousDay = Carbon::now()->subDays($daysBack)->format("Ymd");
            // δεν επιτρέπεται εισαγωγή σε μελλοντική ημνια 
            if ($date > $today) {
                $thisDate = Carbon::createFromFormat("Ymd", $today)->format("Y-m-d");
                $message = 'Δεν επιτρέπεται η επιλογή της μελλοντικής ημερομηνίας ' . Carbon::createFromFormat("Ymd", $date)->format("d/m/Y") . '.';
                return redirect("/apousiologos/$selectedTmima/$thisDate")->with('message', [
                    'dateOutOfRange' => $message
                ]);
            }
            // δεν αφήνω να πάει σε προηγούμενη
            if ($date < $lastPreviousDay) {
                $lastDate = Carbon::createFromFormat("Ymd", $lastPreviousDay)->format("Y-m-d");
                $message = 'Δεν επιτρέπεται η επιλογή ημερομηνίας πρίν από τις ' . Carbon::createFromFormat("Ymd", $lastPreviousDay)->format("d/m/Y") . '.';
                return redirect("/apousiologos/$selectedTmima/$lastDate")->with('message', [
                    'dateOutOfRange' => $message
                ]);
            }
        }
        return $next($request);
    }
}
