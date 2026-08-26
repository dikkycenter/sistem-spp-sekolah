<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Services\PaymentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Tagihan SPP';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('invoice_number')->disabled(),
            Forms\Components\TextInput::make('total_due')->money('IDR')->disabled(),
            Forms\Components\TextInput::make('total_paid')->money('IDR')->disabled(),
            Forms\Components\TextInput::make('remaining_balance')->money('IDR')->disabled(),
            Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->label('No. Invoice')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('student.name')->label('Siswa')->searchable(),
                Tables\Columns\TextColumn::make('student.nisn')->label('NISN'),
                Tables\Columns\TextColumn::make('month')
                    ->formatStateUsing(fn ($state, $record) => sprintf('%02d/%d', $state, $record->year))
                    ->label('Periode')->sortable(),
                Tables\Columns\TextColumn::make('total_due')->money('IDR'),
                Tables\Columns\TextColumn::make('total_paid')->money('IDR'),
                Tables\Columns\TextColumn::make('remaining_balance')->money('IDR')->label('Sisa'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(InvoiceStatus::cases())
                        ->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->toArray()),
                Tables\Filters\SelectFilter::make('year')
                    ->options(fn () => Invoice::query()->distinct()
                        ->pluck('year', 'year')->toArray()),
            ])
            ->actions([
                // === Aksi Bayar (Filament Action) ===
                Action::make('pay')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Invoice $r) => $r->status !== InvoiceStatus::Paid)
                    ->form([
                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Jumlah Bayar (Rp)')
                            ->required()->numeric()->minValue(1)
                            ->default(fn (Invoice $r) => $r->remaining_balance)
                            ->helperText('Boleh kurang dari total (partial). Kelebihan otomatis dialokasi ke invoice tertua lain via FIFO.'),
                        Forms\Components\Select::make('method')
                            ->label('Metode')
                            ->options(collect(PaymentMethod::cases())
                                ->mapWithKeys(fn ($m) => [$m->value => $m->getLabel()])->toArray())
                            ->required()->reactive(),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Bayar')->default(now())->required()->native(false),
                        Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2),
                        // Bukti bayar WAJIB kalau transfer (Case 4)
                        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('proof')
                            ->label('Bukti Pembayaran')
                            ->collection('proof')
                            ->disk(config('filesystems.default'))
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'application/pdf'])
                            ->required(fn (Forms\Get $get) => $get('method') === PaymentMethod::Transfer->value)
                            ->visible(fn (Forms\Get $get) => $get('method') === PaymentMethod::Transfer->value)
                            ->helperText('Wajib untuk metode Transfer.'),
                    ])
                    ->action(function (array $data, Invoice $record) {
                        /** @var PaymentService $service */
                        $service = app(PaymentService::class);

                        $payment = $service->recordPayment([
                            'student_id' => $record->student_id,
                            'amount_paid' => $data['amount_paid'],
                            'method' => $data['method'],
                            'payment_date' => $data['payment_date'],
                            'notes' => $data['notes'] ?? null,
                        ]);

                        // Upload bukti (jika ada) - Filament menangani otomatis via SpatieMediaLibraryFileUpload
                        // yang ter-attach ke payment via ->model($payment), tapi karena form dari record invoice,
                        // kita attach manual:
                        if (! empty($data['proof'])) {
                            foreach ((array) $data['proof'] as $file) {
                                $payment->addMedia(storage_path('app/livewire-tmp/'.basename($file)))
                                    ->toMediaCollection('proof');
                            }
                        }

                        Notification::make()
                            ->title('Pembayaran berhasil dicatat')
                            ->body("Kwitansi: {$payment->receipt_number}")
                            ->success()->send();
                    }),

                Action::make('download_invoice')
                    ->label('Invoice PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $r) => route('invoices.pdf', $r))
                    ->openUrlInNewTab(),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\InvoiceResource\Pages\ListInvoices::route('/'),
        ];
    }
}
