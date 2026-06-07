<?php

namespace App\Services;

use App\Models\CreditInstallment;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SalesReturn;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OfflineBackupService
{
    public function createBackup(): array
    {
        $timestamp = now();
        $backupRoot = $this->getDesktopBackupRoot();
        $folderRelative = 'backup-' . $timestamp->format('Ymd-His');
        $folderPath = $backupRoot . DIRECTORY_SEPARATOR . $folderRelative;
        File::ensureDirectoryExists($folderPath);

        $report = app(ProductGroupReportService::class)->build();

        [$purchaseRows, $salesRows] = $this->flattenGroupRows($report['groups'] ?? []);
        $installmentRows = $this->loadInstallmentRows();
        $returnRows = $this->loadReturnRows();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Toko Pak Paul')
            ->setTitle('Backup Nota dan Track Record')
            ->setSubject('Backup Excel Offline')
            ->setDescription('Backup semua nota, riwayat barang, penjualan, kredit, dan retur.');

        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $this->buildSummarySheet($spreadsheet->getActiveSheet(), $report, $purchaseRows, $salesRows, $installmentRows, $returnRows, $timestamp);
        $this->buildGroupsSheet($spreadsheet->createSheet(), $report, $timestamp);
        $this->buildPurchaseSheet($spreadsheet->createSheet(), $purchaseRows, $timestamp);
        $this->buildSalesSheet($spreadsheet->createSheet(), $salesRows, $timestamp);
        $this->buildInstallmentSheet($spreadsheet->createSheet(), $installmentRows, $timestamp);
        $this->buildReturnSheet($spreadsheet->createSheet(), $returnRows, $timestamp);

        $filename = 'backup-nota-' . $timestamp->format('Ymd-His') . '.xlsx';
        $absolutePath = $folderPath . DIRECTORY_SEPARATOR . $filename;
        $notePdfCount = $this->buildNotePdfs($report['groups'] ?? [], $folderPath, $timestamp);

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($absolutePath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return [
            'folder_relative' => $folderRelative,
            'folder_path' => $folderPath,
            'file_relative' => $folderRelative . DIRECTORY_SEPARATOR . $filename,
            'file_path' => $absolutePath,
            'filename' => $filename,
            'product_groups' => count($report['groups'] ?? []),
            'purchase_rows' => count($purchaseRows),
            'sales_rows' => count($salesRows),
            'installment_rows' => count($installmentRows),
            'return_rows' => count($returnRows),
            'note_pdf_count' => $notePdfCount,
            'note_pdf_folder' => $folderPath . DIRECTORY_SEPARATOR . 'nota-pdf',
        ];
    }

    private function getDesktopBackupRoot(): string
    {
        $candidates = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $desktop = $this->resolveWindowsDesktopDirectory();
            if ($desktop !== null) {
                $candidates[] = $desktop;
            }
        }

        $userProfile = (string) getenv('USERPROFILE');
        if ($userProfile !== '') {
            $candidates[] = rtrim($userProfile, "\\/") . DIRECTORY_SEPARATOR . 'OneDrive' . DIRECTORY_SEPARATOR . 'Desktop';
            $candidates[] = rtrim($userProfile, "\\/") . DIRECTORY_SEPARATOR . 'Desktop';
        }

        $homeDrive = (string) getenv('HOMEDRIVE');
        $homePath = (string) getenv('HOMEPATH');
        if ($homeDrive !== '' && $homePath !== '') {
            $candidates[] = rtrim($homeDrive . $homePath, "\\/") . DIRECTORY_SEPARATOR . 'Desktop';
        }

        foreach (array_unique($candidates) as $desktop) {
            if ($desktop === '') {
                continue;
            }

            if (is_dir($desktop) || @mkdir($desktop, 0775, true) || is_dir($desktop)) {
                $backupRoot = $desktop . DIRECTORY_SEPARATOR . 'Backup Toko Pak Paul';
                File::ensureDirectoryExists($backupRoot);
                return $backupRoot;
            }
        }

        $fallback = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Backup Toko Pak Paul';
        File::ensureDirectoryExists($fallback);

        return $fallback;
    }

    private function resolveWindowsDesktopDirectory(): ?string
    {
        $command = 'powershell -NoProfile -Command "[Environment]::GetFolderPath(\'DesktopDirectory\')"';
        $output = [];
        $exitCode = 0;

        @exec($command, $output, $exitCode);
        $path = trim(implode(PHP_EOL, $output));

        return $exitCode === 0 && $path !== '' ? $path : null;
    }

    private function buildSummarySheet($sheet, array $report, array $purchaseRows, array $salesRows, array $installmentRows, array $returnRows, Carbon $timestamp): void
    {
        $sheet->setTitle('Ringkasan');
        $sheet->freezePane('A4');
        $sheet->setShowGridLines(false);

        $sheet->setCellValue('A1', 'TOKO PAK PAUL');
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A2', 'BACKUP OFFLINE - SEMUA NOTA DAN TRACK RECORD');
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A3', 'Dicetak: ' . $timestamp->format('d M Y H:i'));
        $sheet->mergeCells('A3:F3');

        $summary = $report['summary'] ?? [];
        $rows = [
            ['Total Part Number', (int) ($summary['total_products'] ?? 0)],
            ['Total Pembelian', (int) ($summary['purchase_count'] ?? 0)],
            ['Total Penjualan', (int) ($summary['sales_count'] ?? 0)],
            ['Total Nilai Beli', (string) ($summary['purchase_value'] ?? 'Rp 0')],
            ['Total Nilai Cicilan', (string) ($summary['purchase_installment_value'] ?? 'Rp 0')],
            ['Total Nilai Jual', (string) ($summary['sales_value'] ?? 'Rp 0')],
            ['Pembelian Kredit', (int) ($summary['purchase_kredit'] ?? 0)],
            ['Pembelian Lunas', (int) ($summary['purchase_lunas'] ?? 0)],
            ['Penjualan Kredit', (int) ($summary['sales_kredit'] ?? 0)],
            ['Penjualan Lunas', (int) ($summary['sales_lunas'] ?? 0)],
            ['Total Cicilan', count($installmentRows)],
            ['Total Retur', count($returnRows)],
        ];

        $sheet->fromArray([['Judul', 'Nilai']], null, 'A5');
        $sheet->fromArray($rows, null, 'A6');

        $sheet->getStyle('A1:F3')->applyFromArray($this->titleStyle());
        $sheet->getStyle('A5:B5')->applyFromArray($this->headerStyle());
        $sheet->getStyle('A6:B' . (5 + count($rows)))->applyFromArray($this->bodyStyle());
        $sheet->getColumnDimension('A')->setWidth(26);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
    }

    private function buildGroupsSheet($sheet, array $report, Carbon $timestamp): void
    {
        $sheet->setTitle('Kelompok Barang');
        $sheet->freezePane('A6');
        $sheet->setShowGridLines(false);

        $sheet->setCellValue('A1', 'TOKO PAK PAUL');
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A2', 'PER PART NUMBER - RINGKASAN DAN TRACK RECORD');
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A3', 'Dicetak: ' . $timestamp->format('d M Y H:i'));
        $sheet->mergeCells('A3:O3');

        $sheet->fromArray([
            ['Part Number', 'Nama Barang', 'Kategori', 'Merek', 'Unit', 'Stok', 'Pembelian', 'Penjualan', 'Beli Lunas', 'Beli Kredit', 'Jual Lunas', 'Jual Kredit', 'Nilai Beli', 'Nilai Jual', 'Aktivitas Terakhir'],
        ], null, 'A5');

        $row = 6;
        foreach (($report['groups'] ?? []) as $group) {
            $sheet->fromArray([[
                $group['part_number'] ?? '-',
                $group['part_name'] ?? '-',
                $group['category'] ?? '-',
                $group['brand'] ?? '-',
                $group['unit'] ?? '-',
                (int) ($group['total_stock'] ?? 0),
                (int) ($group['summary']['purchase_batches'] ?? 0),
                (int) ($group['summary']['sales_invoices'] ?? 0),
                (int) ($group['summary']['purchase_lunas'] ?? 0),
                (int) ($group['summary']['purchase_kredit'] ?? 0),
                (int) ($group['summary']['sales_lunas'] ?? 0),
                (int) ($group['summary']['sales_kredit'] ?? 0),
                (string) ($group['summary']['purchase_value'] ?? 'Rp 0'),
                (string) ($group['summary']['sales_value'] ?? 'Rp 0'),
                isset($group['last_activity_ts']) && $group['last_activity_ts']
                    ? Carbon::createFromTimestamp((int) $group['last_activity_ts'])->format('d M Y H:i')
                    : '-',
            ]], null, 'A' . $row);
            $row++;
        }

        $this->styleSheet($sheet, 'A5:O' . max(5, $row - 1));
        $this->setWidths($sheet, [
            'A' => 18, 'B' => 22, 'C' => 18, 'D' => 18, 'E' => 12, 'F' => 10,
            'G' => 12, 'H' => 12, 'I' => 12, 'J' => 12, 'K' => 12, 'L' => 12,
            'M' => 16, 'N' => 16, 'O' => 20,
        ]);
    }

    private function buildPurchaseSheet($sheet, array $rows, Carbon $timestamp): void
    {
        $sheet->setTitle('Pembelian');
        $sheet->freezePane('A6');
        $sheet->setShowGridLines(false);
        $sheet->setCellValue('A1', 'TOKO PAK PAUL');
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A2', 'SEMUA NOTA PEMBELIAN ADMIN');
        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A3', 'Dicetak: ' . $timestamp->format('d M Y H:i'));
        $sheet->mergeCells('A3:N3');

        $headers = [
            'Waktu', 'Part Number', 'Part Name', 'Supplier', 'Kondisi', 'Diproses Oleh',
            'Qty', 'Harga Beli', 'Biaya Ekspedisi', 'Total', 'DP', 'Sisa', 'Jatuh Tempo', 'Status',
        ];
        $sheet->fromArray([$headers], null, 'A5');

        $row = 6;
        foreach ($rows as $dataRow) {
            $sheet->fromArray([[
                $dataRow['tanggal'] ?? '-',
                $dataRow['part_number'] ?? '-',
                $dataRow['part_name'] ?? '-',
                $dataRow['supplier'] ?? '-',
                $dataRow['condition'] ?? '-',
                $dataRow['processed_by'] ?? '-',
                (int) ($dataRow['qty'] ?? 0),
                $dataRow['harga_beli'] ?? 'Rp 0',
                $dataRow['expedition_cost'] ?? 'Rp 0',
                $dataRow['total'] ?? 'Rp 0',
                $dataRow['down_payment'] ?? 'Rp 0',
                $dataRow['sisa_kredit'] ?? 'Rp 0',
                $dataRow['jatuh_tempo'] ?? '-',
                $dataRow['status'] ?? '-',
            ]], null, 'A' . $row);
            $row++;
        }

        $this->styleSheet($sheet, 'A5:N' . max(5, $row - 1));
        $this->setWidths($sheet, [
            'A' => 18, 'B' => 16, 'C' => 20, 'D' => 18, 'E' => 14, 'F' => 16, 'G' => 10,
            'H' => 16, 'I' => 16, 'J' => 16, 'K' => 16, 'L' => 16, 'M' => 16, 'N' => 14,
        ]);
    }

    private function buildSalesSheet($sheet, array $rows, Carbon $timestamp): void
    {
        $sheet->setTitle('Penjualan');
        $sheet->freezePane('A6');
        $sheet->setShowGridLines(false);
        $sheet->setCellValue('A1', 'TOKO PAK PAUL');
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A2', 'SEMUA NOTA PENJUALAN KASIR');
        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A3', 'Dicetak: ' . $timestamp->format('d M Y H:i'));
        $sheet->mergeCells('A3:N3');

        $headers = [
            'Waktu', 'Invoice', 'Part Number', 'Part Name', 'Customer / PT', 'Kasir',
            'Metode', 'Qty', 'Harga', 'Subtotal', 'Kredit', 'Jatuh Tempo', 'Status', 'Retur',
        ];
        $sheet->fromArray([$headers], null, 'A5');

        $row = 6;
        foreach ($rows as $dataRow) {
            $sheet->fromArray([[
                $dataRow['tanggal'] ?? '-',
                $dataRow['invoice'] ?? '-',
                $dataRow['part_number'] ?? '-',
                $dataRow['part_name'] ?? '-',
                $dataRow['customer'] ?? '-',
                $dataRow['cashier'] ?? '-',
                $dataRow['payment_method'] ?? '-',
                (int) ($dataRow['qty'] ?? 0),
                $dataRow['price'] ?? 'Rp 0',
                $dataRow['total'] ?? 'Rp 0',
                $dataRow['kredit'] ?? 'Rp 0',
                $dataRow['jatuh_tempo'] ?? '-',
                $dataRow['status'] ?? '-',
                (int) ($dataRow['return_count'] ?? 0),
            ]], null, 'A' . $row);
            $row++;
        }

        $this->styleSheet($sheet, 'A5:N' . max(5, $row - 1));
        $this->setWidths($sheet, [
            'A' => 18, 'B' => 16, 'C' => 16, 'D' => 20, 'E' => 18, 'F' => 16, 'G' => 12,
            'H' => 10, 'I' => 16, 'J' => 16, 'K' => 16, 'L' => 16, 'M' => 14, 'N' => 10,
        ]);
    }

    private function buildInstallmentSheet($sheet, array $rows, Carbon $timestamp): void
    {
        $sheet->setTitle('Cicilan');
        $sheet->freezePane('A6');
        $sheet->setShowGridLines(false);
        $sheet->setCellValue('A1', 'TOKO PAK PAUL');
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A2', 'RIWAYAT CICILAN KREDIT');
        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A3', 'Dicetak: ' . $timestamp->format('d M Y H:i'));
        $sheet->mergeCells('A3:L3');

        $headers = [
            'Tanggal', 'Part Number', 'Part Name', 'Supplier', 'Nominal', 'Diproses Oleh', 'Catatan',
        ];
        $sheet->fromArray([$headers], null, 'A5');

        $row = 6;
        foreach ($rows as $dataRow) {
            $sheet->fromArray([[
                $dataRow['paid_at'] ?? '-',
                $dataRow['part_number'] ?? '-',
                $dataRow['part_name'] ?? '-',
                $dataRow['supplier'] ?? '-',
                $dataRow['amount'] ?? 'Rp 0',
                $dataRow['processed_by'] ?? '-',
                $dataRow['note'] ?? '-',
            ]], null, 'A' . $row);
            $row++;
        }

        $this->styleSheet($sheet, 'A5:G' . max(5, $row - 1));
        $this->setWidths($sheet, [
            'A' => 16, 'B' => 16, 'C' => 20, 'D' => 18, 'E' => 16, 'F' => 18, 'G' => 26,
        ]);
    }

    private function buildReturnSheet($sheet, array $rows, Carbon $timestamp): void
    {
        $sheet->setTitle('Retur');
        $sheet->freezePane('A6');
        $sheet->setShowGridLines(false);
        $sheet->setCellValue('A1', 'TOKO PAK PAUL');
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A2', 'RIWAYAT RETUR KASIR');
        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A3', 'Dicetak: ' . $timestamp->format('d M Y H:i'));
        $sheet->mergeCells('A3:M3');

        $headers = [
            'Tanggal', 'Invoice', 'Return Number', 'Part Number', 'Part Name', 'Customer / PT', 'Kasir', 'Qty', 'Refund', 'Alasan', 'Tipe', 'Status', 'Catatan',
        ];
        $sheet->fromArray([$headers], null, 'A5');

        $row = 6;
        foreach ($rows as $dataRow) {
            $sheet->fromArray([[
                $dataRow['returned_at'] ?? '-',
                $dataRow['invoice'] ?? '-',
                $dataRow['return_number'] ?? '-',
                $dataRow['part_number'] ?? '-',
                $dataRow['part_name'] ?? '-',
                $dataRow['customer'] ?? '-',
                $dataRow['cashier'] ?? '-',
                (int) ($dataRow['qty'] ?? 0),
                $dataRow['refund'] ?? 'Rp 0',
                $dataRow['reason'] ?? '-',
                $dataRow['return_type'] ?? '-',
                $dataRow['status'] ?? '-',
                $dataRow['note'] ?? '-',
            ]], null, 'A' . $row);
            $row++;
        }

        $this->styleSheet($sheet, 'A5:M' . max(5, $row - 1));
        $this->setWidths($sheet, [
            'A' => 16, 'B' => 16, 'C' => 18, 'D' => 16, 'E' => 20, 'F' => 18, 'G' => 16,
            'H' => 10, 'I' => 16, 'J' => 18, 'K' => 14, 'L' => 14, 'M' => 22,
        ]);
    }

    private function flattenGroupRows(array $groups): array
    {
        $purchaseRows = [];
        $salesRows = [];

        foreach ($groups as $group) {
            $partNumber = $group['part_number'] ?? '-';
            $partName = $group['part_name'] ?? '-';

            foreach (($group['purchase_rows'] ?? []) as $row) {
                $purchaseRows[] = array_merge($row, [
                    'part_number' => $partNumber,
                    'part_name' => $partName,
                    'expedition_cost' => $row['expedition_cost'] ?? 'Rp 0',
                ]);
            }

            foreach (($group['sales_rows'] ?? []) as $row) {
                $salesRows[] = array_merge($row, [
                    'part_number' => $partNumber,
                    'part_name' => $partName,
                    'price' => $row['price'] ?? 'Rp 0',
                ]);
            }
        }

        usort($purchaseRows, fn (array $a, array $b): int => $this->compareDateLabel($b['tanggal'] ?? '', $a['tanggal'] ?? ''));
        usort($salesRows, fn (array $a, array $b): int => $this->compareDateLabel($b['tanggal'] ?? '', $a['tanggal'] ?? ''));

        return [$purchaseRows, $salesRows];
    }

    private function loadInstallmentRows(): array
    {
        if (! Schema::hasTable('credit_installments')) {
            return [];
        }

        return CreditInstallment::query()
            ->with([
                'user:id,name',
                'productBatch.product:id,name,barcode',
                'productBatch.supplier:id,name',
            ])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (CreditInstallment $installment) => [
                'paid_at' => $installment->paid_at ? Carbon::parse($installment->paid_at)->format('d M Y') : '-',
                'part_number' => strtoupper((string) ($installment->productBatch?->product?->barcode ?? '-')),
                'part_name' => $installment->productBatch?->product?->name ?? '-',
                'supplier' => $installment->productBatch?->supplier?->name ?? '-',
                'amount' => 'Rp ' . number_format((float) ($installment->amount ?? 0), 0, ',', '.'),
                'processed_by' => trim((string) ($installment->processed_by ?? $installment->user?->name ?? '')) ?: '-',
                'note' => trim((string) ($installment->note ?? '')) ?: '-',
            ])
            ->toArray();
    }

    private function loadReturnRows(): array
    {
        if (! Schema::hasTable('sales_returns')) {
            return [];
        }

        $returns = SalesReturn::query()
            ->with([
                'sale.user:id,name',
                'user:id,name',
                'items.product:id,name,barcode',
                'items.productBatch.product:id,name,barcode',
            ])
            ->orderByDesc('returned_at')
            ->orderByDesc('id')
            ->get();

        $rows = [];
        foreach ($returns as $return) {
            foreach ($return->items as $item) {
                $product = $item->product ?? $item->productBatch?->product;
                $rows[] = [
                    'returned_at' => $return->returned_at ? Carbon::parse($return->returned_at)->format('d M Y H:i') : '-',
                    'invoice' => $return->invoice_number ?: '-',
                    'return_number' => $return->return_number ?: '-',
                    'part_number' => strtoupper((string) ($product?->barcode ?? '-')),
                    'part_name' => $item->product_name ?: ($product?->name ?? '-'),
                    'customer' => trim((string) ($return->sale?->customer_name ?? '')) ?: 'Pembeli Umum',
                    'cashier' => trim((string) ($return->sale?->cashierDisplayName ?? $return->sale?->user?->name ?? $return->user?->name ?? '')) ?: '-',
                    'qty' => (int) ($item->qty_return ?? 0),
                    'refund' => 'Rp ' . number_format((float) ($item->subtotal_return ?? 0), 0, ',', '.'),
                    'reason' => trim((string) ($return->reason_other ?? $return->reason ?? '')) ?: '-',
                    'return_type' => strtoupper((string) ($return->return_type ?? '-')),
                    'status' => 'RETUR',
                    'note' => trim((string) ($return->notes ?? '')) ?: '-',
                ];
            }
        }

        return $rows;
    }

    /**
     * @param array<int, array<string, mixed>> $groups
     */
    private function buildNotePdfs(array $groups, string $folderPath, Carbon $timestamp): int
    {
        if ($groups === []) {
            return 0;
        }

        $pdfFolder = $folderPath . DIRECTORY_SEPARATOR . 'nota-pdf';
        File::ensureDirectoryExists($pdfFolder);

        $created = 0;
        $created += $this->buildProductGroupNotePdfs($groups, $pdfFolder, $timestamp);
        $created += $this->buildAdminCreditReceiptPdfs($pdfFolder, $timestamp);
        $created += $this->buildAdminCreditInstallmentPdfs($pdfFolder, $timestamp);
        $created += $this->buildCashierReceiptPdfs($pdfFolder, $timestamp);
        $created += $this->buildCashierInstallmentPdfs($pdfFolder, $timestamp);
        $created += $this->buildReturnReceiptPdfs($pdfFolder, $timestamp);

        return $created;
    }

    /**
     * @param array<int, array<string, mixed>> $groups
     */
    private function buildProductGroupNotePdfs(array $groups, string $pdfFolder, Carbon $timestamp): int
    {
        $created = 0;
        foreach ($groups as $group) {
            $viewData = $this->buildGroupReceiptViewData($group, $timestamp);
            $pdf = Pdf::loadView('admin.product-group-receipt', $viewData)->setPaper('a4', 'landscape');

            $fileName = $this->sanitizeFileName(sprintf(
                'nota %s - %s.pdf',
                $viewData['partNumber'],
                $viewData['partName']
            ));

            $pdf->save($pdfFolder . DIRECTORY_SEPARATOR . $fileName);
            $created++;
        }

        return $created;
    }

    private function buildAdminCreditReceiptPdfs(string $pdfFolder, Carbon $timestamp): int
    {
        if (! Schema::hasTable('product_batches')) {
            return 0;
        }

        $batchSelects = ['id', 'product_id', 'supplier_id', 'stock', 'purchase_price', 'expedition_cost', 'down_payment_amount', 'credit_due_date', 'payment_type', 'credit_days', 'created_at'];
        $batches = ProductBatch::query()
            ->with([
                'product:id,name,barcode,unit',
                'supplier:id,name',
                'creditInstallments.user:id,name',
            ])
            ->get($batchSelects);

        $created = 0;
        foreach ($batches as $batch) {
            [$totalCredit, $downPayment, $installmentPaid, $paid, $remaining] = $this->creditSummaryForBatch($batch);
            $viewData = [
                'batch' => $batch,
                'totalCredit' => $totalCredit,
                'downPayment' => $downPayment,
                'installmentPaid' => $installmentPaid,
                'totalPaid' => $paid,
                'remainingCredit' => $remaining,
                'paymentHistory' => $this->buildCreditPaymentHistory($batch, $downPayment),
                'printedAt' => $timestamp,
                'pdf' => true,
            ];

            $pdf = Pdf::loadView('admin.credit-receipt', $viewData)->setPaper('a4', 'portrait');
            $fileName = $this->sanitizeFileName(sprintf(
                'nota kredit %s - %s - batch %d.pdf',
                strtoupper((string) ($batch->product?->barcode ?? 'batch-' . $batch->id)),
                strtoupper((string) ($batch->product?->name ?? '')),
                (int) $batch->id
            ));
            $pdf->save($pdfFolder . DIRECTORY_SEPARATOR . $fileName);
            $created++;
        }

        return $created;
    }

    private function buildAdminCreditInstallmentPdfs(string $pdfFolder, Carbon $timestamp): int
    {
        if (! Schema::hasTable('credit_installments')) {
            return 0;
        }

        $installments = CreditInstallment::query()
            ->with([
                'user:id,name',
                'productBatch.product:id,name,barcode,unit',
                'productBatch.supplier:id,name',
            ])
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        $created = 0;
        foreach ($installments as $installment) {
            $batch = $installment->productBatch;
            if (! $batch) {
                continue;
            }

            [$totalCredit, $downPayment, $installmentPaid, $paid, $remaining] = $this->creditSummaryForBatch($batch);
            $allInstallments = $batch->creditInstallments()
                ->with('user:id,name')
                ->orderBy('paid_at')
                ->orderBy('id')
                ->get();

            $viewData = [
                'batch' => $batch,
                'installment' => $installment,
                'allInstallments' => $allInstallments,
                'paymentHistory' => $this->buildCreditPaymentHistory($batch, $downPayment),
                'installmentNumber' => max(1, $allInstallments->search(fn ($row) => (int) $row->id === (int) $installment->id) + 1),
                'totalCredit' => $totalCredit,
                'downPayment' => $downPayment,
                'installmentPaid' => $installmentPaid,
                'totalPaid' => $paid,
                'remainingCredit' => $remaining,
                'printedAt' => $timestamp,
                'pdf' => true,
            ];

            $pdf = Pdf::loadView('admin.credit-installment-receipt', $viewData)->setPaper('a4', 'portrait');
            $fileName = $this->sanitizeFileName(sprintf(
                'nota cicilan kredit %s - %s - batch %d - cicilan %02d.pdf',
                strtoupper((string) ($batch->product?->barcode ?? 'batch-' . $batch->id)),
                strtoupper((string) ($batch->product?->name ?? '')),
                (int) $batch->id,
                (int) $viewData['installmentNumber']
            ));
            $pdf->save($pdfFolder . DIRECTORY_SEPARATOR . $fileName);
            $created++;
        }

        return $created;
    }

    private function buildCashierReceiptPdfs(string $pdfFolder, Carbon $timestamp): int
    {
        if (! Schema::hasTable('sales')) {
            return 0;
        }

        $sales = Sale::query()
            ->with([
                'items.product',
                'user:id,name',
                'returns.items.replacementBatch.product',
                'installments.user:id,name',
            ])
            ->when(Schema::hasColumn('sales', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $created = 0;
        foreach ($sales as $sale) {
            $viewData = [
                'sale' => $sale,
                'storeName' => config('app.name', 'Toko Pak Paul'),
                'historyUrl' => route('cashier.history'),
                'newTransactionUrl' => route('cashier.dashboard'),
                'pdf' => true,
            ];

            $pdf = Pdf::loadView('cashier.receipt', $viewData)->setPaper('a4', 'portrait');
            $fileName = $this->sanitizeFileName('nota ' . ($sale->invoice_number ?: ('sale-' . $sale->id)) . '.pdf');
            $pdf->save($pdfFolder . DIRECTORY_SEPARATOR . $fileName);
            $created++;
        }

        return $created;
    }

    private function buildCashierInstallmentPdfs(string $pdfFolder, Carbon $timestamp): int
    {
        if (! Schema::hasTable('sale_installments')) {
            return 0;
        }

        $installments = SaleInstallment::query()
            ->with([
                'sale.user:id,name',
                'sale.items.product',
                'sale.returns.items.replacementBatch.product',
                'sale.installments.user:id,name',
                'user:id,name',
            ])
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        $created = 0;
        foreach ($installments as $installment) {
            $sale = $installment->sale;
            if (! $sale) {
                continue;
            }

            $sale->setRelation('installments', $sale->installments->sortBy([['paid_at', 'asc'], ['id', 'asc']])->values());
            $viewData = [
                'sale' => $sale,
                'installment' => $installment,
                'installmentPaid' => (float) $sale->installments->sum('amount'),
                'remainingCredit' => max(0, (float) ($sale->credit_amount ?? 0)),
                'historyUrl' => route('cashier.history'),
                'installmentUrl' => route('cashier.history.installment.form', $sale),
                'pdf' => true,
            ];

            $pdf = Pdf::loadView('cashier.installment-receipt', $viewData)->setPaper('a4', 'portrait');
            $fileName = $this->sanitizeFileName(sprintf(
                'nota cicilan %s - %02d.pdf',
                $sale->invoice_number ?: ('sale-' . $sale->id),
                (int) $installment->id
            ));
            $pdf->save($pdfFolder . DIRECTORY_SEPARATOR . $fileName);
            $created++;
        }

        return $created;
    }

    private function buildReturnReceiptPdfs(string $pdfFolder, Carbon $timestamp): int
    {
        if (! Schema::hasTable('sales_returns')) {
            return 0;
        }

        $returns = SalesReturn::query()
            ->with([
                'sale.user:id,name',
                'sale.items.product',
                'sale.returns.items.replacementBatch.product',
                'sale.installments.user:id,name',
                'user:id,name',
                'items.replacementBatch.product',
            ])
            ->orderBy('returned_at')
            ->orderBy('id')
            ->get();

        $created = 0;
        foreach ($returns as $salesReturn) {
            $viewData = [
                'salesReturn' => $salesReturn,
                'storeName' => config('app.name', 'Toko Pak Paul'),
                'historyUrl' => route('cashier.history'),
                'saleUrl' => route('cashier.receipt', $salesReturn->sale),
                'pdf' => true,
            ];

            $pdf = Pdf::loadView('cashier.return-receipt', $viewData)->setPaper('a4', 'portrait');
            $fileName = $this->sanitizeFileName('nota retur ' . ($salesReturn->return_number ?: ('return-' . $salesReturn->id)) . '.pdf');
            $pdf->save($pdfFolder . DIRECTORY_SEPARATOR . $fileName);
            $created++;
        }

        return $created;
    }

    /**
     * @param array<string, mixed> $group
     * @return array<string, mixed>
     */
    private function buildGroupReceiptViewData(array $group, Carbon $timestamp): array
    {
        $purchaseRows = collect($group['purchase_rows'] ?? []);
        $salesRows = collect($group['sales_rows'] ?? []);
        $purchaseSummary = [
            'count' => (int) ($group['summary']['purchase_batches'] ?? 0),
            'value' => (string) ($group['summary']['purchase_value'] ?? 'Rp 0'),
            'lunas' => (int) ($group['summary']['purchase_lunas'] ?? 0),
            'utang' => (int) ($group['summary']['purchase_kredit'] ?? 0),
        ];
        $salesSummary = [
            'count' => (int) ($group['summary']['sales_invoices'] ?? 0),
            'value' => (string) ($group['summary']['sales_value'] ?? 'Rp 0'),
            'credit' => (int) ($group['summary']['sales_kredit'] ?? 0),
            'lunas' => (int) ($group['summary']['sales_lunas'] ?? 0),
            'retur' => (int) $salesRows->sum(fn (array $row) => (int) ($row['return_count'] ?? 0)),
        ];

        $product = (object) [
            'id' => (int) ($group['product_id'] ?? 0),
            'barcode' => (string) ($group['part_number'] ?? '-'),
            'name' => (string) ($group['part_name'] ?? '-'),
            'category' => (object) ['name' => (string) ($group['category'] ?? '-')],
            'brand' => (object) ['name' => (string) ($group['brand'] ?? '-')],
            'unit' => (string) ($group['unit'] ?? '-'),
            'batches' => collect([(object) [
                'is_active' => true,
                'stock' => (int) ($group['total_stock'] ?? 0),
            ]]),
        ];

        return [
            'storeName' => config('app.name', 'Toko Pak Paul'),
            'partNumber' => (string) ($group['part_number'] ?? '-'),
            'partName' => (string) ($group['part_name'] ?? '-'),
            'product' => $product,
            'purchaseSummary' => $purchaseSummary,
            'salesSummary' => $salesSummary,
            'purchaseMonthGroups' => $group['purchase_month_groups'] ?? [],
            'salesMonthGroups' => $group['sales_month_groups'] ?? [],
            'latestActivityAt' => ! empty($group['last_activity_ts'])
                ? Carbon::createFromTimestamp((int) $group['last_activity_ts'])
                : null,
            'printedAt' => $timestamp,
            'pdf' => true,
        ];
    }

    private function creditSummaryForBatch(ProductBatch $batch): array
    {
        $qty = (int) ($batch->stock ?? 0);
        $expeditionCost = (float) ($batch->expedition_cost ?? 0);
        $totalCredit = max(0, ($qty * (float) ($batch->purchase_price ?? 0)) + $expeditionCost);
        $paymentType = strtoupper((string) ($batch->payment_type ?? 'LUNAS'));
        $downPayment = max(0, (float) ($batch->down_payment_amount ?? 0));
        $installmentPaid = 0.0;
        $hasInstallmentHistory = false;

        if (Schema::hasTable('credit_installments')) {
            $installmentQuery = CreditInstallment::query()
                ->where('product_batch_id', $batch->id);
            $installmentPaid = (float) $installmentQuery->sum('amount');
            $hasInstallmentHistory = $installmentQuery->exists();
        }

        $hasCreditHistory = $downPayment > 0 || $hasInstallmentHistory;

        if ($paymentType !== 'KREDIT' && ! $hasCreditHistory) {
            $downPayment = 0.0;
            $installmentPaid = 0.0;
            $paid = $totalCredit;
            $remaining = 0.0;
        } else {
            $paid = min($totalCredit, $downPayment + $installmentPaid);
            $remaining = max(0, $totalCredit - $paid);
        }

        return [$totalCredit, $downPayment, $installmentPaid, $paid, $remaining];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildCreditPaymentHistory(ProductBatch $batch, float $downPayment): array
    {
        $history = [];
        if ($downPayment > 0) {
            $history[] = [
                'type' => 'DP / Uang Muka',
                'date' => $batch->created_at?->format('d M Y') ?? '-',
                'time' => $batch->created_at?->format('H:i:s') ?? '-',
                'amount' => $downPayment,
                'processed_by' => 'Sistem',
                'note' => 'DP awal pembelian',
            ];
        }

        if ($batch->relationLoaded('creditInstallments') || Schema::hasTable('credit_installments')) {
            foreach (($batch->creditInstallments ?? collect()) as $installment) {
                $history[] = [
                    'type' => 'Cicilan',
                    'date' => $installment->paid_at?->format('d M Y') ?? '-',
                    'time' => $installment->created_at?->format('H:i:s') ?? '-',
                    'amount' => (float) ($installment->amount ?? 0),
                    'processed_by' => $installment->processed_by ?? $installment->user?->name ?? '-',
                    'note' => $installment->note ?: '-',
                ];
            }
        }

        return $history;
    }

    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[<>:"\\/\\\\|?*]+/u', '-', $name) ?? $name;
        $name = preg_replace('/\\s+/u', ' ', $name) ?? $name;
        $name = trim($name, " .-_");

        return Str::limit($name, 180, '');
    }

    private function buildSectionStyle(): array
    {
        return [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEF4F0'],
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'C4D2C7']],
            ],
        ];
    }

    private function styleSheet($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'C7D2CA'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle('A1:Z3')->applyFromArray([
            'font' => ['bold' => true],
        ]);
        $sheet->getStyle('A5:Z5')->applyFromArray($this->headerStyle());
    }

    private function headerStyle(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '006948'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'B7C7BF'],
                ],
            ],
        ];
    }

    private function bodyStyle(): array
    {
        return [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D5DDD8'],
                ],
            ],
        ];
    }

    private function titleStyle(): array
    {
        return [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
    }

    private function setWidths($sheet, array $widths): void
    {
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }

    private function compareDateLabel(string $a, string $b): int
    {
        $parse = fn (string $value): int => $value !== '-' ? (Carbon::createFromFormat('d M Y H:i', $value)->timestamp ?? 0) : 0;
        return $parse($a) <=> $parse($b);
    }
}
