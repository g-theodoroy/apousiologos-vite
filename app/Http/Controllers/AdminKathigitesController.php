<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Anathesi;
use App\Imports\UsersImport;
use App\Exports\KathigitesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class AdminKathigitesController extends Controller
{

    /**
     * Εισάγει τους καθηγητές από αρχείο xls
     */
    public function import()
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

    /**
     * Εξάγει τους καθηγητές σε αρχείο xls
     */
    public function export()
    {
        return Excel::download(new KathigitesExport, 'Καθηγητές_και_Αναθέσεις_by_GΘ.xls');
    }

    /**
     * Διαγράφει όλους τους καθηγητές εκτός από τον Διαχειριστή (1ος χρήστης id=1)
     */
    public function delete()
    {
        $delKathigitesCount = User::count() - 1;
        $firstUser = User::first();
        $users = User::all();
        foreach($users as $user){
            $user->anatheseis()->detach();
        }
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
}
