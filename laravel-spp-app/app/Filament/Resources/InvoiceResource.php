<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Invoice;
use App\Services\PaymentService;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Tagihan SPP';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('invoice_number')->disabled(),
            TextInput::make('total_due')->money('IDR')->disabled(),
            TextInput::make('total_paid')->money('IDR')->disabled(),
            TextInput::make('remaining_balance')->money('IDR')->disabled(),
            Textarea::make('notes')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('invoice_number')->label('No. Invoice')
                    ->searchable()->sortable(),
                TextColumn::make('student.name')->label('Siswa')->searchable(),
                TextColumn::make('student.nisn')->label('NISN'),
                TextColumn::make('month')
                    ->formatStateUsing(fn($state, $record) => sprintf('%02d/%d', $state, $record->year))
                    ->label('Periode')->sortable(),
                TextColumn::make('total_due')->money('IDR'),
                TextColumn::make('total_paid')->money('IDR'),
                TextColumn::make('remaining_balance')->money('IDR')->label('Sisa'),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(InvoiceStatus::cases())
                        ->mapWithKeys(fn($c) => [$c->value => $c->getLabel()])->toArray()),
                SelectFilter::make('year')
                    ->options(fn() => Invoice::query()->distinct()
                        ->pluck('year', 'year')->toArray()),
            ])
            ->actions([
                // === Aksi Bayar (Filament Action) ===
                Action::make('pay')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn(Invoice $r) => $r->status !== InvoiceStatus::Paid)
                    ->form([
                        TextInput::make('amount_paid')
                            ->label('Jumlah Bayar (Rp)')
                            ->required()->numeric()->minValue(1)
                            ->default(fn(Invoice $r) => $r->remaining_balance)
                            ->helperText('Boleh kurang dari total (partial). Kelebihan otomatis dialokasi ke invoice tertua lain via FIFO.'),
                        Select::make('method')
                            ->label('Metode')
                            ->options(collect(PaymentMethod::cases())
                                ->mapWithKeys(fn($m) => [$m->value => $m->getLabel()])->toArray())
                            ->required()->reactive(),
                        DatePicker::make('payment_date')
                            ->label('Tanggal Bayar')->default(now())->required()->native(false),
                        Textarea::make('notes')->label('Catatan')->rows(2),
                        // Bukti bayar WAJIB kalau transfer (Case 4)
                        SpatieMediaLibraryFileUpload::make('proof')
                            ->label('Bukti Pembayaran')
                            ->collection('proof')
                            ->disk(config('filesystems.default'))
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'application/pdf'])
                            ->required(fn(Get $get) => $get('method') === PaymentMethod::Transfer->value)
                            ->visible(fn(Get $get) => $get('method') === PaymentMethod::Transfer->value)
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
                                $payment->addMedia(storage_path('app/livewire-tmp/' . basename($file)))
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
                    ->url(fn(Invoice $r) => route('invoices.pdf', $r))
                    ->openUrlInNewTab(),

                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\InvoiceResource\Pages\ListInvoices::route('/'),
        ];
    }
}
