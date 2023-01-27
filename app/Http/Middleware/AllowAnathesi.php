<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowAnathesi
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
        // έλεγχος κατά την καταχώριση βαθμολογίας
        // αν ο καθηγητής έχει την ανάθεση
        if (Auth::user()->permissions['admin']) return $next($request);
        if (Auth::user()->permissions['student']) return abort(403, "ΔΕΝ ΕΠΙΤΡΕΠΕΤΑΙ Η ΠΡΟΣΒΑΣΗ ΣΕ ΧΡΗΣΤΕΣ ΜΕ ΡΟΛΟ ''STUDENT''.");

        $selectedAnathesiId = request()->selectedAnathesiId;
        $anatheseisCount = Auth::user()->anatheseis()->where('id', $selectedAnathesiId)->count();

        // αν το τμήμα που δόθηκε στο url δεν αντιστοιχεί στον χρήστη επιστρέφω πίσω
        if ($selectedAnathesiId && !$anatheseisCount) return abort(403, "ΑΝΑΝΤΙΣΤΟΙΧΙΑ ΧΡΗΣΤΗ - ΤΜΗΜΑΤΟΣ.");

        return $next($request);
    }
}
