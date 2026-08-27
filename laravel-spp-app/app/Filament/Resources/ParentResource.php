<?php

namespace App\Filament\Resources;

use App\Models\ParentModel;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParentResource extends Resource
{
    protected static ?string $model = ParentModel::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Orang Tua';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            TextInput::make('phone')->tel()->label('No. WhatsApp'),
            TextInput::make('email')->email(),
            TextInput::make('occupation')->label('Pekerjaan'),
            Textarea::make('address')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('phone'),
            TextColumn::make('name')->searchable(),
            TextColumn::make('email'),
            TextColumn::make('students_count')->counts('students')->label('Jml Anak'),
        ])->actions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ParentResource\Pages\ManageParents::route('/'),
        ];
    }
}
