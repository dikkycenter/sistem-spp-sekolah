<?php

namespace App\Filament\Pages;

use App\Models\SchoolProfile;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Halaman singleton untuk edit Profil Sekolah (data kop surat + rekening).
 */
class SchoolProfilePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Profil Sekolah';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.school-profile';
    public ?array $data = [];

    public function mount(): void
    {
        $profile = SchoolProfile::current();
        $this->form->fill($profile->toArray());
    }

    public function fform(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas Sekolah')->schema([
                TextInput::make('name')->label('Nama Sekolah')->required(),
                TextInput::make('headmaster_name')->label('Nama Kepala Sekolah'),
                Textarea::make('address')->required()->columnSpanFull(),
                TextInput::make('phone')->tel()->label('No. Telp'),
                TextInput::make('email')->email(),
                TextInput::make('website'),
                SpatieMediaLibraryFileUpload::make('logo')
                    ->collection('logo')->image()->label('Logo Sekolah')
                    ->disk(config('filesystems.default')),
            ])->columns(2),

            Section::make('Rekening Sekolah (untuk transfer SPP)')->schema([
                TextInput::make('bank_name')->label('Nama Bank')
                    ->placeholder('BCA / BRI / BNI / Mandiri'),
                TextInput::make('bank_account_number')->label('No. Rekening'),
                TextInput::make('bank_account_name')->label('Atas Nama')
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
