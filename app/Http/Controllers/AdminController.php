<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Tmima;
use App\Models\Apousie;
use App\Models\Program;
use App\Models\Student;
use App\Models\Anathesi;
use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Setting;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use App\Exports\ProgramExport;
use App\Imports\ProgramImport;
use App\Exports\MathitesExport;
use App\Imports\StudentsImport;
use App\Exports\KathigitesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApousiesForDayExport;
use App\Exports\ApousiesMyschoolExport;
use App\Imports\ApousiesMyschoolImport;

class AdminController extends Controller
{
    //
    /*
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('web');
        $this->middleware('admin');
    }
*/
    public function index()
    {
        $keys = [
            'allowRegister',
            'hoursUnlocked',
            'letTeachersUnlockHours',
            'allowTeachersSaveAtNotActiveHour',
            'showFutureHours',
            'allowWeekends',
            'showOtherGrades'
        ];

        $settings = Setting::getValues();
        foreach ($keys as $key)  $settings[$key] = $settings[$key] == 1 ?? false;

        // ddd($settings);

        return Inertia::render('Settings', [
            'periods' => config('gth.periods'),
            'initialSettings' => $settings,
        ]);
    }

    public function setConfigs()
    {
        //ddd(request());
        $val = request('allowRegister') ? 1 : null;
        Setting::setValueOf('allowRegister', $val);
        $val = request('hoursUnlocked') ? 1 : null;
        Setting::setValueOf('hoursUnlocked', $val);
        $val = request('allowTeachersSaveAtNotActiveHour') ? 1 : null;
        Setting::setValueOf('allowTeachersSaveAtNotActiveHour', $val);
        $val = request('letTeachersUnlockHours') ? 1 : null;
        Setting::setValueOf('letTeachersUnlockHours', $val);
        $val = request('showFutureHours') ? 1 : null;
        Setting::setValueOf('showFutureHours', $val);
        $val = request('allowWeekends') ? 1 : null;
        Setting::setValueOf('allowWeekends', $val);
        $val = request('showOtherGrades') ? 1 : null;
        Setting::setValueOf('showOtherGrades', $val);
        Setting::setValueOf('schoolName', request('schoolName'));
        Setting::setValueOf('setCustomDate', request('setCustomDate'));
        Setting::setValueOf('timeZone', request('timeZone'));
        Setting::setValueOf('maxDiagonismataForDay', request('maxDiagonismataForDay'));
        Setting::setValueOf('maxDiagonismataForWeek', request('maxDiagonismataForWeek'));
        Setting::setValueOf('activeGradePeriod', request('activeGradePeriod'));
        Setting::setValueOf('pastDaysInsertApousies', request('pastDaysInsertApousies'));
        Setting::setValueOf('gradeBaseAlert', request('gradeBaseAlert'));
        Setting::setValueOf('landingPage', request('landingPage'));
        return redirect()->route('settings')->with(['message' => ['success' => 'Επιτυχής ενημέρωση.']]);
    }


    public function importXls()
    {
        $numKath = User::getNumOfKathigites() - 1;
        $numMath = Student::getNumOfStudents();
        $numProg = Program::getNumOfHours();
        $gradePeriod = Setting::getValueOf('activeGradePeriod');
        if ($gradePeriod > 0) {
            $activeGradePeriod = config('gth.periods')[Setting::getValueOf('activeGradePeriod')];
        } else {
            $activeGradePeriod = null;
        }

        return Inertia::render('ImportXls', [
            'kathCount' => $numKath ? strval($numKath) : '',
            'mathCount' => $numMath ? strval($numMath) : '',
            'progCount' => $numProg ? strval($numProg) : '',
            'activeGradePeriod' => $activeGradePeriod
        ]);
    }

    public function insertUsers()
    {
        //ddd(request()->file('xls'));
        $import = new UsersImport;
        Excel::import($import, request()->file('xls'));
        $insertedUsersCount = $import->getUsersCount();
        $insertedAnatheseisCount = $import->getAnatheseisCount();
        $failures = $import->failures();
        $successData = '';
        $failData = '';
        if ($insertedUsersCount) {
            $successData = "Καταχωρίστηκαν $insertedUsersCount καθηγητές και $insertedAnatheseisCount αναθέσεις.";
        }
        $counter = 1;
        foreach ($failures as $failure) {
            $failData .= "<strong>$counter<br>";
            $failData .= "γραμμή " . $failure->row() . ":</strong>  " . $failure->errors()[0] . "<br>";
            $failData .= "<strong>Δεδομένα:</strong> " . implode(', ', $failure->values()) . "<br>";
            $counter++;
        }
        return redirect('importXls')->with('message', [
            'success' => $successData,
            'error' => $failData
        ]);
    }


    public function delKathigites()
    {
        $delKathigitesCount = User::count() - 1;
        $firstUser = User::first();
        User::truncate();
        Anathesi::truncate();
        User::create([
            'email' => $firstUser->email,
            'name' => $firstUser->name,
            'password' => $firstUser->password,
            'role_id' => 1
        ]);
        return redirect('importXls')->with('message', [
            'success' => "Επιτυχημένη διαγραφή $delKathigitesCount εκπαιδευτικών."
        ]);
    }


    public function exportKathigitesXls()
    {
        return Excel::download(new KathigitesExport, 'Καθηγητές_και_Αναθέσεις_by_GΘ.xls');
    }

    public function insertStudents()
    {
        //ddd(request()->file('xls'));
        $import = new StudentsImport;
        Excel::import($import,  request()->file('xls'));
        $insertedStudentsCount = $import->getStudentsCount();
        $insertedTmimataCount = $import->getTmimataCount();
        $failures = $import->failures();
        $successData = '';
        $failData = '';
        if ($insertedStudentsCount) {
            $insertedStudentsCount = $import->getStudentsCount();
            $successData = "Καταχωρίστηκαν $insertedStudentsCount μαθητές και $insertedTmimataCount τμήματα.";
        }
        $counter = 1;
        foreach ($failures as $failure) {
            $failData .= "<strong>$counter<br>";
            $failData .= "γραμμή " . $failure->row() . ":</strong>  " . $failure->errors()[0] . "<br>";
            $failData .= "<strong>Δεδομένα:</strong> " . implode(', ', $failure->values()) . "<br>";
            $counter++;
        }
        return redirect('importXls')->with('message', [
            'success' => $successData,
            'error' => $failData
        ]);
    }

    public function delStudents()
    {
        $delStudentsCount = Student::count();
        Student::truncate();
        Tmima::truncate();
        return redirect('importXls')->with('message', [
            'success' => "Επιτυχημένη διαγραφή $delStudentsCount μαθητών."
        ]);
    }

    public function exportMathitesXls()
    {
        return Excel::download(new MathitesExport, 'Μαθητές_και_Τμήματα_by_GΘ.xls');
    }


    public function insertProgram()
    {
        //ddd(request()->file('xls'));
        $import = new ProgramImport;
        Program::truncate();
        Excel::import($import, request()->file('xls'));
        $failures = $import->failures();
        $counter = 1;
        $failData = '';
        foreach ($failures as $failure) {
            $failData .= "<strong>$counter<br>";
            $failData .= "γραμμή " . $failure->row() . ":</strong>  " . $failure->errors()[0] . "<br>";
            $failData .= "<strong>Δεδομένα:</strong> " . implode(', ', $failure->values()) . "<br>";
            $counter++;
        }
        return redirect('importXls')->with('message', [
            'success' => $failData ? '' : 'Επιτυχής εισαγωγή προγράμματος.',
            'error' => $failData
        ]);
    }


    public function delProgram()
    {
        Program::truncate();
        return redirect('importXls')->with('message', [
            'success' => "Επιτυχημένη διαγραφή προγράμματος."
        ]);
    }

    public function exportProgramXls()
    {
        return Excel::download(new ProgramExport, 'Ωρολόγιο_Πρόγραμμα_by_GΘ.xls');
    }

    public function exportApousiesXls()
    {
        $apoDate = str_replace("-", "", request()->apoDate);
        $eosDate = str_replace("-", "", request()->eosDate);
        if ($apoDate && $eosDate) {
            if ($apoDate == $eosDate) {
                $filenameDates = '_για_τις_' . $apoDate;
            } else {
                $filenameDates = '_από_' . $apoDate . '_έως_' . $eosDate;
            }
        } elseif (!$apoDate && $eosDate) {
            $filenameDates = '_έως_τις_' . $eosDate;
        } elseif ($apoDate && !$eosDate) {
            $filenameDates = '_από_τις_' . $apoDate;
        } else {
            $filenameDates = '_για_τις_' . Carbon::now()->format("Ymd");
        }

        return Excel::download(new ApousiesForDayExport($apoDate, $eosDate), 'myschool_Eisagwgh_Apousiwn_Mazika_apo_Excel_by_GΘ' . $filenameDates . '.xls');
    }

    public function exportXls()
    {
        $gradePeriod = Setting::getValueOf('activeGradePeriod');
        if ($gradePeriod > 0) {
            $activeGradePeriod = config('gth.periods')[Setting::getValueOf('activeGradePeriod')];
        } else {
            $activeGradePeriod = null;
        }
        return Inertia::render('ExportXls', [
            'activeGradePeriod' => $activeGradePeriod,
            'token' => csrf_token()
        ]);
    }


    public function insertMyschoolApousies()
    {
        $import = new ApousiesMyschoolImport;
        Excel::import($import, request()->file('xls'));
        $insertedStudentsApousiesCount = $import->getStudentsApousiesCount();
        $insertedDaysApousiesCount = $import->getDaysApousiesCount();
        return redirect()->back()->with(['message' => ['success' => "Εισήχθηκαν $insertedDaysApousiesCount ημέρες απουσιών σε $insertedStudentsApousiesCount μαθητές."]]);
    }


    public function delApousies($keep = null)
    {
        if (!$keep) {
            Apousie::truncate();
        } else {
            Apousie::where('date', '<', Carbon::now()->format("Ymd") - $keep)->delete();
        }
        return redirect()->route('admin')->with(['deletedApousies' => 1]);
    }

    public function export()
    {
        return view('export');
    }


    public function exportApousiesMyschoolXls()
    {
        return Excel::download(new ApousiesMyschoolExport, 'Πρότυπο για εισαγωγή απουσιών από Myschool_by_GΘ.xls');
    }

    public function populateXls()
    {
        $lessonsRow = request()->input('rowLabels');
        $startRow = $lessonsRow + 1;
        $startCol = request()->input('lessonColumn');
        $amCol = request()->input('amColumn');
        Setting::setValueOf('187XlsLabelsRow', $lessonsRow);
        Setting::setValueOf('187XlsAmCol', $amCol);
        Setting::setValueOf('187XlsFirstLessonCol', $startCol);

        // ανοίγω το xls
        $file = request()->file('xls');
        $filename = request()->file('xls')->getClientOriginalName();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $spreadsheet = $reader->load($file);

        $sheet = $spreadsheet->getActiveSheet();
        $maxCol = $sheet->getHighestColumn();
        $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCol);
        $maxRow = $sheet->getHighestRow();

        // βάζω σε πίνακα[μάθημα] = στήλη τις στήλες των μαθημάτων[]
        $lessons = array();
        for ($col = $startCol; $col < $maxColIndex; $col++) {
            $lessons[$sheet->getCellByColumnAndRow($col, $lessonsRow)->getValue()] = $col;
        }
        // βάζω σε πίνακα[αμ] = γραμμή τις γραμμές των αρ μητρώου
        $amArr = array();
        for ($row = $startRow; $row < $maxRow + 1; $row++) {
            $amArr[$sheet->getCellByColumnAndRow($amCol, $row)->getValue()] = $row;
        }

        // παίρνω τους βαθμούς για την ενεργή περίοδο
        $grades = Grade::where('period_id', Setting::getValueOf('activeGradePeriod'))->get(['anathesi_id', 'student_id', 'grade']);
        // φτιάχνω πίνακα[μάθημα][αμ]=βαθμός 
        $finalGrades = array();
        foreach ($grades as $grade) {
            if (!Anathesi::find($grade->anathesi_id)) continue;
            $finalGrades[Anathesi::find($grade->anathesi_id)->mathima][$grade->student_id] = $grade->grade;
        }

        // γεμίζω το xls
        foreach ($amArr as $am => $row) {
            foreach ($lessons as $lesson => $col) {
                $sheet->getCellByColumnAndRow($col, $row)->setValue($finalGrades[$lesson][$am] ?? null);
            }
        }

        // το στέλνω για download στο χρήστη
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);

        header('Content-type: application/ms-excel');
        header('Content-Disposition: attachment; filename=' . urlencode($filename));
        $writer->save('php://output');

        return;
    }

    public function insertToDB()
    {
        //ddd(request()->all());
        $lessonsRow = request()->input('rowLabels');
        $startRow = $lessonsRow + 1;
        $startCol = request()->input('lessonColumn');
        $amCol = request()->input('amColumn');
        Setting::setValueOf('187XlsLabelsRow', $lessonsRow);
        Setting::setValueOf('187XlsAmCol', $amCol);
        Setting::setValueOf('187XlsFirstLessonCol', $startCol);

        // ανοίγω το xls
        $file = request()->file('xls');
        $filename = request()->file('xls')->getClientOriginalName();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $spreadsheet = $reader->load($file);

        $sheet = $spreadsheet->getActiveSheet();
        $maxCol = $sheet->getHighestColumn();
        $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($maxCol);
        $maxRow = $sheet->getHighestRow();

        // βάζω σε πίνακα[μάθημα] = στήλη τις στήλες των μαθημάτων[]
        $lessons = array();
        for ($col = $startCol; $col < $maxColIndex; $col++) {
            $lessons[$sheet->getCellByColumnAndRow($col, $lessonsRow)->getValue()] = $col;
        }
        // βάζω σε πίνακα[αμ] = γραμμή τις γραμμές των αρ μητρώου
        $amArr = array();
        for ($row = $startRow; $row < $maxRow + 1; $row++) {
            $amArr[$sheet->getCellByColumnAndRow($amCol, $row)->getValue()] = $row;
        }

        // πίνακας[αμ] = [τμη1, τμη2, τμη3] με τμήματα κάθε μαθητή
        $tmimata =  Tmima::get(['student_id', 'tmima']);
        $arrTmimata = array();
        foreach ($tmimata as $tmi) {
            $arrTmimata[$tmi->student_id][] = $tmi->tmima;
        }
        // πίνακας[μάθημα][τμημα] = id ανάθεσης
        // για να βρώ τον κωδικό ανάθεσης
        $anatheseis = Anathesi::get(['id', 'mathima', 'tmima']);
        $arrAnatheseis = array();
        foreach ($anatheseis as $anath) {
            $arrAnatheseis[$anath->mathima][$anath->tmima] = $anath->id;
        }

        $insertedGradesCount = 0;
        // για κάθε am
        foreach ($amArr as $am => $row) {
            // για κάθε μάθημα
            foreach ($lessons as $lesson => $col) {
                // παίρνω το βαθμό από το κελί
                $grade = $sheet->getCellByColumnAndRow($col, $row)->getValue();


                // βρίσκω το κοινό τμήμα μαθητή και μαθήματος και με αυτό βρίσκω το id της ανάθεσης
                if ($arrTmimata[$am] && array_key_exists($lesson, $arrAnatheseis)) {
                    $tmima = array_values(array_intersect($arrTmimata[$am], array_keys($arrAnatheseis[$lesson])));
                }
                //info($lesson . ' - ' . $am . ' - ' . json_encode($tmima));
                $anathesi_id = null;
                if (count($tmima)) {
                    $anathesi_id = $arrAnatheseis[$lesson][$tmima[0]];
                }
                //echo $am . " - " . $lesson  . " - " . $tmima[0]  . " - " . $anathesi_id  . " - " . $grade . "<hr>";

                // αν υπάρχει id
                if ($anathesi_id) {
                    if ($grade) {
                        // ενημερώνω ή εισάγω
                        Grade::updateOrCreate(['anathesi_id' => $anathesi_id, 'student_id' =>  $am, 'period_id' => Setting::getValueOf('activeGradePeriod')], [
                            'grade' => str_replace(".", ",", $grade),
                        ]);
                    } else {
                        // αν δεν υπάρχει βαθμός διαγράφω
                        Grade::where('anathesi_id', $anathesi_id)->where('student_id', $am)->where('period_id', Setting::getValueOf('activeGradePeriod'))->delete();
                    }
                    $insertedGradesCount++;
                }
            }
        }
        // επιστρέφω στη σελίδα
        return redirect()->back()->with(['message' => ['success' => "Επιτυχής καταχώριση $insertedGradesCount βαθμών"]]);
    }
}
