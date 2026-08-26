<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Exports\InvoicesExport;
use App\Filament\Resources\InvoiceResource;
use App\Services\InvoiceGeneratorService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_now')
                ->label('Generate Invoice Bulan Ini')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()?->isAdmin())
                ->action(function () {
                    $stats = app(InvoiceGeneratorService::class)
                        ->generateForMonth((int) now()->month, (int) now()->year);
                    Notification::make()
                        ->title('Selesai')
                        ->body("Dibuat: {$stats['created']} | Dilewati: {$stats['skipped']} | Error: {$stats['errors']}")
                        ->success()->send();
                }),

            // Export Excel rekap tagihan
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
                    $filename = sprintf('Rekap-Tagihan-SPP-%02d-%d.xlsx', $month, $year);
                    return Excel::download(new InvoicesExport($month, $year), $filename);
                }),
        ];
    }
}
