<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Event;
use App\Services\ExamsService;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;


class ExamsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(ExamsService $examsService)
    {

        // έλεγχος των τιμών
        request()->validate([
            'y' => 'numeric|digits:4',
            'm' => 'numeric|min:1|max:12',
            'g' => 'in:0,1'
        ]);

        return Inertia::render('Exams', $examsService->indexCreateData());
    }


    public function store(ExamsService $examsService)
    {

        if (Carbon::createFromFormat('Y-m-d', request()->date)->isWeekend()) {
            return redirect()->back()->with(['message' => ['error' => 'Δεν επιτρέπεται διαγώνισμα το Σαββατοκύριακο.']]);
        }

        Event::create($examsService->storeCreateData());

        return redirect()->back()->with(['message' => ['success' =>  'Επιτυχής καταχώριση διαγωνίσματος.']]);
    }


    public function update(Event $event, $date, ExamsService $examsService)
    {
        // όχι διαγωνίσματα το Σαββατοκύριακο. Ενεργοποιείται όταν γίνεται με την modal φόρμα αλλαγή ημερομηνίας
        if (Carbon::createFromFormat('Y-m-d', $date)->isWeekend()) {
            return redirect()->back()->with(['message' => ['error' => 'Δεν επιτρέπεται διαγώνισμα το Σαββατοκύριακο.']]);
        }

        // οταν ο διαχειριστής αλλάζει το "ΟΧΙ ΔΙΑΓΩΝΙΣΜΑΤΑ" δεν χρειάζεται έλεγχος
        if ($event->tmima1 == 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ') {
            $event->update([
                'date' => Carbon::createFromFormat('Y-m-d', $date)->format('Ymd'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            return redirect()->back()->with(['message' => ['success' =>   'Επιτυχής ενημέρωση ημέρας απαγόρευσης διαγωνισμάτων.']]);
        }

        // γίνεται έλεγχος αν μπορεί να μπει διαγώνισμα
        // αν επιστραφεί $message υπάρχει λάθος - αν επιστραφεί null είναι ok
        $message = $examsService->checkIfUpdateOk($event, $date);

        if ($message) {
            return redirect()->back()->with(['message' => ['error' => $message]]);
        }

        $event->update([
            'date' => Carbon::createFromFormat('Y-m-d', $date)->format('Ymd'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with(['message' => ['success' =>   'Επιτυχής ενημέρωση διαγωνίσματος.']]);
    }


    public function destroy($id)
    {
        Event::where('id', $id)->delete();
        return redirect()->back()->with(['message' => ['success' =>  'Επιτυχής διαγραφή διαγωνίσματος.']]);
    }
}
