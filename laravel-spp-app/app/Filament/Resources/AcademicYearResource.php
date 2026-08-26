<?php

namespace App\Filament\Resources;

use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Tahun Ajaran';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama T.A.')
                ->placeholder('2026/2027')->required()->unique(ignoreRecord: true),
            Forms\Components\DatePicker::make('start_date')->native(false),
            Forms\Components\DatePicker::make('end_date')->native(false),
            Forms\Components\Toggle::make('is_active')->label('Set sebagai Tahun Ajaran Aktif')
                ->helperText('Hanya 1 tahun ajaran yang bisa aktif pada satu waktu.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => AcademicYearResource\Pages\ManageAcademicYears::route('/'),
        ];
    }
}
