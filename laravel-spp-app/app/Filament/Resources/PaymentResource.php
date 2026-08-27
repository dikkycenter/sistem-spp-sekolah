<?php

namespace App\Filament\Resources;

use App\Enums\PaymentMethod;
use App\Models\Payment;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pembayaran';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('receipt_number')->disabled(),
            Forms\Components\TextInput::make('amount_paid')->money('IDR')->disabled(),
            Forms\Components\TextInput::make('method')->disabled(),
            Forms\Components\DatePicker::make('payment_date')->disabled(),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull()->disabled(),
            \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('proof')
                ->collection('proof')->label('Bukti Bayar')->disk(config('filesystems.default')),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')->label('No. Kwitansi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.name')->label('Siswa')->searchable(),
                Tables\Columns\TextColumn::make('payment_date')->date()->label('Tanggal'),
                Tables\Columns\TextColumn::make('amount_paid')->money('IDR')->label('Nominal'),
                Tables\Columns\TextColumn::make('method')->badge(),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('proof')
                    ->collection('proof')->label('Bukti')->square(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->options(collect(PaymentMethod::cases())
                        ->mapWithKeys(fn($m) => [$m->value => $m->getLabel()])->toArray()),
            ])
            ->actions([
                Tables\Actions\Action::make('download_receipt')
                    ->label('Kwitansi PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(Payment $r) => route('payments.pdf', $r))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PaymentResource\Pages\ListPayments::route('/'),
        ];
    }
}
