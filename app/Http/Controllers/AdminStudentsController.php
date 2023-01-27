<?php

namespace App\Http\Controllers;

use App\Models\Tmima;
use App\Models\Student;
use App\Exports\MathitesExport;
use App\Imports\StudentsImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AdminStudentsController extends Controller
{

    /**
     * Εισάγει τους μαθητές από αρχείο xls
     */
    public function import()
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

    /**
     * Εξάγει τους μαθητές σε αρχείο xls
     */
    public function export()
    {
        return Excel::download(new MathitesExport, 'Μαθητές_και_Τμήματα_by_GΘ.xls');
    }


    /**
     * Διαγράφει όλους τους μαθητές
     */
    public function delete()
    {
        $delStudentsCount = Student::count();
        Student::truncate();
        Tmima::truncate();
        return redirect('importXls')->with('message', [
            'success' => "Επιτυχημένη διαγραφή $delStudentsCount μαθητών."
        ]);
    }


}
