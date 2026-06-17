<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductGroupReportService;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductGroupXlsxExportController extends Controller
{
    private const LAST_COL = 'K';

    public function __invoke(ProductGroupReportService $reportService)
    {
        $report = $reportService->build();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kelompok Barang');
        $sheet->setShowGridLines(false);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $this->setColumnWidths($sheet);

        $row = 1;
        $this->writeMergedRow($sheet, $row, self::LAST_COL, ['SURYA DUTA MULTINDO'], $this->styleTitle());
        $this->writeMergedRow($sheet, $row, self::LAST_COL, ['Export Kelompok Barang - per part number, histori pembelian, dan histori penjualan'], $this->styleSubtitle());
        $row++;

        $metaRows = [
            ['Dicetak', now()->format('d M Y H:i')],
            ['Total Produk', number_format((int) ($report['summary']['total_products'] ?? 0), 0, ',', '.') . ' part number'],
            ['Total Pembelian', number_format((int) ($report['summary']['purchase_count'] ?? 0), 0, ',', '.') . ' transaksi'],
            ['Total Cicilan', number_format((int) ($report['summary']['purchase_installment_count'] ?? 0), 0, ',', '.') . ' transaksi'],
            ['Total Penjualan', number_format((int) ($report['summary']['sales_count'] ?? 0), 0, ',', '.') . ' transaksi'],
        ];
        $this->writeKeyValueTable($sheet, $row, $metaRows, 4, $this->styleMetaKey(), $this->styleMetaValue(), true);
        $row++;

        $summaryRows = [
            ['Total Nilai Beli', $report['summary']['purchase_value'] ?? 'Rp 0'],
            ['Total Nilai Cicilan', $report['summary']['purchase_installment_value'] ?? 'Rp 0'],
            ['Total Nilai Jual', $report['summary']['sales_value'] ?? 'Rp 0'],
            ['Pembelian Kredit', number_format((int) ($report['summary']['purchase_kredit'] ?? 0), 0, ',', '.')],
            ['Penjualan Kredit', number_format((int) ($report['summary']['sales_kredit'] ?? 0), 0, ',', '.')],
        ];
        $this->writeKeyValueTable($sheet, $row, $summaryRows, 4, $this->styleSummaryKey(), $this->styleSummaryValue(), false, 2);
        $row += 2;

        foreach (($report['groups'] ?? []) as $group) {
            $this->writeMergedRow(
                $sheet,
                $row,
                self::LAST_COL,
                ['Part Number: ' . $group['part_number'] . ' | ' . $group['part_name']],
                $this->styleSectionHeader()
            );

            $infoHeaders = ['Part Number', 'Kategori', 'Merek', 'Unit', 'Stok Aktif'];
            $infoValues = [
                $group['part_number'],
                $group['category'],
                $group['brand'],
                $group['unit'],
                number_format((int) ($group['total_stock'] ?? 0), 0, ',', '.'),
            ];
            $this->writeRow($sheet, $row, $infoHeaders, $this->styleInfoHeader());
            $this->writeRow($sheet, $row, $infoValues, $this->styleInfoValue());

            $row++;
            $this->writeMergedRow($sheet, $row, self::LAST_COL, ['Pembelian Admin'], $this->styleSubsectionHeader());

            if (! empty($group['purchase_month_groups'])) {
                foreach ($group['purchase_month_groups'] as $monthGroup) {
                    $this->writeMergedRow(
                        $sheet,
                        $row,
                        self::LAST_COL,
                        ['Bulan ' . $monthGroup['month_label'] . ' | ' . ($monthGroup['summary']['count'] ?? 0) . ' transaksi | ' . ($monthGroup['summary']['value'] ?? 'Rp 0')],
                        $this->styleMonthHeader()
                    );

                    $this->writeRow($sheet, $row, [
                        'Tanggal',
                        'Supplier',
                        'Kondisi',
                        'Diproses Oleh',
                        'Qty',
                        'Harga Beli',
                        'Total',
                        'DP',
                        'Sisa',
                        'Jatuh Tempo',
                        'Status',
                    ], $this->styleTableHeader());

                    foreach ($monthGroup['rows'] as $dataRow) {
                        $this->writeRow($sheet, $row, [
                            $dataRow['tanggal'] ?? '-',
                            $dataRow['supplier'] ?? '-',
                            $dataRow['condition'] ?? '-',
                            $dataRow['processed_by'] ?? '-',
                            (int) ($dataRow['qty'] ?? 0),
                            $dataRow['harga_beli'] ?? 'Rp 0',
                            $dataRow['total'] ?? 'Rp 0',
                            $dataRow['down_payment'] ?? 'Rp 0',
                            $dataRow['sisa_kredit'] ?? 'Rp 0',
                            $dataRow['jatuh_tempo'] ?? '-',
                            $dataRow['status'] ?? '-',
                        ], $this->styleTableBody());
                    }
                }
            } else {
                $this->writeMergedRow($sheet, $row, self::LAST_COL, ['Belum ada riwayat pembelian admin untuk part number ini.'], $this->styleEmptyState());
            }

            $row++;
            $this->writeMergedRow($sheet, $row, self::LAST_COL, ['Cicilan Pembelian Admin'], $this->styleSubsectionHeader());

            if (! empty($group['purchase_installment_month_groups'])) {
                foreach ($group['purchase_installment_month_groups'] as $monthGroup) {
                    $this->writeMergedRow(
                        $sheet,
                        $row,
                        self::LAST_COL,
                        ['Bulan ' . $monthGroup['month_label'] . ' | ' . ($monthGroup['summary']['count'] ?? 0) . ' transaksi | ' . ($monthGroup['summary']['value'] ?? 'Rp 0')],
                        $this->styleInstallmentMonthHeader()
                    );

                    $this->writeRow($sheet, $row, [
                        'Tanggal',
                        'Part Number',
                        'Supplier',
                        'Nominal',
                        'Diproses Oleh',
                        'Catatan',
                        'Sisa Setelah Cicilan',
                        'Status',
                    ], $this->styleTableHeader());

                    foreach ($monthGroup['rows'] as $dataRow) {
                        $this->writeRow($sheet, $row, [
                            $dataRow['tanggal'] ?? '-',
                            $dataRow['part_number'] ?? '-',
                            $dataRow['supplier'] ?? '-',
                            $dataRow['nominal'] ?? 'Rp 0',
                            $dataRow['diproses_oleh'] ?? '-',
                            $dataRow['catatan'] ?? '-',
                            $dataRow['sisa_setelah_cicilan'] ?? 'Rp 0',
                            $dataRow['status'] ?? '-',
                        ], $this->styleTableBody());
                    }
                }
            } else {
                $this->writeMergedRow($sheet, $row, self::LAST_COL, ['Belum ada riwayat cicilan pembelian untuk part number ini.'], $this->styleEmptyState());
            }

            $row++;
            $this->writeMergedRow($sheet, $row, self::LAST_COL, ['Transaksi Admin'], $this->styleSubsectionHeader());

            if (! empty($group['sales_month_groups'])) {
                foreach ($group['sales_month_groups'] as $monthGroup) {
                    $this->writeMergedRow(
                        $sheet,
                        $row,
                        self::LAST_COL,
                        ['Bulan ' . $monthGroup['month_label'] . ' | ' . ($monthGroup['summary']['count'] ?? 0) . ' transaksi | ' . ($monthGroup['summary']['value'] ?? 'Rp 0')],
                        $this->styleMonthHeaderSales()
                    );

                    $this->writeRow($sheet, $row, [
                        'Tanggal',
                        'Invoice',
                        'Pembeli / PT',
                        'Admin',
                        'Metode',
                        'Qty',
                        'Total',
                        'Kredit',
                        'Jatuh Tempo',
                        'Status',
                        'Retur',
                    ], $this->styleTableHeader());

                    foreach ($monthGroup['rows'] as $dataRow) {
                        $this->writeRow($sheet, $row, [
                            $dataRow['tanggal'] ?? '-',
                            $dataRow['invoice'] ?? '-',
                            $dataRow['customer'] ?? '-',
                            $dataRow['cashier'] ?? '-',
                            $dataRow['payment_method'] ?? '-',
                            (int) ($dataRow['qty'] ?? 0),
                            $dataRow['total'] ?? 'Rp 0',
                            $dataRow['kredit'] ?? 'Rp 0',
                            $dataRow['jatuh_tempo'] ?? '-',
                            $dataRow['status'] ?? '-',
                            number_format((int) ($dataRow['return_count'] ?? 0), 0, ',', '.'),
                        ], $this->styleTableBody());
                    }
                }
            } else {
                $this->writeMergedRow($sheet, $row, self::LAST_COL, ['Belum ada riwayat penjualan kasir untuk part number ini.'], $this->styleEmptyState());
            }

            $row += 2;
        }

        $this->applyGlobalBorders($sheet, 1, $row);

        $tempPath = tempnam(sys_get_temp_dir(), 'kelompok_barang_');
        $xlsx = new Xlsx($spreadsheet);
        $xlsx->setPreCalculateFormulas(false);
        $xlsx->save($tempPath);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $filename = 'kelompok-barang-' . Carbon::now()->format('Ymd-His') . '.xlsx';

        return response()
            ->download($tempPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    private function setColumnWidths($sheet): void
    {
        $widths = [
            'A' => 18,
            'B' => 22,
            'C' => 16,
            'D' => 18,
            'E' => 10,
            'F' => 16,
            'G' => 16,
            'H' => 16,
            'I' => 16,
            'J' => 16,
            'K' => 14,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
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

        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;
    }

    private function writeMergedRow($sheet, int &$row, string $lastColumn, array $values, array $style = []): void
    {
        $startRow = $row;
        $this->writeRow($sheet, $row, $values, $style);
        $sheet->mergeCells('A' . $startRow . ':' . $lastColumn . $startRow);
    }

    private function writeKeyValueTable(
        $sheet,
        int &$row,
        array $pairs,
        int $columns,
        array $keyStyle,
        array $valueStyle,
        bool $singleRow = true,
        int $gapRows = 1,
    ): void {
        $index = 0;
        $currentRow = $row;
        foreach ($pairs as [$key, $value]) {
            $colIndex = ($index % $columns) * 2 + 1;
            $keyCell = Coordinate::stringFromColumnIndex($colIndex) . $currentRow;
            $valueCell = Coordinate::stringFromColumnIndex($colIndex) . ($currentRow + 1);
            $sheet->setCellValueExplicit($keyCell, (string) $key, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit($valueCell, (string) $value, DataType::TYPE_STRING);
            $sheet->getStyle($keyCell)->applyFromArray($keyStyle);
            $sheet->getStyle($valueCell)->applyFromArray($valueStyle);
            $index++;
        }

        $row += $singleRow ? 2 : 2 + $gapRows;
    }

    private function applyGlobalBorders($sheet, int $startRow, int $endRow): void
    {
        if ($endRow < $startRow) {
            return;
        }

        $sheet->getStyle("A{$startRow}:" . self::LAST_COL . "{$endRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFCBD5E1'));
    }

    private function styleTitle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 18,
                'color' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
    }

    private function styleSubtitle(): array
    {
        return [
            'font' => [
                'italic' => true,
                'size' => 11,
                'color' => ['rgb' => '475569'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
    }

    private function styleMetaKey(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
    }

    private function styleMetaValue(): array
    {
        return [
            'font' => [
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
    }

    private function styleSummaryKey(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEF2FF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
    }

    private function styleSummaryValue(): array
    {
        return [
            'font' => [
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEF2FF'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
    }

    private function styleSectionHeader(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0'],
            ],
        ];
    }

    private function styleSubsectionHeader(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '1E293B'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC'],
            ],
        ];
    }

    private function styleInfoHeader(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '475569'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ];
    }

    private function styleInfoValue(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
        ];
    }

    private function styleMonthHeader(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ECFDF5'],
            ],
        ];
    }

    private function styleInstallmentMonthHeader(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF7ED'],
            ],
        ];
    }

    private function styleMonthHeaderSales(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '0F172A'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EEF2FF'],
            ],
        ];
    }

    private function styleTableHeader(): array
    {
        return [
            'font' => [
                'bold' => true,
                'size' => 9,
                'color' => ['rgb' => '334155'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
    }

    private function styleTableBody(): array
    {
        return [
            'font' => [
                'size' => 9,
                'color' => ['rgb' => '0F172A'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }

    private function styleEmptyState(): array
    {
        return [
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '64748B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC'],
            ],
        ];
    }
}
