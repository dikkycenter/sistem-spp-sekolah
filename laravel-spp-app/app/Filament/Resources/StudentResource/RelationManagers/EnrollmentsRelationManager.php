<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Models\AcademicYear;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';
    protected static ?string $title = 'Riwayat Kelas & SPP';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('academic_year_id')
                ->label('Tahun Ajaran')
                ->options(AcademicYear::pluck('name', 'id'))
                ->required(),
            Forms\Components\TextInput::make('grade')->label('Tingkat')->required()
                ->helperText('Contoh: 1 (SD), 7 (SMP), 10 (SMA)'),
            Forms\Components\TextInput::make('class_name')->label('Kelas')->required()
                ->helperText('Contoh: 1A, 7B, 10 IPA 1'),
            Forms\Components\TextInput::make('base_spp_amount')->label('SPP Bulanan (Rp)')
                ->required()->numeric()->prefix('Rp')->minValue(0),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('class_name')
            ->columns([
                Tables\Columns\TextColumn::make('academicYear.name')->label('T.A.'),
                Tables\Columns\TextColumn::make('grade')->label('Tingkat'),
                Tables\Columns\TextColumn::make('class_name')->label('Kelas'),
                Tables\Columns\TextColumn::make('base_spp_amount')
                    ->label('SPP/Bulan')->money('IDR'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
