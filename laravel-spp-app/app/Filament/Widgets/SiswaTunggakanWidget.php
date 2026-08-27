<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Student;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tabel siswa dengan tunggakan tertinggi di dashboard.
 */
class SiswaTunggakanWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Siswa dengan Tunggakan Tertinggi';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->select('students.*')
                    ->selectRaw('COALESCE(SUM(invoices.remaining_balance), 0) as total_arrears')
                    ->selectRaw('COUNT(invoices.id) filter (where invoices.status != \'paid\') as unpaid_count')
                    ->leftJoin('invoices', function ($join) {
                        $join->on('invoices.student_id', '=', 'students.id')
                            ->whereIn('invoices.status', [
                                InvoiceStatus::Unpaid->value,
                                InvoiceStatus::Partial->value,
                            ]);
                    })
                    ->groupBy('students.id')
                    ->havingRaw('COALESCE(SUM(invoices.remaining_balance), 0) > 0')
                    ->orderByDesc('total_arrears')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nisn'),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('parent.phone')->label('WhatsApp'),
                Tables\Columns\TextColumn::make('unpaid_count')
                    ->label('Invoice Belum Lunas')->badge()->color('warning'),
                Tables\Columns\TextColumn::make('total_arrears')
                    ->label('Total Tunggakan')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->color('danger')
                    ->weight('bold'),
            ])
            ->paginated(false);
    }
}
