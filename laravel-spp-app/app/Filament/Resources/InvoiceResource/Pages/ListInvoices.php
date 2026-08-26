<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\InvoiceGeneratorService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

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
                ->action(function () {
                    $stats = app(InvoiceGeneratorService::class)
                        ->generateForMonth((int) now()->month, (int) now()->year);
                    Notification::make()
                        ->title('Selesai')
                        ->body("Dibuat: {$stats['created']} | Dilewati: {$stats['skipped']} | Error: {$stats['errors']}")
                        ->success()->send();
                }),
        ];
    }
}
