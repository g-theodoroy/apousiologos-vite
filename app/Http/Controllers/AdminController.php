<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Student;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{

    public function index()
    {
        $boolKeys = config('gth.boolean_setting_keys');

        $settings = Setting::getValues();
        foreach ($boolKeys as $key) {
            if (array_key_exists($key, $settings)) {
                $settings[$key] = $settings[$key] == 1 ?? false;
            } else {
                $settings[$key] = false;
            }
        }

        return Inertia::render('Settings', [
            'periods' => config('gth.periods'),
            'initialSettings' => $settings,
        ]);
    }

    public function store()
    {
        //dd(request()->all());

        $boolKeys = config('gth.boolean_setting_keys');

        foreach (request()->all() as $key => $value) {
            if (in_array($key, $boolKeys)) {
                $val = $value ? 1 : null;
                Setting::setValueOf($key, $val);
            } else {
                Setting::setValueOf($key, $value);
            }
        }

        return redirect()->route('settings')->with(['message' => ['success' => 'Επιτυχής ενημέρωση.']]);
    }


    /**
     * Εμφανίζει τη σελίδα "Εισαγωγή xls"
     */
    public function importXls()
    {
        $numKath = User::getNumOfKathigites() - 1;
        $numMath = Student::getNumOfStudents();
        $numProg = Program::getNumOfHours();
        $gradePeriod = Setting::getValueOf('activeGradePeriod');
        if ($gradePeriod > 0) {
            $activeGradePeriod = config('gth.periods')[$gradePeriod];
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
    /**
     * Εμφανίζει τη σελίδα "Εξαγωγή xls"
     */
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
}
