<?php

namespace App\Services;

use Carbon\Carbon;


class DateFormater {


    private $date;

    private static $DB_FORMAT = 'Ymd';
    private static $SHOW_FORMAT = 'd/m/Y';
    private static $DATEPICKER_FORMAT = 'Y-m-d';
     private static $DB_REGEX = "/[0-9]{8}/";
    private static $SHOW_REGEX = "/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/";
    private static $DATEPICKER_REGEX = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
    


    public function __construct($date = '')
    {

        $this->createDate($date);

    }


    /**
     * Ελέγχω το format ( ηη/μμ/εεεε - εεεε-μμ-ηη - εεεεμμηη)
     * 
     * και φτιάχνω την ημνια με createFromFormat από το ανάλογο format
     * 
     * Αν δεν ταιριάζει παίρνω το now()
     * 
     *  */
    private function createDate($date = ''){
        if (preg_match(self::$DB_REGEX, $date)) {

            $this->date = Carbon::createFromFormat(self::$DB_FORMAT, $date);
        } elseif (preg_match(self::$SHOW_REGEX, $date)) {

            $this->date = Carbon::createFromFormat(self::$SHOW_FORMAT, $date);
        } elseif (preg_match(self::$DATEPICKER_REGEX, $date)) {

            $this->date = Carbon::createFromFormat(self::$DATEPICKER_FORMAT, $date);
        } else {

            $this->date = Carbon::now();
        }
    }

    
    public function toDB(){
        return $this->date->format('Ymd');
    }

    public function toShow()
    {
        return $this->date->format('d/m/Y');
    }

    public function toDatePicker()
    {
        return $this->date->format('Y-m-d');
    }

    public function getDate()
    {
        return $this->date;
    }

}