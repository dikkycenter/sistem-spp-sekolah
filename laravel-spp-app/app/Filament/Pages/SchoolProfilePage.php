<?php

namespace App\Filament\Pages;

use App\Models\SchoolProfile;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Halaman singleton untuk edit Profil Sekolah (data kop surat + rekening).
 */
class SchoolProfilePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Profil Sekolah';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.school-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $profile = SchoolProfile::current();
        $this->form->fill($profile->toArray());
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Sekolah')->schema([
                Forms\Components\TextInput::make('name')->label('Nama Sekolah')->required(),
                Forms\Components\TextInput::make('headmaster_name')->label('Nama Kepala Sekolah'),
                Forms\Components\Textarea::make('address')->required()->columnSpanFull(),
                Forms\Components\TextInput::make('phone')->tel()->label('No. Telp'),
                Forms\Components\TextInput::make('email')->email(),
                Forms\Components\TextInput::make('website'),
                \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('logo')
                    ->collection('logo')->image()->label('Logo Sekolah')
                    ->disk(config('filesystems.default')),
            ])->columns(2),

            Forms\Components\Section::make('Rekening Sekolah (untuk transfer SPP)')->schema([
                Forms\Components\TextInput::make('bank_name')->label('Nama Bank')
                    ->placeholder('BCA / BRI / BNI / Mandiri'),
                Forms\Components\TextInput::make('bank_account_number')->label('No. Rekening'),
                Forms\Components\TextInput::make('bank_account_name')->label('Atas Nama')
                    ->columnSpanFull(),
            ])->columns(2),
        ])->statePath('data')->model(SchoolProfile::current());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Simpan')->submit('save'),
        ];
    }

    public function save(): void
    {
        SchoolProfile::current()->update($this->form->getState());

        Notification::make()->success()->title('Profil sekolah disimpan.')->send();
    }
}
