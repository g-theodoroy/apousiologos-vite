<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Exports\ProgramExport;
use App\Imports\ProgramImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AdminProgramController extends Controller
{

    /**
     * Εισάγει το πρόγραμμα από αρχείο xls
     */
    public function import()
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

    /**
     * Εξάγει το πρόγραμμα σε αρχείο xls
     */
    public function export()
    {
        return Excel::download(new ProgramExport, 'Ωρολόγιο_Πρόγραμμα_by_GΘ.xls');
    }


    /**
     * Διαγράφει το πρόγραμμα
     */
    public function delete()
    {
        Program::truncate();
        return redirect('importXls')->with('message', [
            'success' => "Επιτυχημένη διαγραφή προγράμματος."
        ]);
    }


}
