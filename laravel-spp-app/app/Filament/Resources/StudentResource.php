<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Siswa';
    protected static ?string $modelLabel = 'Siswa';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Siswa')->schema([
                Forms\Components\TextInput::make('nisn')
                    ->label('NISN')->required()->unique(ignoreRecord: true)->maxLength(20),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')->required()->maxLength(150),
                Forms\Components\Select::make('gender')->label('Jenis Kelamin')
                    ->options(['L' => 'Laki-laki', 'P' => 'Perempuan'])->required(),
                Forms\Components\TextInput::make('birth_place')->label('Tempat Lahir'),
                Forms\Components\DatePicker::make('birth_date')->label('Tanggal Lahir')
                    ->native(false),
                Forms\Components\Textarea::make('address')->label('Alamat')->rows(2),
                Forms\Components\Select::make('parent_id')->label('Orang Tua')
                    ->relationship('parent', 'name')->searchable()->preload()->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('phone')->tel(),
                        Forms\Components\Textarea::make('address'),
                    ]),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nisn')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('gender')->label('JK')->badge(),
                Tables\Columns\TextColumn::make('parent.name')->label('Orang Tua')->searchable(),
                Tables\Columns\TextColumn::make('parent.phone')->label('WhatsApp'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EnrollmentsRelationManager::class,
            RelationManagers\DiscountsRelationManager::class,
            RelationManagers\InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
