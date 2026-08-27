<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';
    protected static ?string $title = 'Diskon / Beasiswa';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('name')->label('Nama')->required(),
            Forms\Components\TextInput::make('amount')->label('Potongan (Rp)')
                ->required()->numeric()->prefix('Rp')->minValue(0),
            Forms\Components\Select::make('start_month')->label('Bulan Mulai')
                ->options(array_combine(range(1, 12), range(1, 12)))->required(),
            Forms\Components\TextInput::make('start_year')->label('Tahun Mulai')
                ->numeric()->required(),
            Forms\Components\Select::make('end_month')->label('Bulan Akhir')
                ->options(array_combine(range(1, 12), range(1, 12)))->required(),
            Forms\Components\TextInput::make('end_year')->label('Tahun Akhir')
                ->numeric()->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('amount')->money('IDR'),
            Tables\Columns\TextColumn::make('start_month')
                ->formatStateUsing(fn($record) => sprintf('%02d/%d', $record->start_month, $record->start_year))
                ->label('Mulai'),
            Tables\Columns\TextColumn::make('end_month')
                ->formatStateUsing(fn($record) => sprintf('%02d/%d', $record->end_month, $record->end_year))
                ->label('Akhir'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }
}
