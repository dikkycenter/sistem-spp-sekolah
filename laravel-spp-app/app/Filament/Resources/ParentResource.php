<?php

namespace App\Filament\Resources;

use App\Models\ParentModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ParentResource extends Resource
{
    protected static ?string $model = ParentModel::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Orang Tua';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('phone')->tel()->label('No. WhatsApp'),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\TextInput::make('occupation')->label('Pekerjaan'),
            Forms\Components\Textarea::make('address')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('phone'),
            Tables\Columns\TextColumn::make('email'),
            Tables\Columns\TextColumn::make('students_count')->counts('students')->label('Jml Anak'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ParentResource\Pages\ManageParents::route('/'),
        ];
    }
}
