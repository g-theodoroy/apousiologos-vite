<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Tmima;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Anathesi;
use App\Exports\NoGradesExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NoGradesStudentsExport;


class GradesService {


    public function indexCreateData($selectedAnathesiId){

        $activeGradePeriod = Setting::getValueOf('activeGradePeriod');
        $gradeBaseAlert = intVal(Setting::getValueOf('gradeBaseAlert'));

        $selectedTmima = null;
        $selectedMathima = null;
        $gradesStudentsPeriod = null;
        $periods = config('gth.periods');
        unset($periods[key($periods)]);

        if ($selectedAnathesiId) {
            $selectedAnathesi = Anathesi::find($selectedAnathesiId);
            $selectedTmima = $selectedAnathesi->tmima;
            $selectedMathima = $selectedAnathesi->mathima;

            // βάζω σε ένα πίνακα τους ΑΜ των μαθητών που ανήκουν στο επιλεγμένο τμήμα
            $student_ids = Tmima::where('tmima', $selectedTmima)->pluck('student_id')->toArray();


            $grades = Grade::where('anathesi_id', $selectedAnathesiId)->where('period_id', $activeGradePeriod)->pluck('grade', 'student_id');
            $allGrades = Grade::where('anathesi_id', $selectedAnathesiId)->get(['grade', 'student_id', 'period_id'])->toArray();
            $gradesStudentsPeriod = array();

            // αρχικοποιώ τις τιμές σε null
            foreach ($student_ids as $student_id) {
                foreach ($periods as $periodKey => $period) {
                    $gradesStudentsPeriod[$student_id][$periodKey] = null;
                }
            }
            // εισάγω τις υπάρχουσες
            foreach ($allGrades as $gr) {
                $gradesStudentsPeriod[$gr['student_id']][$gr['period_id']] = $gr['grade'];
            }
        }


        // παίρνω τα τμηματα του χρήστη
        // ταξινόμηση με το μήκος του ονόματος + αλφαβητικά
        $anatheseis = Auth::user()->anatheseis()->where('mathima', '<>', '')->orderby('mathima')->orderByRaw('LENGTH(tmima)')->orderby('tmima')->get(['id', 'mathima', 'tmima']);

        // αν είναι Διαχειριστής τα παίρνω όλα από μια φορά
        if (Auth::user()->role_id == 1) {
            $anatheseis = Anathesi::where('mathima', '<>', '')->orderby('mathima')->orderByRaw('LENGTH(tmima)')->orderby('tmima')->get(['id', 'mathima', 'tmima']);
        }

        $students = array();

        if ($selectedTmima) {
            // παίρνω τα στοιχεία των μαθητών ταξινομημένα κσι φιλτράρω μόνο τους ΑΜ που έχει το τμήμα
            $students = Student::orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata')->with('anatheseis')->get()->only($student_ids);
        }

        $arrStudents = array();
        $gradesPeriodLessons = array();
        foreach ($students as $stuApFoD) {
            foreach ($stuApFoD->anatheseis as $anath) {
                $gradesPeriodLessons[$stuApFoD->id]['name'] = $stuApFoD->eponimo . ' ' . $stuApFoD->onoma;
                $gradesPeriodLessons[$stuApFoD->id][$anath->mathima][$anath->pivot->period_id] = $anath->pivot->grade;
            }
            $tmimata = $stuApFoD->tmimata->pluck('tmima');
            $arrStudents[] = [
                'id' => $stuApFoD->id,
                'eponimo' => $stuApFoD->eponimo,
                'onoma' => $stuApFoD->onoma,
                'patronimo' => $stuApFoD->patronimo,
                'tmima' => $tmimata[0],
                'tmimata' => $tmimata->implode(', '),
                'grade' => $grades[$stuApFoD->id] ?? null,
                'olografos' => isset($grades[$stuApFoD->id])  ? $this->olografos($grades[$stuApFoD->id]) : null
            ];
        }


        usort($arrStudents, function ($a, $b) {
            return $a['eponimo'] <=> $b['eponimo'] ?:
                $a['onoma'] <=> $b['onoma'] ?:
                strnatcasecmp($a['patronimo'], $b['patronimo']);
        });

        $mathimata = Anathesi::select('mathima')->distinct()->orderBy('mathima')->pluck('mathima')->toArray();

        $showOtherGrades = Setting::getValueOf('showOtherGrades') == 1 ?? false;

        $insertedAnatheseisCount = Grade::where('period_id', $activeGradePeriod)->distinct('anathesi_id')->count();
        $anatheseisCount = Anathesi::count();
        $infoInsertedAnatheseis = $insertedAnatheseisCount ? "$insertedAnatheseisCount από $anatheseisCount" : "";
        $infoNotInsertedAnatheseis = $anatheseisCount - $insertedAnatheseisCount; 

        return [
            'anatheseis' => $anatheseis,
            'selectedAnathesiId' => intval($selectedAnathesiId),
            'selectedTmima' => $selectedTmima,
            'selectedMathima' => $selectedMathima,
            'arrStudents' => $arrStudents,
            'gradesStudentsPeriod' => $gradesStudentsPeriod,
            'gradesPeriodLessons' => $gradesPeriodLessons,
            'mathimata' => $mathimata,
            'activeGradePeriod' => $activeGradePeriod,
            'periods' => $periods,
            'showOtherGrades' => $showOtherGrades,
            'gradeBaseAlert' => $gradeBaseAlert,
            'infoInsertedAnatheseis' => $infoInsertedAnatheseis,
            'infoNotInsertedAnatheseis' => $infoNotInsertedAnatheseis
        ];
    }


    private function olografos($n)
    {
        if ($n == 'Δ') return "Όχι άποψη";
        if (in_array($n, ['0', '00', '00,0'])) return "μηδέν";
        if ($n == '-1') return "Δεν προσήλθε";
        $num = floatval(str_replace(',', '.', $n));
        if ($num > 20) return null;
        if (!$num || $num < 0) return null;
        $whole = intval($num);
        $fraction = substr($num - $whole, -1);
        $numberNames = ['μηδέν', 'ένα', 'δύο', 'τρία', 'τέσσερα', 'πέντε', 'έξι', 'επτά', 'οκτώ', 'εννέα', 'δέκα', 'έντεκα', 'δώδεκα', 'δεκατρία', 'δεκατέσσερα', 'δεκαπέντε', 'δεκαέξι', 'δεκαεπτά', 'δεκαοκτώ', 'δεκαεννέα', 'είκοσι'];
        $name = $numberNames[$whole];
        if ($fraction > 0) $name .= ' κ ' . $numberNames[$fraction];
        return  $name;
    }

    
    public function noGrades(){

        return Excel::download(new NoGradesExport, 'Υπολοιπόμενοι βαθμοί.xls');
    }


    public function noGradesStudents()
    {

       return Excel::download(new NoGradesStudentsExport, 'Μαθητές χωρίς βαθμούς.xls');
    }

}
