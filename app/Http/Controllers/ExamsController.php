<?php

namespace App\Http\Controllers;

use App\Models\User;
use Response;
use App\Models\Event;
use App\Models\Tmima;
use App\Models\Setting;
use App\Models\Anathesi;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\CalendarExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
// use Illuminate\Support\Facades\Log;


class ExamsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // έλεγχος των τιμών
        request()->validate([
            'y' => 'numeric|digits:4',
            'm' => 'numeric|min:1|max:12',
            'g' => 'in:0,1'
        ]);

        $mathimata = $this->mathimata();

        $year = request('y');
        if (!$year)  $year = Carbon::now()->year;
        $month = request('m');
        if (!$month)  $month = Carbon::now()->month;
        $date = Carbon::createFromDate($year, $month, 1);
        $gridMode = request('g');
        if ($gridMode == null) $gridMode = 1;

        $first = Carbon::createFromFormat("Y-m-d", $date->startOfMonth()->format("Y-m-d"))->startOfWeek(Carbon::MONDAY);
        $last = Carbon::createFromFormat("Y-m-d", $date->endOfMonth()->format("Y-m-d"))->endOfWeek(Carbon::FRIDAY);
        $start = $first->format("Ymd");
        $end = $last->format("Ymd");
        $diff = $last->diffInDays($first);
        $shortDateNames = ['Κυρ', 'Δευ', 'Τρι', 'Τετ', 'Πεμ', 'Παρ', 'Σαβ'];
        //$fullDateNames = ['Δευτέρα', 'Τρίτη', 'Τετάρτη', 'Πέμπτη', 'Παρασκευή', 'Σάββατο', 'Κυριακή'];
        $monthNames = ['Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος', 'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος'];
        $selectedMonth = $monthNames[$date->format('n') - 1] . ' ' . $date->format('Y');

        $dateValues = [];
        for ($i = 0; $i <= $diff; $i++) {
            if (!in_array($first->dayOfWeek, [6, 0])) {
                $dateValues[$i]['date'] = $first->format("Y-m-d");
                $dateValues[$i]['shortName'] = $shortDateNames[$first->dayOfWeek];
            }
            $first->addDay();
        }

        $data = Event::where('date', '>=', $start)->where('date',   '<=', $end)->orderBy('start')->orderBy('updated_at')->get();

        $exams = [];
        $noExams = [];
        foreach ($data as $d) {
            $date = Carbon::createFromFormat("Ymd", $d->date)->format("Y-m-d");
            $exams[$date][] = [
                'id' => $d->id,
                'user_id' => $d->user_id,
                'title' => $d->title,
                'tmima1' => $d->tmima1,
                'tmima2' => $d->tmima2,
                'mathima' => $d->mathima,
                'date' => $date,
            ];
            if ($d->tmima1 == 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ') {
                $noExams[$date] = true;
            }
        }

        $formExams = [
            'id' => '',
            'user_id' => '',
            'title' => '',
            'tmima1' => '',
            'tmima2' => '',
            'mathima' => '',
            'date' => ''
        ];

        return Inertia::render('Exams', [
            'mathimata' => $mathimata,
            'dateValues' => $dateValues,
            'month' => intval($month),
            'year' => intval($year),
            'selectedMonth' => $selectedMonth,
            'exams' => $exams,
            'noExams' => $noExams,
            'formExams' => $formExams,
            'initGridmode' => intval($gridMode)
        ]);
    }


    public function tmimata($date)
    {
        $date = Carbon::createFromFormat('Y-m-d', $date)->format('Ymd');
        $startOfWeek = Carbon::createFromFormat('Ymd', $date)->startOfWeek()->format('Ymd');
        $endOfWeek = Carbon::createFromFormat('Ymd', $date)->endOfWeek()->format('Ymd');

        $user_id = Auth::user()->id;
        $isAdmin = Auth::user()->role_id == 1;

        $maxDiagonismataForDay = Setting::getValueOf('maxDiagonismataForDay');
        $maxDiagonismataForWeek = Setting::getValueOf('maxDiagonismataForWeek');


        // βρίσκω ποια τμήματα έχουν διαγώνισμα σήμερα
        $tmimata = Event::where('date', $date)->where('tmima1', '!=', 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ')->select('tmima1', 'tmima2')->get();
        $withDiagonismaForDay = collect($tmimata->toArray())->all();
        // βρίσκω ποιοι μαθητές έχουν ήδη ένα προγραμματισμένο διαγώνισμα την ημέρα
        $studentsWithDiagonismataForDay = $this->studentsWithMaxDiagonismata($withDiagonismaForDay, $maxDiagonismataForDay);

        // βρίσκω ποια τμήματα έχουν διαγώνισμα την εβδομάδα
        $tmimata = Event::where('date', '>=',  $startOfWeek)->where('date', '<=',  $endOfWeek)->where('tmima1', '!=', 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ')->select('tmima1', 'tmima2')->get();
        $withDiagonismaForWeek = collect($tmimata->toArray())->all();
        // βρίσκω ποιοι μαθητές έχουν προγραμματισμένα συνολικά πάνω από τα επιτρεπόμενα διαγωνίσματα (3) την εβδομάδα
        $studentsWithMaxDiagonismataForWeek = $this->studentsWithMaxDiagonismata($withDiagonismaForWeek, $maxDiagonismataForWeek);

        // ποια τμήματα δεν χτυπάνε με τα προηγούμενα
        $tmimataNonConflict = $this->tmimataNotConflict($studentsWithDiagonismataForDay, $studentsWithMaxDiagonismataForWeek);

        // βρίσκω τις αναθέσεις για τον καθηγητή
        if ($isAdmin) {
            $anatheseis = $this->tmimataList();
        } else {
            $anatheseis = Anathesi::where('user_id', $user_id)->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->unique()->toArray();
       }

        // ποια τμήματα είναι ελεύθερα για τον καθηγητή
        //$tmimataNonConflictForTeacher = array_unique(array_intersect($anatheseis, $tmimataNonConflict));
        //sort($tmimataNonConflictForTeacher);
        $tmimataNonConflictForTeacher = array_intersect($anatheseis, $tmimataNonConflict);

        // Log::channel('myinfo')->info($tmimataNonConflictForTeacher);
        if ($isAdmin) {
            array_unshift($tmimataNonConflictForTeacher, 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ');
        }

        return  collect($tmimataNonConflictForTeacher)->toJson();
    }

    public function store()
    {
        // addNew
        if (!request()->id) {
            $user_id = auth()->user()->id;
            // αν δηλώνει ο Διαχειριστής διαγώνισμα
            // αυτό δηλώνεται στον καθηγητή που το έχει ανάθεση
            $isAdmin = Auth::user()->role_id == 1;
            if ($isAdmin){
                $user_id = Anathesi::where('mathima', request()->mathima)->where('tmima', request()->tmima1)->first()->user_id ?? auth()->user()->id;
            }
            $title = request()->tmima2  ?  request()->tmima1 . '-' . request()->tmima2  : request()->tmima1;
            $title .= ', ';
            $title .= request()->mathima ? request()->mathima .  ', ' : '';
            $title .= User::find($user_id)->name;
            $date = str_replace("-", "", request()->date);
            if (Carbon::createFromFormat('Ymd', $date)->isWeekend()) {
                return redirect()->back()->with(['message' => ['error' => 'Δεν επιτρέπεται διαγώνισμα το Σαββατοκύριακο.']]);
            }
            Event::create([
                'user_id' => $user_id,
                'tmima1' => request()->tmima1,
                'tmima2' => request()->tmima2,
                'mathima' => request()->mathima,
                'title' => $title,
                'date' => $date,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
            return redirect()->back()->with(['message' => ['success' =>  'Επιτυχής καταχώριση διαγωνίσματος.']]);
        }
    }

    public function delete($id)
    {
        Event::where('id', $id)->delete();
        return redirect()->back()->with(['message' => ['success' =>  'Επιτυχής διαγραφή διαγωνίσματος.']]);
    }

    public function update(Event $event, $date)
    {
        // παίρνω τα δεδομένα
        $eventDate = $date;
        $date = Carbon::createFromFormat('Y-m-d', $date)->format('Ymd');
        $dateShow = Carbon::createFromFormat('Ymd', $date)->format('d/m/Y');
        $startOfWeek = Carbon::createFromFormat('Ymd', $date)->startOfWeek()->format('Ymd');
        $endOfWeek = Carbon::createFromFormat('Ymd', $date)->endOfWeek()->format('Ymd');

        $dateShowOld = Carbon::createFromFormat('Ymd', $event->date)->format('d/m/Y');
        $startOfWeekOld = Carbon::createFromFormat('Ymd', $event->date)->startOfWeek()->format('Ymd');

        if (Carbon::createFromFormat('Ymd', $date)->isWeekend()) {
            return redirect()->back()->with(['message' => ['error' => 'Δεν επιτρέπεται διαγώνισμα το Σαββατοκύριακο.']]);
        }

        if ($event->tmima1 == 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ') {
            $updated_at = Carbon::now()->format('Y-m-d H:i:s');
            $updateArr = [
                'date' => $date,
                'updated_at' => $updated_at,
            ];
            $event->update($updateArr);

            return redirect()->back()->with(['message' => ['success' =>   'Επιτυχής ενημέρωση διαγωνίσματος.']]);
        }

        $tmima1 = $event->tmima1;
        $tmima2 = $event->tmima2;
        $user_id = $event->user_id;

        // παίρνω τους μαθητές κάθε τμήματος σε πίνακα['τμήμα'] = μαθητές
        $studentsForTmima = $this->studentsForTmima();

        $maxDiagonismataForDay = Setting::getValueOf('maxDiagonismataForDay');
        $maxDiagonismataForWeek = Setting::getValueOf('maxDiagonismataForWeek');
        // Αν η αλλαγή διαγωνίσματος είναι μέσα στην ίδια εβδομάδα οπότε μετράει και το δικό μου διαγώνισμα
        // αυξάνω τον αριθμό για να μη χτυπάει και να επιτρέψει την αλλαγή
        if ($startOfWeekOld == $startOfWeek) $maxDiagonismataForWeek++;


        // βρίσκω ποια μαθητές έχουν διαγώνισμα σήμερα
        $tmimata = Event::where('date', $date)->where('tmima1', '!=', 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ')->select('tmima1', 'tmima2')->get();
        $withDiagonismaForDay = collect($tmimata->toArray())->all();
        // βρίσκω ποιοι μαθητές έχουν ήδη ένα προγραμματισμένο διαγώνισμα την ημέρα
        $studentsWithDiagonismataForDay = $this->studentsWithMaxDiagonismata($withDiagonismaForDay, $maxDiagonismataForDay);


        // βρίσκω ποια τμήματα έχουν διαγώνισμα την εβδομάδα
        $tmimata = Event::where('date', '>=',  $startOfWeek)->where('date', '<=',  $endOfWeek)->where('tmima1', '!=', 'ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ')->select('tmima1', 'tmima2')->get();
        $withDiagonismaForWeek = collect($tmimata->toArray())->all();
        // βρίσκω ποιοι μαθητές έχουν προγραμματισμένα συνολικά πάνω από τα επιτρεπόμενα διαγωνίσματα (3) την εβδομάδα
        $studentsWithMaxDiagonismataForWeek = $this->studentsWithMaxDiagonismata($withDiagonismaForWeek, $maxDiagonismataForWeek);
        if (count(array_intersect($studentsForTmima[$tmima1], $studentsWithDiagonismataForDay)) || ($tmima2 && count(array_intersect($studentsForTmima[$tmima2], $studentsWithDiagonismataForDay)))) {
            $message = 'Δεν μπορείτε να μεταθέσετε το διαγώνισμα "' . $event->title . '" από τις ' . $dateShowOld . ' στις ' . $dateShow . ' γιατί ';
            if ($tmima2) {
                $message .= 'τουλάχιστον ένα από τα τμήματα ' . $tmima1 . '-' . $tmima2 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForDay . ' διαγωνίσματα στην ημέρα.';
            } else {
                $message .= 'τo τμήμα ' . $tmima1 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForDay . ' διαγωνίσματα στην ημέρα.';
            }
            return redirect()->back()->with(['message' => ['error' => $message]]);
        }


        if (count(array_intersect($studentsForTmima[$tmima1], $studentsWithMaxDiagonismataForWeek)) || ($tmima2 && count(array_intersect($studentsForTmima[$tmima2], $studentsWithMaxDiagonismataForWeek)))) {
            $message = 'Δεν μπορείτε να μεταθέσετε το διαγώνισμα "' . $event->title . '" από τις ' . $dateShowOld . ' στις ' . $dateShow . ' γιατί ';
            if ($tmima2) {
                $message .= 'τουλάχιστον ένα από τα τμήματα ' . $tmima1 . '-' . $tmima2 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForWeek . ' διαγωνίσματα στην εβδομάδα.';
            } else {
                $message .= 'τo τμήμα ' . $tmima1 . ' έχει ήδη συμπληρώσει τα επιτρεπόμενα ' . $maxDiagonismataForWeek . ' διαγωνίσματα στην εβδομάδα.';
            }
            return redirect()->back()->with(['message' => ['error' => $message]]);
        }

        $updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $updateArr = [
            'date' => $date,
            'updated_at' => $updated_at,
        ];
        $event->update($updateArr);

        unset($event->created_at);
        unset($event->updated_at);
        $event->date = $eventDate;

        return redirect()->back()->with(['message' => ['success' =>   'Επιτυχής ενημέρωση διαγωνίσματος.']]);
    }



    public function tmimataConflict()
    {
        // παίρνω τους μαθητές κάθε τμήματος σε πίνακα['τμήμα'] = μαθητές
        $studentsForTmima = $this->studentsForTmima();
        $arrConflicts = array();
        // συγκρίνω τους μαθητές κάθε τμήματος με όλα τα άλλα τμήματα
        foreach ($studentsForTmima as $tmima => $students) {
            foreach ($studentsForTmima as $tmi => $stu) {
                // αν υπάρχει τουλάχιστον ένας κοινός μαθητής τα τμήματα χτυπάνε
                // array_intersect => βρίσκει τις κοινές τιμές πινάκων
                if (count(array_intersect($students, $stu))) {
                    $arrConflicts[$tmima][] = $tmi;
                }
            }
        }
        return $arrConflicts;
    }

    public function exportExamsXls()
    {
        $date = Carbon::createFromDate(request()->year, request()->month, 1);

        $start = $date->startOfMonth()->format("Ymd");
        $end = $date->endOfMonth()->format("Ymd");

        $startLabel = $start ?? 'την_αρχή';
        $endLabel = $end ?? 'το_τέλος';
        return Excel::download(new CalendarExport(request()->start, request()->end), 'Διαγωνίσματα_από_' . $startLabel . '_έως_' . $endLabel . '.xls');
    }


    public function mathimata()
    {
        $isAdmin = Auth::user()->role_id == 1;

        $mathimata = Anathesi::select('mathima');
        if (!$isAdmin) {
            $mathimata = $mathimata->where('user_id', Auth::user()->id);
        }

        $mathimata = $mathimata->distinct()->orderBy('mathima')->pluck('mathima')->toArray();

        return $mathimata;
    }



    public function tmimataList()
    {
        //dd(Tmima::select('tmima')->distinct()->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->toArray());
        return Tmima::select('tmima')->distinct()->orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->toArray();
        
    }

    public function tmimataNotConflict($withDiagonismaForDay = [], $withMaxDiagonismataForWeek = [])
    {
        // λίστα τμημάτων
        $tmimata = $this->tmimataList();
        // λίστα μαθητών κάθε τμήματος
        $studentsForTmima = $this->studentsForTmima();
        // ενώνω τους μαθητές που δεν πρέπει να γράψουν άλλο διαγώνισμα (για την ημέρα, για την εβδομάδα)
        $studentsMustNotWrite = array_unique(array_merge($withDiagonismaForDay, $withMaxDiagonismataForWeek));
        $tmimataNotConflict = array();

        foreach ($tmimata as $tmima) {
            // αν δεν υπάρχουν κοινοί μαθητές (μαθητές τμήματος, μαθητές που δεν πρέπει να γράψουν)
            // το τμήμα είναι ελέυθερο
            if (!count(array_intersect($studentsForTmima[$tmima], $studentsMustNotWrite))) {
                $tmimataNotConflict[] = $tmima;
            }
        }
        return $tmimataNotConflict;
    }

    public function userExams()
    {
        $isAdmin = Auth::user()->role_id == 1;
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $chkDate = Carbon::now()->format('Ymd');

        if ($month < 8) $year--;
        $start = $year . "0901";
        $end = $year + 1 . "0630";
        $events = Event::where('date', '>=', $start)->where('date', '<=', $end)->orderBy('date');
        if (!$isAdmin) {
            $events = $events->where('user_id', Auth::user()->id);
        }
        $events = $events->get();

        $names = User::select('id', 'name')->pluck('name', 'id');
        $arrExams = [];
        $num = 1;
        foreach ($events as $ev) {
            $tmima = $ev->tmima2 ?  "$ev->tmima1 - $ev->tmima2" :   $ev->tmima1;
            $arrExams[] = [
                'aa' => $num,
                'id' => $ev->id,
                'title' => $ev->title,
                'date' => $ev->date,
                'dateShow' => Carbon::createFromFormat('Ymd', $ev->date)->format('d/m/Y'),
                'tmima' => $tmima,
                'mathima' => $ev->mathima,
                'teacher' => $names[$ev->user_id] ?? '',
                'past' => $ev->date < $chkDate,

            ];
            $num++;
        }
        return Response::json($arrExams);
    }

    public function studentsForTmima()
    {
        // παίρνω από τα τμήματα το τμήμα και το student_id
        $stuForTmima = Tmima::orderByRaw('LENGTH(tmima)')->orderBy('tmima')->get(['tmima', 'student_id'])->toArray();
        $studentsForTmima = array();
        // προσθέτω για κάθε τμήμα πίνακα με τα sudent_id των μαθητών
        foreach ($stuForTmima  as $stu) {
            $studentsForTmima[$stu['tmima']][] = $stu['student_id'];
        }
        return $studentsForTmima;
    }

    public function studentsWithMaxDiagonismata($withDiagonisma, $maxNum)
    {

        $studentsWithMaxDiagonismata = [];
        // παίρνω τους μαθητές κάθε τμήματος
        $studentsForTmima = $this->studentsForTmima();

        $totalConflicts = array();
        // για κάθε τμήμα με διαγώνισμα
        foreach ($withDiagonisma as $withDia) {
            // το 1ο τμήμα απαιτείται. Προσθέτω τους μαθητές
            $conflicts = $studentsForTmima[$withDia['tmima1']];
            // αν έχει επιλεγεί και 2ο τμήμα
            if ($withDia['tmima2']) {
                // προσθέτω τους μαθητές. Παίρνω πίνακα με μοναδικές τιμές μαθητών για κάθε ζευγάρι τμημάτων
                $conflicts = array_unique(array_merge($conflicts, $studentsForTmima[$withDia['tmima2']]));
            }
            // προστίθενται όλοι οι μαθητές όσες φορές έγραψαν διαγώνισμα
            $totalConflicts = array_merge($totalConflicts, $conflicts);
        }
        // πόσες φορές εμφανίζεται (γράφει) κάθε μαθητής
        $sixnotitaOfStudents = array_count_values($totalConflicts);
        arsort($sixnotitaOfStudents);
        foreach ($sixnotitaOfStudents as $key => $value) {
            // Αν εμφανίζεται >= με $maxNum μπαίνει στον πίνακα ως μη διαθέσιμος μαθητής
            if ($value > $maxNum - 1) {
                $studentsWithMaxDiagonismata[] = $key;
            }
        }
        return $studentsWithMaxDiagonismata;
    }
}
