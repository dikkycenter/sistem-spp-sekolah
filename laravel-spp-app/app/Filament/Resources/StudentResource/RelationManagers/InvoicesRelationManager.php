<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Enums\InvoiceStatus;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';
    protected static ?string $title = 'Tagihan SPP';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('No. Invoice')->searchable(),
                Tables\Columns\TextColumn::make('month_name')->label('Bulan'),
                Tables\Columns\TextColumn::make('year')->label('Tahun'),
                Tables\Columns\TextColumn::make('total_due')->money('IDR')->label('Total'),
                Tables\Columns\TextColumn::make('total_paid')->money('IDR')->label('Dibayar'),
                Tables\Columns\TextColumn::make('remaining_balance')->money('IDR')->label('Sisa'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(collect(InvoiceStatus::cases())
                    ->mapWithKeys(fn($c) => [$c->value => $c->getLabel()])->toArray()),
            ])
            ->actions([
                Action::make('download_invoice')
                    ->label('Download Invoice')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => route('invoices.pdf', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}
