<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminCreditXlsxExportController extends Controller
{

    public function __invoke()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $credits = $this->buildCreditRows();
        $installments = $this->buildInstallmentRows();
        $settlements = $this->buildSettlementRows($credits);

        $this->buildSummarySheet($spreadsheet->getActiveSheet(), $credits, $installments, $settlements);
        $this->buildInstallmentSheet($spreadsheet->createSheet(), $installments);
        $this->buildSettlementSheet($spreadsheet->createSheet(), $settlements);

        $tempPath = tempnam(sys_get_temp_dir(), 'utang_saya_');
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($tempPath);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $filename = 'utang-saya-' . Carbon::now()->format('Ymd-His') . '.xlsx';

        return response()
            ->download($tempPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    private function buildCreditRows(): array
    {
        if (! Schema::hasTable('product_batches') || ! Schema::hasColumn('product_batches', 'payment_type')) {
            return [];
        }

        $hasInstallments = Schema::hasTable('credit_installments');
        $installmentPaidMap = [];

        if ($hasInstallments) {
            $installmentPaidMap = DB::table('credit_installments')
                ->selectRaw('product_batch_id, COALESCE(SUM(amount), 0) as paid_total')
                ->groupBy('product_batch_id')
                ->pluck('paid_total', 'product_batch_id')
                ->map(fn ($value) => (float) $value)
                ->toArray();
        }

        $batches = ProductBatch::query()
            ->with(['product:id,name,barcode,unit', 'product.brand:id,name', 'supplier:id,name', 'creditInstallments.user:id,name'])
            ->where('payment_type', 'KREDIT')
            ->latest('created_at')
            ->get();

        return $batches->map(function (ProductBatch $batch) use ($installmentPaidMap): array {
            $qty = (int) ($batch->stock ?? 0);
            $unitPrice = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $downPayment = (float) ($batch->down_payment_amount ?? 0);
            $principal = $qty * $unitPrice;
            $totalCredit = $principal + $expeditionCost;
            $installmentPaid = (float) ($installmentPaidMap[$batch->id] ?? 0);
            $paid = min($totalCredit, $downPayment + $installmentPaid);
            $remaining = max(0, $totalCredit - $paid);
            $status = $remaining <= 0
                ? 'LUNAS'
                : (($batch->credit_due_date && Carbon::parse($batch->credit_due_date)->isPast()) ? 'JATUH TEMPO' : 'BELUM LUNAS');

            return [
                'batch_id' => (int) $batch->id,
                'tanggal' => $batch->created_at?->format('d M Y H:i') ?? '-',
                'tanggal_ts' => $batch->created_at?->timestamp ?? 0,
                'supplier' => $batch->supplier?->name ?: '-',
                'part_number' => $batch->product?->barcode ?: '-',
                'part_name' => $batch->product?->name ?: '-',
                'brand' => $batch->product?->brand?->name ?: '-',
                'unit' => $batch->product?->unit ?: '-',
                'qty' => $qty,
                'pokok' => $principal,
                'biaya_ekspedisi' => $expeditionCost,
                'total_kredit' => $totalCredit,
                'down_payment' => $downPayment,
                'total_cicilan' => $installmentPaid,
                'total_dibayar' => $paid,
                'sisa_kredit' => $remaining,
                'jatuh_tempo' => $batch->credit_due_date ? Carbon::parse($batch->credit_due_date)->format('d M Y') : '-',
                'jatuh_tempo_ts' => $batch->credit_due_date ? Carbon::parse($batch->credit_due_date)->timestamp : 0,
                'status' => $status,
                'processed_by' => (string) ($batch->processed_by ?? '-'),
            ];
        })->sortByDesc('tanggal_ts')->values()->toArray();
    }

    private function buildInstallmentRows(): array
    {
        if (! Schema::hasTable('credit_installments')) {
            return [];
        }

        $batchSelects = [
            'id',
            'product_id',
            'supplier_id',
            'stock',
            'purchase_price',
            'expedition_cost',
            'down_payment_amount',
            'credit_due_date',
            'created_at',
        ];

        if (Schema::hasColumn('product_batches', 'processed_by')) {
            $batchSelects[] = 'processed_by';
        }

        $batches = ProductBatch::query()
            ->with([
                'product:id,name,barcode',
                'supplier:id,name',
                'creditInstallments.user:id,name',
            ])
            ->get($batchSelects);

        $rows = [];

        foreach ($batches as $batch) {
            $qty = (int) ($batch->stock ?? 0);
            $unitPrice = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $downPayment = (float) ($batch->down_payment_amount ?? 0);
            $principal = $qty * $unitPrice;
            $totalCredit = $principal + $expeditionCost;

            $installments = $batch->creditInstallments->sortBy([
                ['paid_at', 'asc'],
                ['id', 'asc'],
            ]);

            $runningInstallment = 0.0;
            $runningTotalPaid = $downPayment;

            foreach ($installments as $index => $installment) {
                $amount = (float) ($installment->amount ?? 0);
                $runningInstallment += $amount;
                $runningTotalPaid = min($totalCredit, $downPayment + $runningInstallment);
                $remaining = max(0, $totalCredit - $runningTotalPaid);

                $rows[] = [
                    'batch_id' => (int) $batch->id,
                    'tanggal' => $installment->paid_at?->format('d M Y') ?? '-',
                    'tanggal_ts' => $installment->paid_at?->timestamp ?? 0,
                    'supplier' => $batch->supplier?->name ?: '-',
                    'part_number' => $batch->product?->barcode ?: '-',
                    'part_name' => $batch->product?->name ?: '-',
                    'nomor_kredit' => 'KR-' . str_pad((string) $batch->id, 5, '0', STR_PAD_LEFT),
                    'pokok' => $principal,
                    'biaya_ekspedisi' => $expeditionCost,
                    'down_payment' => $downPayment,
                    'cicilan_ke' => $index + 1,
                    'nominal_cicilan' => $amount,
                    'total_cicilan' => $runningInstallment,
                    'total_dibayar' => $runningTotalPaid,
                    'sisa_setelah_cicilan' => $remaining,
                    'kasir' => $installment->user?->name ?: ($installment->processed_by ?: '-'),
                    'catatan' => trim((string) ($installment->note ?? '')) ?: '-',
                    'jatuh_tempo' => $batch->credit_due_date ? Carbon::parse($batch->credit_due_date)->format('d M Y') : '-',
                    'status' => $remaining <= 0 ? 'LUNAS' : 'BELUM LUNAS',
                ];
            }
        }

        return collect($rows)
            ->sortByDesc(fn (array $row) => sprintf('%020d-%020d', $row['tanggal_ts'] ?? 0, $row['batch_id'] ?? 0))
            ->values()
            ->map(function (array $row): array {
                unset($row['tanggal_ts']);

                return $row;
            })
            ->toArray();
    }

    private function buildSettlementRows(array $creditRows): array
    {
        if (! Schema::hasTable('credit_installments')) {
            return [];
        }

        $batchIds = collect($creditRows)->pluck('batch_id')->all();
        if ($batchIds === []) {
            return [];
        }

        $batches = ProductBatch::query()
            ->with([
                'product:id,name,barcode',
                'supplier:id,name',
                'creditInstallments.user:id,name',
            ])
            ->whereIn('id', $batchIds)
            ->get([
                'id',
                'product_id',
                'supplier_id',
                'stock',
                'purchase_price',
                'expedition_cost',
                'down_payment_amount',
                'credit_due_date',
                'created_at',
            ]);

        $rows = [];

        foreach ($batches as $batch) {
            $qty = (int) ($batch->stock ?? 0);
            $unitPrice = (float) ($batch->purchase_price ?? 0);
            $expeditionCost = (float) ($batch->expedition_cost ?? 0);
            $downPayment = (float) ($batch->down_payment_amount ?? 0);
            $principal = $qty * $unitPrice;
            $totalCredit = $principal + $expeditionCost;
            $installments = $batch->creditInstallments->sortBy([
                ['paid_at', 'asc'],
                ['id', 'asc'],
            ]);

            $runningPaid = $downPayment;
            $finalInstallment = null;
            $installmentCount = 0;

            foreach ($installments as $installment) {
                $installmentCount++;
                $runningPaid += (float) ($installment->amount ?? 0);

                if ($runningPaid >= $totalCredit) {
                    $finalInstallment = $installment;
                    break;
                }
            }

            if (! $finalInstallment) {
                continue;
            }

            $rows[] = [
                'tanggal' => $finalInstallment->paid_at?->format('d M Y') ?? '-',
                'tanggal_ts' => $finalInstallment->paid_at?->timestamp ?? 0,
                'supplier' => $batch->supplier?->name ?: '-',
                'part_number' => $batch->product?->barcode ?: '-',
                'part_name' => $batch->product?->name ?: '-',
                'pokok' => $principal,
                'biaya_ekspedisi' => $expeditionCost,
                'down_payment' => $downPayment,
                'total_kredit' => $totalCredit,
                'total_cicilan' => min($totalCredit, max(0, $runningPaid - $downPayment)),
                'total_dibayar' => min($totalCredit, $runningPaid),
                'cicilan_ke' => $installmentCount,
                'nominal_lunas' => (float) ($finalInstallment->amount ?? 0),
                'kasir' => $finalInstallment->user?->name ?: ($finalInstallment->processed_by ?: '-'),
                'catatan' => trim((string) ($finalInstallment->note ?? '')) ?: 'Pelunasan kredit',
                'receipt_id' => (int) $finalInstallment->id,
            ];
        }

        return collect($rows)
            ->sortByDesc('tanggal_ts')
            ->values()
            ->map(function (array $row): array {
                unset($row['tanggal_ts']);

                return $row;
            })
            ->toArray();
    }

    private function buildSummarySheet($sheet, array $credits, array $installments, array $settlements): void
    {
        $sheet->setTitle('Utang Saya');
        $sheet->setShowGridLines(false);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $this->setSummaryWidths($sheet);

        $row = 1;
        $this->writeMergedRow($sheet, $row, 'N', 'TOKO PAK PAUL', $this->styleTitle());
        $this->writeMergedRow($sheet, $row, 'N', 'Export Excel utang, cicilan, dan riwayat pelunasan yang rinci', $this->styleSubtitle());
        $row++;

        $summaryRows = [
            ['Total Transaksi Kredit', number_format(count($credits), 0, ',', '.') . ' transaksi'],
            ['Total Nilai Pokok', $this->sumMoney($credits, 'pokok')],
            ['Total Biaya Ekspedisi', $this->sumMoney($credits, 'biaya_ekspedisi')],
            ['Total Kredit', $this->sumMoney($credits, 'total_kredit')],
            ['Total DP', $this->sumMoney($credits, 'down_payment')],
            ['Total Cicilan', $this->sumMoney($credits, 'total_cicilan')],
            ['Sisa Kredit', $this->sumMoney($credits, 'sisa_kredit')],
            ['Transaksi Jatuh Tempo', number_format(collect($credits)->where('status', 'JATUH TEMPO')->count(), 0, ',', '.') . ' transaksi'],
        ];
        $this->writeTwoColumnSummary($sheet, $row, $summaryRows);

        $row += 2;
        $this->writeMergedRow($sheet, $row, 'N', 'DAFTAR UTANG', $this->styleSectionHeader());

        $this->writeRow($sheet, $row, [
            'Tanggal',
            'Supplier',
            'Part Number',
            'Barang',
            'Merek',
            'Qty',
            'Pokok',
            'Ekspedisi',
            'Total Kredit',
            'DP',
            'Cicilan',
            'Total Dibayar',
            'Sisa Kredit',
            'Jatuh Tempo',
            'Status',
        ], $this->styleTableHeader());

        foreach ($credits as $rowData) {
            $statusStyle = $this->statusStyle($rowData['status'] ?? '-');
            $this->writeRow($sheet, $row, [
                $rowData['tanggal'] ?? '-',
                $rowData['supplier'] ?? '-',
                $rowData['part_number'] ?? '-',
                $rowData['part_name'] ?? '-',
                $rowData['brand'] ?? '-',
                (int) ($rowData['qty'] ?? 0),
                (float) ($rowData['pokok'] ?? 0),
                (float) ($rowData['biaya_ekspedisi'] ?? 0),
                (float) ($rowData['total_kredit'] ?? 0),
                (float) ($rowData['down_payment'] ?? 0),
                (float) ($rowData['total_cicilan'] ?? 0),
                (float) ($rowData['total_dibayar'] ?? 0),
                (float) ($rowData['sisa_kredit'] ?? 0),
                $rowData['jatuh_tempo'] ?? '-',
                $rowData['status'] ?? '-',
            ], array_merge($this->styleTableBody(), $statusStyle));
        }

        $row += 2;
        $this->writeMergedRow($sheet, $row, 'N', 'RINGKASAN BULANAN', $this->styleSectionHeader());

        $monthlyGroups = collect($credits)
            ->groupBy(fn (array $rowData) => substr((string) ($rowData['tanggal'] ?? ''), 3, 8))
            ->map(function ($group, $key) {
                return [
                    'bulan' => $key ?: '-',
                    'transaksi' => $group->count(),
                    'pokok' => $group->sum('pokok'),
                    'total_kredit' => $group->sum('total_kredit'),
                    'sisa' => $group->sum('sisa_kredit'),
                    'jatuh_tempo' => $group->where('status', 'JATUH TEMPO')->count(),
                ];
            })
            ->values()
            ->toArray();

        $this->writeRow($sheet, $row, ['Bulan', 'Transaksi', 'Pokok', 'Total Kredit', 'Sisa Kredit', 'Jatuh Tempo'], $this->styleTableHeader());
        foreach ($monthlyGroups as $group) {
            $this->writeRow($sheet, $row, [
                $group['bulan'],
                (int) $group['transaksi'],
                (float) $group['pokok'],
                (float) $group['total_kredit'],
                (float) $group['sisa'],
                (int) $group['jatuh_tempo'],
            ], $this->styleTableBody());
        }

        $this->applyMoneyFormats($sheet, [
            'G' => '"Rp" #,##0',
            'H' => '"Rp" #,##0',
            'I' => '"Rp" #,##0',
            'J' => '"Rp" #,##0',
            'K' => '"Rp" #,##0',
            'L' => '"Rp" #,##0',
            'M' => '"Rp" #,##0',
        ], 1, $row - 1);
        $this->applyBorders($sheet, 1, $row - 1, 'N');
    }

    private function buildInstallmentSheet($sheet, array $installments): void
    {
        $sheet->setTitle('Cicilan');
        $sheet->setShowGridLines(false);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $this->setInstallmentWidths($sheet);

        $row = 1;
        $this->writeMergedRow($sheet, $row, 'O', 'TOKO PAK PAUL', $this->styleTitle());
        $this->writeMergedRow($sheet, $row, 'O', 'Detail cicilan per transaksi, termasuk pokok, DP, dan sisa setelah bayar', $this->styleSubtitle());
        $row++;

        $this->writeRow($sheet, $row, [
            'Tanggal',
            'Supplier',
            'Part Number',
            'Barang',
            'No Kredit',
            'Pokok',
            'Ekspedisi',
            'DP Awal',
            'Cicilan Ke',
            'Nominal Cicilan',
            'Total Cicilan',
            'Total Dibayar',
            'Sisa Setelah Cicilan',
            'Admin',
            'Catatan',
        ], $this->styleTableHeader());

        foreach ($installments as $rowData) {
            $this->writeRow($sheet, $row, [
                $rowData['tanggal'] ?? '-',
                $rowData['supplier'] ?? '-',
                $rowData['part_number'] ?? '-',
                $rowData['part_name'] ?? '-',
                $rowData['nomor_kredit'] ?? '-',
                (float) ($rowData['pokok'] ?? 0),
                (float) ($rowData['biaya_ekspedisi'] ?? 0),
                (float) ($rowData['down_payment'] ?? 0),
                (int) ($rowData['cicilan_ke'] ?? 0),
                (float) ($rowData['nominal_cicilan'] ?? 0),
                (float) ($rowData['total_cicilan'] ?? 0),
                (float) ($rowData['total_dibayar'] ?? 0),
                (float) ($rowData['sisa_setelah_cicilan'] ?? 0),
                $rowData['kasir'] ?? '-',
                $rowData['catatan'] ?? '-',
            ], $this->styleTableBody());
        }

        $this->applyMoneyFormats($sheet, [
            'F' => '"Rp" #,##0',
            'G' => '"Rp" #,##0',
            'H' => '"Rp" #,##0',
            'J' => '"Rp" #,##0',
            'K' => '"Rp" #,##0',
            'L' => '"Rp" #,##0',
            'M' => '"Rp" #,##0',
        ], 1, max(1, $row - 1));
        $this->applyBorders($sheet, 1, max(1, $row - 1), 'O');
    }

    private function buildSettlementSheet($sheet, array $settlements): void
    {
        $sheet->setTitle('Pelunasan');
        $sheet->setShowGridLines(false);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $this->setSettlementWidths($sheet);

        $row = 1;
        $this->writeMergedRow($sheet, $row, 'M', 'TOKO PAK PAUL', $this->styleTitle());
        $this->writeMergedRow($sheet, $row, 'M', 'Riwayat pelunasan kredit yang sudah lunas', $this->styleSubtitle());
        $row++;

        $this->writeRow($sheet, $row, [
            'Tanggal Lunas',
            'Supplier',
            'Part Number',
            'Barang',
            'Pokok',
            'Ekspedisi',
            'DP',
            'Total Kredit',
            'Total Cicilan',
            'Nominal Pelunasan',
            'Admin',
            'Catatan',
            'ID Nota',
        ], $this->styleTableHeader());

        foreach ($settlements as $rowData) {
            $this->writeRow($sheet, $row, [
                $rowData['tanggal'] ?? '-',
                $rowData['supplier'] ?? '-',
                $rowData['part_number'] ?? '-',
                $rowData['part_name'] ?? '-',
                (float) ($rowData['pokok'] ?? 0),
                (float) ($rowData['biaya_ekspedisi'] ?? 0),
                (float) ($rowData['down_payment'] ?? 0),
                (float) ($rowData['total_kredit'] ?? 0),
                (float) ($rowData['total_cicilan'] ?? 0),
                (float) ($rowData['nominal_lunas'] ?? 0),
                $rowData['kasir'] ?? '-',
                $rowData['catatan'] ?? '-',
                (int) ($rowData['receipt_id'] ?? 0),
            ], $this->styleTableBody());
        }

        $this->applyMoneyFormats($sheet, [
            'E' => '"Rp" #,##0',
            'F' => '"Rp" #,##0',
            'G' => '"Rp" #,##0',
            'H' => '"Rp" #,##0',
            'I' => '"Rp" #,##0',
            'J' => '"Rp" #,##0',
        ], 1, max(1, $row - 1));
        $this->applyBorders($sheet, 1, max(1, $row - 1), 'M');
    }

    private function writeTwoColumnSummary($sheet, int &$row, array $rows): void
    {
        $index = 0;
        foreach ($rows as [$label, $value]) {
            $leftCol = $index % 2 === 0 ? 'A' : 'G';
            $leftStart = Coordinate::columnIndexFromString($leftCol);
            $labelEnd = Coordinate::stringFromColumnIndex($leftStart + 2);
            $valueStart = Coordinate::stringFromColumnIndex($leftStart + 3);
            $valueEnd = Coordinate::stringFromColumnIndex($leftStart + 5);
            $labelCell = $leftCol . $row;
            $valueCell = $valueStart . $row;
            $sheet->setCellValueExplicit($labelCell, (string) $label, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit($valueCell, (string) $value, DataType::TYPE_STRING);
            $sheet->mergeCells($labelCell . ':' . $labelEnd . $row);
            $sheet->mergeCells($valueCell . ':' . $valueEnd . $row);
            $sheet->getStyle($labelCell . ':' . $labelEnd . $row)->applyFromArray($this->styleMetaKey());
            $sheet->getStyle($valueCell . ':' . $valueEnd . $row)->applyFromArray($this->styleMetaValue());

            if ($index % 2 === 1) {
                $row++;
            }

            $index++;
        }

        if ($index % 2 === 1) {
            $row++;
        }
    }

    private function writeRow($sheet, int &$row, array $values, array $style = []): void
    {
        $columnIndex = 1;

        foreach ($values as $value) {
            $cell = Coordinate::stringFromColumnIndex($columnIndex) . $row;

            if (is_int($value) || is_float($value)) {
                $sheet->setCellValue($cell, $value);
            } else {
                $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
            }

            $columnIndex++;
        }

        if ($style) {
            $range = 'A' . $row . ':' . Coordinate::stringFromColumnIndex(count($values)) . $row;
            $sheet->getStyle($range)->applyFromArray($style);
        }

        $sheet->getRowDimension($row)->setRowHeight(22);
        $row++;
    }

    private function writeMergedRow($sheet, int &$row, string $lastColumn, string $value, array $style = []): void
    {
        $startRow = $row;
        $sheet->setCellValueExplicit('A' . $startRow, $value, DataType::TYPE_STRING);
        $sheet->mergeCells('A' . $startRow . ':' . $lastColumn . $startRow);
        if ($style) {
            $sheet->getStyle('A' . $startRow . ':' . $lastColumn . $startRow)->applyFromArray($style);
        }
        $sheet->getRowDimension($startRow)->setRowHeight(24);
        $row++;
    }

    private function styleTitle(): array
    {
        return [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
    }

    private function styleSubtitle(): array
    {
        return [
            'font' => ['italic' => true, 'color' => ['rgb' => '475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
    }

    private function styleSectionHeader(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => '064E3B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A7F3D0']]],
        ];
    }

    private function styleTableHeader(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => '1F2937']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E5E7EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ];
    }

    private function styleTableBody(): array
    {
        return [
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
        ];
    }

    private function styleMetaKey(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => '334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
    }

    private function styleMetaValue(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
    }

    private function statusStyle(string $status): array
    {
        return match ($status) {
            'LUNAS' => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
                'font' => ['bold' => true, 'color' => ['rgb' => '166534']],
            ],
            'JATUH TEMPO' => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'B91C1C']],
            ],
            default => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'B45309']],
            ],
        };
    }

    private function setSummaryWidths($sheet): void
    {
        $widths = [
            'A' => 18,
            'B' => 14,
            'C' => 18,
            'D' => 16,
            'E' => 16,
            'F' => 14,
            'G' => 18,
            'H' => 14,
            'I' => 14,
            'J' => 16,
            'K' => 14,
            'L' => 16,
            'M' => 16,
            'N' => 14,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function setInstallmentWidths($sheet): void
    {
        $widths = [
            'A' => 16,
            'B' => 20,
            'C' => 18,
            'D' => 24,
            'E' => 14,
            'F' => 14,
            'G' => 14,
            'H' => 14,
            'I' => 12,
            'J' => 14,
            'K' => 14,
            'L' => 14,
            'M' => 16,
            'N' => 16,
            'O' => 24,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function setSettlementWidths($sheet): void
    {
        $widths = [
            'A' => 16,
            'B' => 20,
            'C' => 18,
            'D' => 24,
            'E' => 14,
            'F' => 14,
            'G' => 14,
            'H' => 14,
            'I' => 14,
            'J' => 14,
            'K' => 16,
            'L' => 24,
            'M' => 12,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function applyMoneyFormats($sheet, array $columnFormats, int $startRow, int $endRow): void
    {
        foreach ($columnFormats as $column => $format) {
            $sheet->getStyle("{$column}{$startRow}:{$column}{$endRow}")
                ->getNumberFormat()
                ->setFormatCode($format);
        }
    }

    private function applyBorders($sheet, int $startRow, int $endRow, string $lastColumn): void
    {
        if ($endRow < $startRow) {
            return;
        }

        $sheet->getStyle("A{$startRow}:{$lastColumn}{$endRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()
            ->setRGB('CBD5E1');
    }

    private function sumMoney(array $rows, string $key): string
    {
        $sum = collect($rows)->sum(fn (array $row) => (float) ($row[$key] ?? 0));

        return 'Rp ' . number_format((float) $sum, 0, ',', '.');
    }
}
