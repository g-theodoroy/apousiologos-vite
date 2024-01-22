<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Anathesi;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NoGradesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    private $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:C1')->getFont()->setSize(12)->setBold(true);
                $event->sheet->getDelegate()->getStyle('A1:C1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
                $event->sheet->getDefaultRowDimension()->setRowHeight(20);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'ΚΑΘΗΓΗΤΗΣ',
            'ΤΜΗΜΑ',
            'ΜΑΘΗΜΑ'
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {

        $activeGradePeriod = Setting::getValueOf('activeGradePeriod');
        $insertedAnatheseis = Grade::where('period_id', $activeGradePeriod)->distinct('anathesi_id')->pluck('anathesi_id');
        if( $this->status){
            // αν έρχεται η μεταβλητή $this->status = 1 τότε βρίσκω τις καταχωρισμένες καταστάσεις
            $anatheseis = Anathesi::whereIn('id', $insertedAnatheseis);
        }else{
            // αν δεν έρχεται η μεταβλητή $this->status = 0 τότε βρίσκω τις υπολοιπόμενες καταστάσεις
            $anatheseis = Anathesi::whereNotIn('id', $insertedAnatheseis);
        }
        if(auth()->user()->permissions['teacher']){
            $anatheseis = $anatheseis->whereHas('users', function ($query) {
                $query->where('id', auth()->user()->id);
            });
        }
        $anatheseis = $anatheseis->with('users:id,name')->get()->toArray();
        
        $data = [];
        foreach ($anatheseis as $not) {
          $data[] = [  $not['users'][0]['name'], $not['tmima'],  $not['mathima']];
        }
        
        array_multisort(
            array_column($data, 0),
            SORT_ASC,
            array_column($data, 1),
            SORT_ASC,
            array_column($data, 2),
            SORT_ASC,
            $data
        );
        
        return collect($data);    
    }
}
