<?php

namespace App\Http\Controllers;

use Session;
use Throwable;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Tmima;
use App\Models\Apousie;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Anathesi;
use App\Mail\ApousiesMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ApousiologosController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function index($selectedTmima = '0', $postDate = null)
  {
    // μεταβλητές
    $isAdmin = Auth::user()->role_id == 1;
    $settings = Setting::getValues();
    // πόσες μέρες μπορούν να πανε πίσω στην Ημνια οι μη Διαχειριστές
    $pastDaysInsertApousies = $settings['pastDaysInsertApousies'] ? true : false;
    $initApouArray = array();
    $numOfHours = Program::getNumOfHours();
    for ($i = 1; $i <= $numOfHours; $i++) {
      $initApouArray[$i] = false;
    }

    if ($isAdmin) {
      if (strpos(request()->headers->get('referer'), 'login')) {
        $this->chkForUpdates();
      } else {
        Session::forget('notification');
      }
    }

    // αρχικοποιώ την ημέρα αν δεν έχει έρθει με το url

    if (!$postDate) {
      $date = Carbon::now()->format("Ymd");
      $postDate = Carbon::now()->format("Y-m-d");
    } else {
      $date = str_replace("-", "", $postDate);
    }
    // αν έχει οριστεί συγκεκριμμένη ημέρα από τον Διαχειριστή
    $setCustomDate = $settings['setCustomDate'];
    // αν ο χρήστης δεν είναι Διαχειριστής
    if (!$isAdmin) {
      if ($setCustomDate) {
        // ή η συγκεκριμμένη ημέρα
        try {
          $date = Carbon::createFromFormat("!d/m/Y", $setCustomDate)->format("Ymd");
        } catch (\Exception $e) {
          $date = Carbon::parse($setCustomDate)->format("Ymd");
        }
        $postDate = Carbon::createFromFormat("Ymd", $date)->format("Y-m-d");
      }
    }

    // παίρνω τα τμηματα του χρήστη
    // ταξινόμηση με το μήκος του ονόματος + αλφαβητικά
    $anatheseis = Auth::user()->anatheseis->sortBy('LENGTH(tmima)')->sortBy('tmima')->pluck('tmima')->unique();

    // αν είναι Διαχειριστής τα παίρνω όλα από μια φορά
    if ($isAdmin) {
      $anatheseis = Anathesi::orderByRaw('LENGTH(tmima)')->orderby('tmima')->pluck('tmima')->unique();
    }

    // αν το τμήμα που δόθηκε στο url δεν αντιστοιχεί στον χρήστη επιστρέφω πίσω
    if ($selectedTmima && !$anatheseis->contains($selectedTmima)) return back();

    // βάζω σε πίνακα [ΑΜ]=απουσίες για την ημέρα
    $apousiesForDate = Apousie::where('date', $date)->pluck('apousies', 'student_id')->toArray();

    if ($selectedTmima) {
      // βάζω σε ένα πίνακα τους ΑΜ των μαθητών που ανήκουν στο επιλεγμένο τμήμα
      $student_ids = Tmima::where('tmima', $selectedTmima)->pluck('student_id')->toArray();

      // παίρνω τα στοιχεία των μαθητών ταξινομημένα κσι φιλτράρω μόνο τους ΑΜ που έχει το τμήμα
      $students = Student::select('id', 'eponimo', 'onoma', 'email')->orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata:student_id,tmima')->get()->only($student_ids);
    } else { // δεν είναι επιλεγμένο τμήμα = όλοι όσοι έχουν απουσίες
      // βρίσκω τους μαθητές που έχουν απουσίες την συγκεκριμμένη ημέρα
      $students = Student::select('id', 'eponimo', 'onoma', 'email')
        ->whereHas('apousies', function ($query) use ($date) {
          $query->where('date', '=', $date);
        })->orderby('eponimo')->orderby('onoma')->orderby('patronimo')->with('tmimata:student_id,tmima')->get('id', 'eponimo', 'onoma', 'patronimo');
    }

    // φτιάχνω πίνακα με τα στοιχεία που θα εμφανίσω
    $arrStudents = array();
    $arrSendEmail = array();
    $arrApousies = array();
    $arrApousies['date'] = Carbon::createFromFormat("!Ymd", $date)->format("Y-m-d");
    foreach ($students as $stuApFoD) {
      $tmimata = $stuApFoD->tmimata->sortBy(function ($string) {
        return strlen($string);
      })->pluck('tmima');
      $arrStudents[] = [
        'id' => $stuApFoD->id,
        'eponimo' => $stuApFoD->eponimo,
        'onoma' => $stuApFoD->onoma,
        //'patronimo' => $stuApFoD->patronimo,
        'email' => $stuApFoD->email,
        // παίρνω το πρώτο τμήμα με το λιγότερο μήκος σαν βασικό τμήμα
        // υποθέτοντας ότι συνήθως τα τμήματα γενικής τα γράφουμε σύντομα πχ Α1 αντί Α1-ΑΓΓΛΙΚΑ
        'tmima' => $tmimata[0],
        // παίρνω όλα τα τμήματα και φτιάχνω string χωρισμένο με κόμμα (,)
        'tmimata' => $tmimata->implode(', '),
        // αν υπάρχουν απουσίες για την συγκεκριμμένη ημέρα  για το μαθητή. Μορφή: '1111000' 
        'apousies' => array_sum(preg_split('//', $apousiesForDate[$stuApFoD->id] ?? '')) > 0 ? array_sum(preg_split('//', $apousiesForDate[$stuApFoD->id] ?? '')) : null
      ];
      $arrSendEmail[$stuApFoD->id]= false;
      if ($apousiesForDate[$stuApFoD->id] ?? false) {
        $arrApou = array();
        $num = 1;
        foreach (str_split($apousiesForDate[$stuApFoD->id]) as $value) {
          $value == '1' ? $arrApou[$num] = true : $arrApou[$num] = false;
          $num++;
        }
        $arrApousies[$stuApFoD->id] = $arrApou;
      } else {
        $arrApousies[$stuApFoD->id] = $initApouArray;
      }
    }
    // ταξινόμηση πίνακα
    if ($selectedTmima) {
      usort($arrStudents, function ($a, $b) {
        return $a['eponimo'] <=> $b['eponimo'] ?:
          strnatcasecmp($a['onoma'], $b['onoma']);
      });
    } else {
      usort($arrStudents, function ($a, $b) {
        return $a['tmima'] <=> $b['tmima'] ?:
          $a['eponimo'] <=> $b['eponimo'] ?:
          strnatcasecmp($a['onoma'], $b['onoma']);
      });
    }
    //διαβάζω ρυθμίσεις από τον πίνακα configs
    $program = new Program;
    // οι ώρες του προγράμματος
    $totalHours = $program->getNumOfHours();
    // η ζώνη ώρας
    $timeZone = $settings['timeZone'];
    // βρίσκω την ενεργή ώρα για πέρασμα απουσιών
    // αν η ημέρα είναι διαφορετική από σήμερα δεν έχω ενεργή ώρα ( = 0 )
    $activeHour = $program->getActiveHour(Carbon::Now($timeZone)->format("Hi"));
    if ($date !== Carbon::Now($timeZone)->format("Ymd")) $activeHour = 0;
    // αν είναι ΣΚ 
    $isWeekend = intval(Carbon::createFromFormat("!Ymd", $date)->isWeekend());
    // επιτρέπεται η καταχώριση το ΣΚ
    $allowWeekends = intval($settings['allowWeekends']);
    // αν θέλουμε τις ώρες ξεκλείδωτες ή είμαστε Διαχειριστής
    $hoursUnlocked = intval($settings['hoursUnlocked']) ?? 0;
    if ($isAdmin) $hoursUnlocked = 1;
    // επιτρέπεται στους καθηγητές να ξεκλειδώσουν τις ώρες;
    $letTeachersUnlockHours = intval($settings['letTeachersUnlockHours']);
    // να φαίνονται ή όχι οι επόμενες ώρες
    $showFutureHours = intval($settings['showFutureHours']);
    // παίρνω την ημέρα και αλλάζω το format της ημνιας από εεεεμμηη σε εεεε-μμ-ηη
    $date = Carbon::createFromFormat("!Ymd", $date)->format("Y-m-d");
    // αν έχει οριστεί συγκεκριμμένη ημέρα
    // ξεκλειδώνω τις ώρες
    if ($setCustomDate) {
      $hoursUnlocked = 1;
    }

    $allowTeachersSaveAtNotActiveHour = intval($settings['allowTeachersSaveAtNotActiveHour']);
    $allowTeachersEmail = intval($settings['allowTeachersEmail'] ?? false);

    // Οι μαθητές δεν μπορούν  ακόμη και αν επιτρέπεται στους καθηγητές
    // να έχουν ξεκλείδωτες τις ώρες
    // να ξεκλειδώσουν τις ώρες
    // να εισάγουν απουσίες ΣαββατοΚύριακο
    // να επιλέξουν άλλη ημέρα
    // να αποθηκεύουν εκτός ωραρίου
    if (Auth::user()->role_id == 3) {
      $hoursUnlocked = 0;
      $letTeachersUnlockHours = 0;
      $allowWeekends = 0;
      $pastDaysInsertApousies = false;
      $allowTeachersSaveAtNotActiveHour = 0;
      $allowTeachersEmail = 0;
    }

    //$activeHour = 5;
    //$letTeachersUnlockHours = 1;
    //$showFutureHours = 0;
    //$hoursUnlocked = 0;
    //$isWeekend =1;

    return Inertia::render('Apousiologos', [
      'date' => $postDate,
      'anatheseis' => $anatheseis,
      'selectedTmima' => $selectedTmima,
      'totalHours' => $totalHours,
      'activeHour' => $activeHour,
      'hoursUnlocked' => $hoursUnlocked,
      'letTeachersUnlockHours' => $letTeachersUnlockHours,
      'showFutureHours' => $showFutureHours,
      'arrStudents' => $arrStudents,
      'arrSendEmail' => $arrSendEmail,
      'arrApousies' => $arrApousies,
      'setCustomDate' => $setCustomDate,
      'allowTeachersSaveAtNotActiveHour' => $allowTeachersSaveAtNotActiveHour,
      'allowTeachersEmail' => $allowTeachersEmail,
      'isWeekend' => $isWeekend,
      'allowWeekends' => $allowWeekends,
      'allowPastDays' => $pastDaysInsertApousies,
      'setCustomDate' => $setCustomDate
    ]);
  }

  public function store($selectedTmima = '0', $date = null)
  {

    // παίρνω τα στοιχεία των απουσιών (τιμες boolean true - false)
    $data = request()->except(['date']);
    $postDate = request('date');
    $date = str_replace("-", "", $postDate);

    $initApouValue = str_repeat("0", Program::getNumOfHours());
    // αρχικοποιώ την ημέρα αν δεν έχει έρθει με το url
    if (!$date) $date = Carbon::now()->format("Ymd");

    // φτιάχνω την τιμή για αποθήκευση '1100100'
    foreach ($data as $key => $arrValue) {
      $value = '';
      foreach ($arrValue as $num => $val) {
        $val == true ? $value .= '1' :  $value .= '0';
      }
      // αν δεν υπάρχουν απουσίες '0000000' δεν θα εισάγω τιμές
      if ($value == $initApouValue) $value = '';

      // αν δεν είναι κενό ενημερώνω αν υπάρχει ΑΜ+ημνια ή πρόσθέτω
      if ($value) {
        Apousie::updateOrCreate(['student_id' => $key, 'date' => $date], [
          'apousies' => $value,
        ]);
      } else {
        // αν κενό διαγράφω αν υπάρχει ΑΜ+ημνια
        Apousie::where('student_id', $key)->where('date', $date)->delete();
      }
    }
    $dateShow = Carbon::createFromFormat("Y-m-d", $postDate)->format("d/m/Y");
    return redirect("/apousiologos/$selectedTmima/$postDate")->with(['message' => ['saveSuccess' => "Kαταχώριση απουσιών για τις $dateShow επιτυχής."]]);
  }

  public function sendEmailToParent($am, $date, $tmima = null)
  {
    $stuToSendEmail = request()->get('st') ? explode( ',', request()->get('st')) : [];
    $date = str_replace("-", "", $date);
    $today = $date == Carbon::now()->format('Ymd');
    $dateShow = Carbon::createFromFormat('Ymd', $date)->format('d/m/Y');
    if ($am == 'all') {
      if ($tmima) {
        $students = Student::whereHas('tmimata', function ($query) use ($tmima) {
          $query->where('tmima', '=', $tmima);
        })->whereHas('apousies', function ($query) use ($date) {
          $query->where('date', '=', $date);
        })->with('tmimata')->with('apousies')->get();
      } else {
        $students = Student::whereHas('apousies', function ($query) use ($date) {
          $query->where('date', '=', $date);
        })->with('tmimata')->with('apousies')->get();
      }
    } else {
      $students = Student::where('id', $am)->with('tmimata')->with('apousies')->get();
    }
    $emailData = array();
    foreach ($students as $student) {
      if(count($stuToSendEmail) && !in_array($student->id, $stuToSendEmail)) continue;
      $apousiesForDate = $student->apousies->where('date', $date)->pluck('apousies', 'date');
      $sumapp = array_sum(preg_split('//', $apousiesForDate[$date] ?? '')) > 0 ? array_sum(preg_split('//', $apousiesForDate[$date] ?? '')) : null;
      if (!$sumapp) continue;
      $num = 1;
      $hoursStr = '';
      foreach (str_split($apousiesForDate[$date]) as $value) {
        $value == '1' ?  $hoursStr .= $num . "η " : $hoursStr .= "__  ";
        $num++;
      }
      $hoursStr = trim($hoursStr, ", ");
      if (!$student->email) continue;
      $emailData[] = [
        'email' => $student->email,
        'date' => $dateShow,
        'today' => $today,
        'name' => $student->eponimo . " " .  $student->onoma,
        'patronimo' => $student->patronimo,
        'tmima' => $student->tmimata->sortBy(function ($string) {
          return strlen($string);
        })->pluck('tmima')[0],
        'sum' => $sumapp,
        'hours' => $hoursStr
      ];
    }
    $message = "Επιτυχής αποστολή email σε ";
    foreach ($emailData as $data) {
      //return new ApousiesMail($data);
      try {
        $mail = Mail::to($data["email"]);
        // αν έχει ρυθμιστεί σε true αποστολή email στην ηλ.διέυθυνσή μας
        if(config('gth.emails.cc')) $mail = $mail->cc(env('MAIL_FROM_ADDRESS'));
        $mail->send(new ApousiesMail($data));

        $message .= $data['name'] . ", ";

        // αν έχει ρυθμιστεί σε true καταγραφή των στοιχείων των απεσταλμένων email
        if(config('gth.emails.log')) Log::channel('emailSent')->info(implode(', ',$data));
      } catch (Throwable $e) {
        return redirect()->back()->with(['message' => ['saveError' => 'Ελέγξτε τις ρυθμίσεις αποστολής email.']]);
      }
    }
    $message = trim($message, ", ") . '.';
    return redirect()->back()->with(['message' => ['saveSuccess' => $message]]);
  }

  private function chkForUpdates()
  {
    try {
      // έλεγχος εάν έχουν γίνει αλλαγές στο github
      $url = 'https://api.github.com/repos/g-theodoroy/apousiologos-examsplanner-bathmologia/commits';
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
      if (Auth::user()->role_id == 1) {
        $message = 'Έγιναν τροποποιήσεις στον κώδικα του Ηλ.Απουσιολόγου στο Github.<br><br>Αν επιθυμείτε <a href=\"https://github.com/g-theodoroy/apousiologos-examsplanner-bathmologia/commits/\" target=\"_blank\"><u> εξετάστε τον κώδικα</u></a> και ενημερώστε την εγκατάστασή σας.<br><br>Για να μην εμφανίζεται το παρόν μήνυμα καντε κλικ στο κουμπί Ενημερώθηκε.';
        // διαβάζω από το αρχείο .updateCheck το id του τελευταίου αποθηκευμένου commit
        $file = storage_path('app/.updateCheck');
        if (file_exists($file)) {
          // αν διαφέρει με το id του τελευταίου commit στο github
          // στέλνω ειδοποίηση για την υπάρχουσα ενημέρωση
          if ($commits[0]['sha'] != file_get_contents($file)) {
            $notification = array(
              'message' =>  $message,
            );
            session()->flash('notification', $notification);
          }
        } else {
          // αν δεν υπάρχει το αρχείο .updateCheck το
          // δημιουργώ και γράφω το id του τελευταίου commit
          file_put_contents($file, $commits[0]['sha']);
        }
      }
    }
  }
}
