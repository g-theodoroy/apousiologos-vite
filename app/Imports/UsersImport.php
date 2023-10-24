<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Anathesi;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Log;

class UsersImport implements OnEachRow, WithStartRow, WithValidation, SkipsOnFailure
{
  use Importable, SkipsFailures;

  protected static $user_id;
  protected static $users = 0;
  protected static $anatheseis = 0;

  public function startRow(): int
  {
    return 2;
  }

  public function onRow(Row $row)
  {
    $currentRowNumber = $row->getIndex();
    $row = $row->toArray();
    // αν υπάρχει κάποια τιμή από Επώνυμο, Όνομα, email, Password
    if (trim($row[0]) || trim($row[1]) || trim($row[2]) || trim($row[3])) {

      // αρχικοποιώ τη μεταβλητή self::$user_id κενή
      self::$user_id = null;

      // CUSTOM VALIDATION
      // επειδή δεν έχω δεδομένα user σε κάθε γραμμή ελέγχω αν δεν είναι συμπληρωμένο κάποιο από τα Επώνυμο, Όνομα, email, Password. 
      // Αν λείπει κάποιο κάνω τη μεταβλητή $allowInsertUser = false και δεν αφήνω να προχωρήσει η εισαγωγή χρήστη. Ενημερώνω για την Failure.
      // Αν δεν εισαχθεί χρήστης η μεταβλητή self::$user_id παραμένει null και όλες οι εγγραφές τμήμα-μάθημα του χρήστη δεν εισάγονται
      $allowInsertUser = true;

      if ((trim($row[0]) || trim($row[1]) || trim($row[2])) && !trim($row[3])) {
        $this->onFailure(new Failure($currentRowNumber, 'Password', ['Το πεδίο Password είναι απαραίτητο.'], $row));
        $allowInsertUser = false;
      } elseif ((trim($row[0]) || trim($row[1]) || trim($row[3])) && !trim($row[2])) {
        $this->onFailure(new Failure($currentRowNumber, 'Email', ['Το πεδίο Email είναι απαραίτητο.'], $row));
        $allowInsertUser = false;
      } elseif ((trim($row[0]) || trim($row[2]) || trim($row[3])) && !trim($row[1])) {
        $this->onFailure(new Failure($currentRowNumber, 'Όνομα', ['Το πεδίο Όνομα είναι απαραίτητο.'], $row));
        $allowInsertUser = false;
      } elseif ((trim($row[1]) || trim($row[2]) || trim($row[3])) && !trim($row[0])) {
        $this->onFailure(new Failure($currentRowNumber, 'Επώνυμο', ['Το πεδίο Επώνυμο είναι απαραίτητο.'], $row));
        $allowInsertUser = false;
      }

      // αν επιτρέπεται η εισαγωγή τότε εισάγω τον χρήστη
      if ($allowInsertUser) {
        $name = trim($row[0]) . " " . trim($row[1]);
        $user = User::updateOrCreate(['email' => trim($row[2])], [
          'name' => $name,
          'password' => Hash::make(trim($row[3])),
          'role_id' => 2,
        ]);

        // άν προχώρησε η δημιουργία του χρήστη και μετά το validation της LARAVEL
        // ενημερώνω τη μεταβλητή self::$user_id με το νέο id για να εισαχθούν
        // τα τμήματα - μάθηματα της παρούσας και των ακόλουθων γραμμών 
        if ($user->id) {
          self::$users++;
          self::$user_id = $user->id;
        }


        // αν στην ίδια γραμμή υπάρχει τμήμα ή/και μάθημα
        //ενημερώνω την ανάθεση εάν έχει γίνει εισαγωγή χρήστη
        if (trim($row[4])) {
          if (self::$user_id) {
            self::$anatheseis++;
            $anathesi = Anathesi::updateOrCreate(['tmima' => trim($row[4]), 'mathima' => trim($row[5])], [
              'tmima' => trim($row[4]),
              'mathima' => trim($row[5]),
            ]);
            // προσθέτω στο pivot την εγγραφή αναθεση <-> χρήστης
            $anathesi->users()->attach(self::$user_id);
          }
        }
      }
    } else {
      // είμαστε στις γραμμές με τα μαθήματα
      // και κενά τα στοιχεία του καθηγητή
      // αν υπάρχει τμήμα ή/και μάθημα και η μεταβλητή self::$user_id
      // ενημερώνω την ανάθεση
      if (trim($row[4])) {
        if (self::$user_id) {
          self::$anatheseis++;
          $anathesi = Anathesi::updateOrCreate(['tmima' => trim($row[4]), 'mathima' => trim($row[5])], [
            'tmima' => trim($row[4]),
            'mathima' => trim($row[5]),
          ]);
          // προσθέτω στο pivot την εγγραφή αναθεση <-> χρήστης
          $anathesi->users()->attach(self::$user_id);
        }
      }
    }
  }

  // εδώ κάνω LARAVEL validate το email
  public function rules(): array
  {
    return [
      '2' => 'nullable|email|unique:users,email',
      '*.2' => 'nullable|email|unique:users,email',
    ];
  }

  public function customValidationAttributes()
  {
    return [
      '0' => 'Επώνυμο',
      '1' => 'Όνομα',
      '2' => 'Email',
      '3' => 'Password'
    ];
  }

  public function getUsersCount()
  {
    return self::$users;
  }
  public function getAnatheseisCount()
  {
    return self::$anatheseis;
  }
}
