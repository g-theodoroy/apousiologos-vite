<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Grade;
use App\Models\Tmima;
use App\Models\Apousie;
use App\Models\Program;
use App\Models\Student;
use App\Models\Anathesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class StudentsController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
    $this->middleware('web');
    $this->middleware('admin');
  }

  public function index()
  {
    // μεταβλητές για το table
    $tableLabels = ['Α/Α','ΑΜ','Απ', 'Πμ', 'Επώνυμο', 'Όνομα', 'Πατρώνυμο', 'Email', 'Τμήματα', 'Ενέργεια'];
    $fields = ['', 'id','sumap', 'sumapov', 'eponimo', 'onoma', 'patronimo', 'email', 'tmimataStr', ''];
    $tableApouLabels = ['Α/Α',  'Συν', 'Ημ/νια','Απ', 'Πμ', '1η', '2η', '3η', '4η', '5η', '6η', '7η', 'Ενέργεια'];
    $numOfHours = Program::getNumOfHours();
    
    // έλεγχος των τιμών
    request()->validate([
      'page' => 'int',
      'rows' => 'int',
      'field' => "in:id,sumap,sumapov,eponimo,onoma,patronimo,email,tmimataStr",
      'direction' => 'in:asc,desc'
    ]);

    // φτιάχνω queryStr για να το προσθέσω στα pagination urls στο component Pagination.vue
    $queryStr = '';
    foreach (request()->all() as $key => $value) {
      if ($key == 'page') continue;
      if ($value)  $queryStr .= "&$key=$value";
    }

    // αποθηκεύω τα φίλτρα για να τα γυρίσω πίσω
    $filters = array();
    $filters['page'] = request()->page ?? 1;
    $filters['rows'] = request()->rows ?? 10;
    $filters['search'] = request()->search ?? '';
    $filters['field'] = request()->field ?? '';
    $filters['direction'] = request()->direction;

    // παίρνω τους μαθητές
    $students = Student::select('id', 'eponimo', 'onoma', 'patronimo','email','sumap', 'sumapov', 'tmimataStr')
      ->with('apousies:student_id,id,date,apousies,apovoles,teachers')->with('tmimata:student_id,id,tmima')
      ->leftjoin(
        DB::raw(
        "(SELECT  t1.student_id as student_id, sumap, sumapov, tmimata as tmimataStr
            FROM
              (SELECT student_id, GROUP_CONCAT(tmima, ', ') as tmimata
                FROM
                  (SELECT student_id, tmima
                    FROM
                      tmimas
                    order by length(tmima) )
                    GROUP BY student_id) as t1

                left join

                    (select student_id, sum(sumapday) as sumap, sum(sumapovday) as sumapov
                      FROM
                        (select student_id,
                          cast(apousies % 10000000 / 1000000 as int)
                          +cast(apousies % 1000000 / 100000 as int)
                          +cast(apousies % 100000 / 10000 as int)
                          +cast(apousies % 10000 / 1000 as int)
                          +cast(apousies % 1000 / 100 as int)
                          + cast(apousies % 100 / 10 as int)
                          + cast(apousies % 10 as int) as sumapday,
                          cast(apovoles % 10000000 / 1000000 as int)
                          +cast(apovoles % 1000000 / 100000 as int)
                          +cast(apovoles % 100000 / 10000 as int)
                          +cast(apovoles % 10000 / 1000 as int)
                          +cast(apovoles % 1000 / 100 as int)
                          + cast(apovoles % 100 / 10 as int)
                          + cast(apovoles % 10 as int) as sumapovday
                            from apousies)
                          group by student_id) as t2

					      on  t1.student_id = t2.student_id

          UNION

          SELECT  t2.student_id as student_id, sumap, sumapov, tmimata as tmimataStr
            FROM
              (select student_id, sum(sumapday) as sumap, sum(sumapovday) as sumapov
                FROM
                  (select student_id,
                    cast(apousies % 10000000 / 1000000 as int)
                    +cast(apousies % 1000000 / 100000 as int)
                    +cast(apousies % 100000 / 10000 as int)
                    +cast(apousies % 10000 / 1000 as int)
                    +cast(apousies % 1000 / 100 as int)
                    + cast(apousies % 100 / 10 as int)
                    + cast(apousies % 10 as int) as sumapday,
                    cast(apovoles % 10000000 / 1000000 as int)
                    +cast(apovoles % 1000000 / 100000 as int)
                    +cast(apovoles % 100000 / 10000 as int)
                    +cast(apovoles % 10000 / 1000 as int)
                    +cast(apovoles % 1000 / 100 as int)
                    + cast(apovoles % 100 / 10 as int)
                    + cast(apovoles % 10 as int) as sumapovday
                      from apousies)
                    group by student_id) as t2

                left join

                    (SELECT student_id, GROUP_CONCAT(tmima, ', ') as tmimata
							        FROM
								        (SELECT student_id, tmima
								        	FROM
										        tmimas
									        order by length(tmima) )
									        GROUP BY student_id) as t1
                          
				        on  t1.student_id = t2.student_id)"
        ),
        'id',
        '=',
        'student_id'
      );

    // φιλτράρω με ότι ήρθε από την πληκτρολόγηση στο input
    if (request()->search) {
      $searchStr = request()->search ?? '';
      $students = $students->where('id', 'LIKE',  '%' . $searchStr . '%')
        ->orWhere('sumap', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('sumapov', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('eponimo', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('eponimo', 'LIKE', '%' . mb_strtoupper($searchStr) . '%')
        ->orWhere('onoma', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('onoma', 'LIKE', '%' .  mb_strtoupper($searchStr) . '%')
        ->orWhere('patronimo', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('patronimo', 'LIKE', '%' . mb_strtoupper($searchStr) . '%')
        ->orWhere('email', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('tmimataStr', 'LIKE', '%' . $searchStr . '%')
        ->orWhere('tmimataStr', 'LIKE', '%' . mb_strtoupper($searchStr) . '%');
    }

    // ταξινόμηση
    if (request()->field) {
      $students = $students->orderby(request()->field, request()->direction);
    } else {
      $students = $students->orderby('eponimo')->orderby('onoma')->orderby('patronimo');
      $filters['field'] = 'eponimo';
      $filters['direction'] = 'asc';
    }
    // αν είμαστε στην τελευταία σελίδα πχ 28
    // ξαναπροωθώ στην νέα τελευτάια σελίδα πχ 13
    if (request()->rows) {
      $newPage = ceil(($students->count() / request()->rows));
      if ($newPage < request()->page) {
        return redirect("students?page=" . $newPage . $queryStr);
      }
    }

    $students = $students->paginate(request()->rows ?? 10);

    $showApouForStu = $students->pluck('id');
    foreach ($showApouForStu as $key => $value) {
      unset($showApouForStu[$key]);
      $showApouForStu[$value] = false;
    }

    $apousiesForStudent = [];
    $tmimataCount = 0;
    foreach ($students as $student) {
      if ($student->apousies) {
        $apousies = $student->apousies->sortBy('date');
        $daysSum = 0;
        $daysApovSum = 0;
        $aa = 1;
        foreach ($apousies as $apou) {
          $daySum =  substr_count($apou->apousies , '1', 0, $numOfHours)  ??  null;
          $dayApovSum = substr_count($apou->apovoles , '1')  ??  null;
          $daysSum += $daySum;
          $daysApovSum += $dayApovSum;
          $arrApou = array();
          $num = 1;
          foreach (str_split(substr($apou->apousies,0,$numOfHours)) as $value) {
            $value == '1' ? $arrApou['apou'][$num] = true : $arrApou['apou'][$num] = false;
            $num++;
          }
          $num = 1;
          foreach (explode('-',$apou->teachers) as $value) {
            $value == '0' ? $arrApou['teach'][$num] = '' : $arrApou['teach'][$num] = $value;
            $num++;
          }
          $num = 1;
          if(!$apou->apovoles) $apou->apovoles = str_repeat("0", $numOfHours);
          foreach (str_split($apou->apovoles) as $value) {
            $value == '1' ? $arrApou['apov'][$num] = true : $arrApou['apov'][$num] = false;
            $num++;
          }


          $apou->sum = $daySum;
          $apou->tot = $daysSum;
          $apou->sumapov = $dayApovSum;
          $apou->totapov = $daysApovSum;
          $apou->arrApou = $arrApou;
          $apou->dateShow = Carbon::createFromFormat("Ymd", $apou->date)->format("d/m/Y");
          $apou->date = Carbon::createFromFormat("Ymd", $apou->date)->format("Y-m-d");
          $apou->aa = $aa;
          unset($apou->apousies);
          unset($apou->apovoles);
          unset($apou->teachers);
          unset($apou->student_id);
          $aa++;
        }
        $apousiesDesc = $apousies->sortByDesc('tot');
        $apousiesForStudent[$student->id] = array_values($apousiesDesc->toArray());
        unset($student->apousies);
      }
      if ($tmimataCount < count($student->tmimata)) $tmimataCount = count($student->tmimata);
      foreach ($student->tmimata as $tmima) unset($tmima->student_id);
    }
    $tmimataRows = intval($tmimataCount / 2) + 1;
    $tmimata = array();
    for ($i = 0; $i < $tmimataRows * 2; $i++) {
      $tmimata[] = '';
    }
    $formStudents = [
      'id' => '',
      'oldId' => '',
      'eponimo' => '',
      'onoma' => '',
      'patronimo' => '',
      'email' => '',
      'tmima' => $tmimata
    ];

    $formApousies = [
      'student_id' => '',
      'date' => ''
    ];
    $totalHours = Program::getNumOfHours();
    for ($i = 1; $i <= $totalHours; $i++) {
      $formApousies['apou'][$i] = false;
      $formApousies['apov'][$i] = false;
      $formApousies['teach'][$i] = '';
    }

    return Inertia::render('Students', [
      'students' => $students,
      'arrNames' => User::getNames(),
      'tableLabels' => $tableLabels,
      'tableApouLabels' => $tableApouLabels,
      'filters' => $filters,
      'fields' =>  $fields,
      'queryStr' => $queryStr,
      'iniShowApouForStu' => $showApouForStu,
      'apousiesForStudent' => $apousiesForStudent,
      'tmimataRows' => $tmimataRows,
      'formStudents' => $formStudents,
      'formApousies' => $formApousies,
      'totalHours' => $totalHours
    ]);
  }

  public function store(Request $request)
  {
    if ($this->studentUnique($request->id)) {
      $message = "Επιτυχής ενημέρωση μαθητή/τριας";
    } else {
      $message = "Επιτυχής καταχώριση μαθητή/τριας";
    }
    if (! $request->oldId){
      $student = Student::create(
        [ 'id' => $request->id,
          'eponimo' => $request->eponimo,
          'onoma' => $request->onoma,
          'patronimo' => $request->patronimo,
          'email' => $request->email
        ]
      );
      } else{
      $student = Student::find($request->oldId);
      $student->update(
        [ 'id' => $request->id,
          'eponimo' => $request->eponimo,
          'onoma' => $request->onoma,
          'patronimo' => $request->patronimo,
          'email' => $request->email
        ]
      );
 
    }

    Tmima::where('student_id', $student->id)->delete();

    foreach ($request->tmima as $tmima) {
      if ($tmima) {
        Tmima::updateOrCreate(['student_id' => $student->id, 'tmima' => $tmima], [
          'student_id' => $student->id,
          'tmima' => $tmima,
        ]);
      }
    }
    return redirect()->back()->with(['message' => $message]);
  }

  /**
   * Την καλώ από Students.vue
   * είναι το κόκκινο κουμπί με τον κάδο 
   * στον πίνακα με τουΣ μαθητές
   */
  public function delete($id)
  {
    Student::where('id', $id)->delete();
    return redirect()->back()->with(['message' => "Επιτυχής διαγραφή μαθητή/τριας"]);
  }

  /**
   * Την καλώ από Students.vue MODAL APOUSIES κουμπι ΑΠΟΘΗΚΕΥΣΗ
   */
  public function apousiesStore(Request $request)
  {
    $numOfHours = Program::getNumOfHours();
    $initApovValue = str_repeat("0", $numOfHours);
    // παίρνω τα στοιχεία των απουσιών (τιμες boolean true - false)
    $data = request()->except(['student_id', 'date']);
    $postDate = request('date');
    $date = str_replace("-", "", $postDate);
    if (!$date) $date = Carbon::now()->format('Ymd');
    $student_id = request('student_id');
    $apousies = '';
    $teachValue = '';
    foreach ($data['apou'] as $key => $value) {
      if($value == true){
        $apousies .= '1';
        if($data['teach'][$key]){
          $teachValue .= $data['teach'][$key] . '-';
        }else{
          $teachValue .= auth()->user()->id . '-';
        }
      }else{
        $apousies .= '0';
        $teachValue .= '0-';
      }
    }
    $teachValue = rtrim($teachValue, '-');
    $apovoles = '';
    foreach ($data['apov'] as $key => $value) {
      $value == true ? $apovoles .= '1' :  $apovoles .= '0';
    }
    if($apovoles == $initApovValue) $apovoles = '';

    Apousie::updateOrCreate(['student_id' => $student_id, 'date' => $date], [
      'apousies' => $apousies,
      'apovoles' => $apovoles,
      'teachers' => $teachValue
    ]);

    return redirect()->back()->with(['message' => "Επιτυχής καταχώριση απουσιών"]);
  }


  /**
   * Την καλώ από Students.vue με inertia.delete/${apousies.id}
   * είναι το κόκκινο κουμπί με τον κάδο 
   * στον υποπίνακα με τις απουσίες του μαθητή
   */
  public function apousiesDelete($id)
  {
    Apousie::where('id', $id)->delete();
    return redirect()->back()->with(['message' => "Επιτυχής διαγραφή απουσιών"]);
  }

  // Την καλώ με axios.get στο Students.vue
  // για να διαπιστώσω άν ο am είναι ελεύθερος για νέα εγγραφή 
  public function studentUnique($id)
  {
    return Student::where('id', $id)->count();
  }

}
