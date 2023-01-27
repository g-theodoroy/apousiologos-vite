<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;


class CheckUpdates {

    public function chkForUpdates()
    {

        try {
            // έλεγχος εάν έχουν γίνει αλλαγές στο github
            $url = 'https://api.github.com/repos/g-theodoroy/apousiologos-v2/commits';
            $opts = ['http' => ['method' => 'GET', 'header' => ['User-Agent: PHP']]];
            $context = stream_context_create($opts);
            $json = file_get_contents($url, false, $context);
            $commits = json_decode($json, true);
        } catch (\Throwable $e) {
            report($e);
            $commits = null;
        }
        // εάν υπάρχουν commits
        if ($commits) {
            if (Auth::user()->permissions['admin']) {
                // διαβάζω από το αρχείο .updateCheck το id του τελευταίου αποθηκευμένου commit
                $file = storage_path('app/.updateCheck');
                if (file_exists($file)) {
                    // αν διαφέρει με το id του τελευταίου commit στο github
                    // στέλνω ειδοποίηση για την υπάρχουσα ενημέρωση
                    if ($commits[0]['sha'] != file_get_contents($file)) {
                        $message = 'Έγιναν τροποποιήσεις στον κώδικα του Ηλ.Απουσιολόγου στο Github.<br><br>';
                        $message .= 'Αν επιθυμείτε <a href="https://github.com/g-theodoroy/apousiologos-examsplanner-bathmologia/commits/" target="_blank"><u>εξετάστε τον κώδικα</u></a> και ενημερώστε την εγκατάστασή σας.<br><br>';
                        $message .= 'Για να μην εμφανίζεται το παρόν μήνυμα καντε κλικ εδώ: <a href="setUpdated/' . $commits[0]['sha'] . '" >"<u>Ενημερώθηκε</u>"</a>';
                        return redirect()->route('apousiologos')->with(['message' => ['checkUpdates' => $message]]);
                    }
                } else {
                    // αν δεν υπάρχει το αρχείο .updateCheck το
                    // δημιουργώ και γράφω το id του τελευταίου commit
                    file_put_contents($file, $commits[0]['sha']);
                }
            }
        }
        return;
    }


    public function setUpdated($commit_id){

        $file = storage_path('app/.updateCheck');

        file_put_contents($file, $commit_id);

        $landingPage = Setting::getValueOf('landingPage');

        return redirect()->route($landingPage)->with(['message' => ['saveSuccess' => 'Επιτυχής ενημέρωση της κατάστασης του Ηλ.Απουσιολόγου σε "Ενημερώθηκε"']]);
    }




}


