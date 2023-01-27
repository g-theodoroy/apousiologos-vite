<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApousiesMyschoolExport;
use App\Imports\ApousiesMyschoolImport;

class AdminMyschoolApousiesController extends Controller
{
    /**
     * Την καλώ από Διαχείριση -> Εισαγωγή xls -> Απουσίες από το myschool -> Εισαγωγή xls
     */
    public function import()
    {
        $import = new ApousiesMyschoolImport;
        Excel::import($import, request()->file('xls'));
        $insertedStudentsApousiesCount = $import->getStudentsApousiesCount();
        $insertedDaysApousiesCount = $import->getDaysApousiesCount();
        return redirect()->back()->with(['message' => ['success' => "Εισήχθηκαν $insertedDaysApousiesCount ημέρες απουσιών σε $insertedStudentsApousiesCount μαθητές."]]);
    }


    /**
     * Την καλώ από Διαχείριση -> Εισαγωγή xls -> Απουσίες από το myschool -> Πρότυπο_xls
     */
    public function export()
    {
        return Excel::download(new ApousiesMyschoolExport, 'Πρότυπο για εισαγωγή απουσιών από Myschool_by_GΘ.xls');
    }
}
