<?php

return [

    /**
     * Οι ρόλοι είναι 3:
     *  1   Διαχειριστής
     *  2   Καθηγητής : έχει και τμήματα και μαθήματα
     *  3   Μαθητής   : έχει μόνο μαθήματα
     * 
     * Τους είχα σε πίνακα στη ΒΔ 
     * αλλά θα τους έχω πλέον εδώ
     */

    'roles' => [
        1 => 'Διαχειριστής',
        2 => 'Καθηγητής',
        3 => 'Μαθητής'
    ],

    /**
     * Βαθμολογικές Περίοδοι
     */
    'periods' => [
        0 => '-----------',
        1 => 'Α ΤΕΤΡΑΜΗΝΟ',
        2 => 'Β ΤΕΤΡΑΜΗΝΟ',
        3 => 'ΕΞ ΙΟΥΝΙΟΥ'
    ],

    /**
     * email 
     *  αποστολή και στο email μας
     *  καταγραφή σε Log
     *  ενημέρωση για το σύνολο των απουσιών στο email
     */
    'emails' => [
        'cc' => false,
        'log' => true,
        'informForTotalApousies' => true
    ],


    /**
     * ρύθμιση αν θα γίνονται ενημερώσεις true/false
     * διαβάζει το αρχείο .env
     */
    'check_updates' => env('CHECK_UPDATES', false),

    /**
     * τα κλειδιά του πίνακα settings με τιμή boolean
     */
    'boolean_setting_keys'  => [
            'allowRegister',
            'hoursUnlocked',
            'letTeachersUnlockHours',
            'allowTeachersEditOthersApousies',
            'allowTeachersSaveAtNotActiveHour',
            'showFutureHours',
            'allowWeekends',
            'showOtherGrades',
            'allowExams',
            'allowTeachersEmail',
        ]


];
