<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Tmima;
use App\Models\Setting;
use App\Models\Anathesi;
use App\Exports\BathmologiaExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AdminGradesController extends Controller
{
    
    /**
     * Την καλώ από Διαχείριση -> Εισαγωγή xls -> Βαθμοί από 187.xls
     */
    public function import()
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
        $maxColIndex = Coordinate::columnIndexFromString($maxCol);
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


    /**
     * Την καλώ από Διαχείριση -> Εξαγωγή xls -> Εξαγωγή αρχείου 187.xls όλων των μαθητών όλων των τάξεων για καταχώριση στο myschool
     */
    public function export()
    {
        $filename = Setting::getValueOf('schoolName') . ' - ' . config('gth.periods')[Setting::getValueOf('activeGradePeriod')] . ' - 187.xls';
        return Excel::download(new BathmologiaExport, $filename);
    }


    /**
     * Την καλώ από Διαχείριση -> Εξαγωγή xls -> Ενημέρωση των εξηχθέντων αρχείων 187.xls για κάθε τάξη από το myschool
     */
    public function update()
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
        $maxColIndex = Coordinate::columnIndexFromString($maxCol);
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

}
