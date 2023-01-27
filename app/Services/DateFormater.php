<?php

namespace App\Services;

use Carbon\Carbon;


class DateFormater {


    private $date;


    /**
     * Ελέγχω το format ( ηη/μμ/εεεε - εεεε-μμ-ηη - εεεεμμηη)
     * 
     * και φτιάχνω την ημνια με createFromFormat από το ανάλογο format
     * 
     * Αν δεν ταιριάζει παίρνω το now()
     * 
     *  */
    public function __construct($date = '')
    {
        if (preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $date)) {

            $this->date = Carbon::createFromFormat('d/m/Y', $date);

        }elseif (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $date)) {

            $this->date = Carbon::createFromFormat('Y-m-d', $date);

        }elseif (preg_match("/[0-9]{8}/", $date)){

            $this->date = Carbon::createFromFormat('Ymd', $date);

        }else{

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