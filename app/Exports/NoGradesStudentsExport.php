<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Anathesi;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NoGradesStudentsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:F1')->getFont()->setSize(12)->setBold(true);
                $event->sheet->getDelegate()->getStyle('A1:F1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE0E0E0');
                $event->sheet->getDefaultRowDimension()->setRowHeight(20);
            },
        ];
    }

    public function headings(): array
    {
        return [
            'ΑΜ',
            'ΕΠΩΝΥΜΟ',
            'ΟΝΟΜΑ',
            'ΤΜΗΜΑ',
            'ΜΑΘΗΜΑ',
            'ΚΑΘΗΓΗΤΗΣ',
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $activeGradePeriod = Setting::getValueOf('activeGradePeriod');
        $students = Student::with('tmimata:student_id,tmima')->get(['id', 'eponimo', 'onoma']);
        $data = [];
        foreach ($students as $stu) {
            foreach ($stu->tmimata as $tmima) {
                $anatheseis = Anathesi::whereTmima($tmima['tmima']);
                if(auth()->user()->permissions['teacher']){
                    $anatheseis = $anatheseis->whereHas('users', function ($query) {
                        $query->where('id', auth()->user()->id);
                    });
                }
                $anatheseis = $anatheseis->with('users:id,name')->orderby('mathima')->get();
                foreach ($anatheseis as $anathesi) {
                    $gradeExists = Grade::where('anathesi_id', $anathesi->id)->where('student_id', $stu['id'])->where('period_id', $activeGradePeriod)->count();
                    if (!$gradeExists) {
                        $data[] = [$stu->id, $stu->eponimo, $stu->onoma, $tmima->tmima, $anathesi->mathima, $anathesi->users()->first()->name];
                    }
                }
            }
        }
        array_multisort(
            array_column($data, 3),
            SORT_ASC,
            array_column($data, 4),
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
