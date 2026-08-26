<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Exports\PaymentsExport;
use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('month')->label('Bulan')
                        ->options(array_combine(range(1, 12), [
                            'Januari','Februari','Maret','April','Mei','Juni',
                            'Juli','Agustus','September','Oktober','November','Desember',
                        ]))
                        ->default((int) now()->month),
                    Forms\Components\TextInput::make('year')->label('Tahun')
                        ->numeric()->default((int) now()->year),
                ])
                ->action(function (array $data) {
                    $month = (int) $data['month'];
                    $year = (int) $data['year'];
                    $filename = sprintf('Rekap-Pembayaran-%02d-%d.xlsx', $month, $year);
                    return Excel::download(new PaymentsExport($month, $year), $filename);
                }),
        ];
    }
}
