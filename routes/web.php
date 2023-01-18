<?php

use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExamsController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\ApousiologosController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    if(Schema::hasTable('settings')){
        $isTeacher = Auth::user() ? Auth::user()->permissions['teacher'] : false;
        $isStudent = Auth::user() ? Auth::user()->permissions['student'] : false;
        // βρίσκω την αρχική σελίδα
        $landingPage = Setting::getValueOf('landingPage');
        if ($isStudent) {
            $landingPage = 'apousiologos';
        } elseif ($isTeacher) {
            if ($landingPage == 'exams') {
                $allowExams = Setting::getValueOf('allowExams') == '1' ?? false;
                if (!$allowExams) $landingPage = 'apousiologos';
            }
            if ($landingPage == 'grades') {
                $activeGradePeriod = Setting::getValueOf('activeGradePeriod') <> 0 ?? false;
                if (!$activeGradePeriod) $landingPage = 'apousiologos';
            }
        }
    }else{
        $landingPage = 'apousiologos';
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'welcome' => URL::route('welcome'),
        'landingPage' => $landingPage,
    ]);
})->name('welcome');


Route::get('/apousiologos/{selectedTmima?}/{date?}', [ApousiologosController::class, 'index'])->middleware(['auth', 'verified', 'redirectAfterLogin', 'dateBackAllowed'])->name('apousiologos');
Route::post('/apousiologos/store/{selectedTmima?}/{date?}', [ApousiologosController::class, 'store'])->middleware(['auth', 'verified', 'redirectAfterLogin'])->name('apousiologos.store');
Route::get('/emailParent/{am?}/{date?}/{tmima?}', [ApousiologosController::class, 'sendEmailToParent'])->middleware(['auth', 'verified', 'redirectAfterLogin'])->name('emailParent');


Route::get('/exams', [ExamsController::class, 'index'])->middleware(['auth', 'verified', 'teacher'])->name('exams');
Route::post('/exams/store', [ExamsController::class, 'store'])->middleware(['auth', 'verified', 'teacher'])->name('exams.store');
Route::put('/exams/update/{event}/{date}', [ExamsController::class, 'update'])->middleware(['auth', 'verified', 'teacher'])->name('exams.update');
Route::get('/exams/tmimata/{date}', [ExamsController::class, 'tmimata'])->middleware(['auth', 'verified', 'teacher'])->name('tmimata');
Route::delete('/deleteExam/{id}', [ExamsController::class, 'delete'])->middleware(['auth', 'verified', 'teacher'])->name('deleteExam');

Route::get('/exportExamsXls', [ExamsController::class, 'exportExamsXls'])->middleware(['auth', 'verified', 'admin'])->name('exportExamsXls');
Route::get('/userExams', [ExamsController::class, 'userExams'])->middleware(['auth', 'verified', 'teacher'])->name('userExams');


Route::get('/grades/{selectedAnathesiId?}', [GradeController::class, 'index'])->middleware(['auth', 'verified', 'teacher', 'anathesi'])->name('grades');
Route::post('/grades/store/{selectedAnathesiId?}', [GradeController::class, 'store'])->middleware(['auth', 'verified', 'teacher'])->name('grades.store');


Route::get('/teachers', [TeachersController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('teachers');
Route::post('/teachers/store', [TeachersController::class, 'store'])->middleware(['auth', 'verified', 'admin'])->name('teachers.store');
Route::get('/uniqueEmail/{email}', [TeachersController::class, 'uniqueEmail'])->middleware(['auth', 'verified', 'teacher'])->name('uniqueEmail');
Route::delete('/deleteTeacher/{id}', [TeachersController::class, 'delete'])->middleware(['auth', 'verified', 'admin'])->name('deleteTeacher');


Route::get('/students', [StudentsController::class, 'index'])->middleware(['auth', 'verified', 'teacher'])->name('students');
Route::post('/students/store', [StudentsController::class, 'store'])->middleware(['auth', 'verified', 'teacher'])->name('students.store');
Route::get('/studentUnique/{id}', [StudentsController::class, 'studentUnique'])->middleware(['auth', 'verified', 'teacher'])->name('studentUnique');
Route::delete('/studentDelete/{id}', [StudentsController::class, 'delete'])->middleware(['auth', 'verified', 'teacher'])->name('studentDelete');


Route::post('/apousiesStore', [StudentsController::class, 'apousiesStore'])->middleware(['auth', 'verified', 'teacher'])->name('apousiesStore');
Route::delete('/apousiesDelete/{id}', [StudentsController::class, 'apousiesDelete'])->middleware(['auth', 'verified', 'teacher'])->name('apousiesDelete');


Route::get('/settings', [AdminController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('settings');
Route::post('/settings/store', [AdminController::class, 'setConfigs'])->middleware(['auth', 'verified', 'admin'])->name('settings.store');


Route::get('/importXls', [AdminController::class, 'importXls'])->middleware(['auth', 'verified', 'admin'])->name('importXls');


Route::post('/insertUsers', [AdminController::class, 'insertUsers'])->middleware(['auth', 'verified', 'admin'])->name('insertUsers');
Route::get('/exportKathXls', [AdminController::class, 'exportKathigitesXls'])->middleware(['auth', 'verified', 'admin'])->name('exportKathXls');
Route::delete('/delKath', [AdminController::class, 'delKathigites'])->middleware(['auth', 'verified', 'admin'])->name('delKath');


Route::post('/insertStudents', [AdminController::class, 'insertStudents'])->middleware(['auth', 'verified', 'admin'])->name('insertStudents');
Route::get('/exportMathXls', [AdminController::class, 'exportMathitesXls'])->middleware(['auth', 'verified', 'admin'])->name('exportMathXls');
Route::delete('/delMath', [AdminController::class, 'delStudents'])->middleware(['auth', 'verified', 'admin'])->name('delMath');


Route::post('/insertProgram', [AdminController::class, 'insertProgram'])->middleware(['auth', 'verified', 'admin'])->name('insertProgram');
Route::get('/exportProgXls', [AdminController::class, 'exportProgramXls'])->middleware(['auth', 'verified', 'admin'])->name('exportProgXls');
Route::delete('/delProg', [AdminController::class, 'delProgram'])->middleware(['auth', 'verified', 'admin'])->name('delProg');


Route::get('/exportApouxls', [AdminController::class, 'exportApousiesXls'])->middleware(['auth', 'verified', 'admin'])->name('exportApouxls');


Route::get('/exportXls', [AdminController::class, 'exportXls'])->middleware(['auth', 'verified', 'admin'])->name('exportXls');
Route::get('/gradesXls', [GradeController::class, 'exportGradesXls'])->middleware(['auth', 'verified', 'admin'])->name('gradesXls');
Route::post('/populateXls', [AdminController::class, 'populateXls'])->middleware(['auth', 'verified', 'admin'])->name('populateXls');
Route::post('/insertToDB', [AdminController::class, 'insertToDB'])->middleware(['auth', 'verified', 'admin'])->name('insertToDB');


Route::post('/importMyschoolApousies', [AdminController::class, 'insertMyschoolApousies'])->middleware(['auth', 'verified', 'admin'])->name('importMyschoolApousies');
Route::get('/exportMyschoolApousies', [AdminController::class, 'exportApousiesMyschoolXls'])->middleware(['auth', 'verified', 'admin'])->name('exportMyschoolApousies');


Route::get('/about', function () {
    return Inertia::render('About', [
        'infoUrl' => asset('files/Οδηγίες ρύθμισης κ χρήσης Ηλ.Απουσιολόγου.pdf')
    ]);
})->name('about');


Route::get('/logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->middleware(['auth', 'verified', 'admin'])->name('logs');


require __DIR__ . '/auth.php';


Route::fallback(function () {
    return redirect()->route('welcome');
});
