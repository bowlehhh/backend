<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductGroupReportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductGroupCsvExportController extends Controller
{
    public function __invoke(ProductGroupReportService $reportService): StreamedResponse
    {
        $report = $reportService->build();
        $filename = 'kelompok-barang-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            // BOM agar Excel mengenali UTF-8 dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");

            $writeRow = static function (array $row) use ($handle): void {
                fputcsv($handle, $row);
            };

            $writeRow(['SURYA DUTA MULTINDO']);
            $writeRow(['Export Kelompok Barang - per part number, histori pembelian, dan histori penjualan']);
            $writeRow([]);

            $summary = $report['summary'] ?? [];
            $writeRow(['Dicetak', now()->format('d M Y H:i')]);
            $writeRow(['Total Produk', number_format((int) ($summary['total_products'] ?? 0), 0, ',', '.') . ' part number']);
            $writeRow(['Total Pembelian', number_format((int) ($summary['purchase_count'] ?? 0), 0, ',', '.') . ' transaksi']);
            $writeRow(['Total Penjualan', number_format((int) ($summary['sales_count'] ?? 0), 0, ',', '.') . ' transaksi']);
            $writeRow(['Total Nilai Beli', $summary['purchase_value'] ?? 'Rp 0']);
            $writeRow(['Total Nilai Jual', $summary['sales_value'] ?? 'Rp 0']);
            $writeRow(['Pembelian Kredit', number_format((int) ($summary['purchase_kredit'] ?? 0), 0, ',', '.')]);
            $writeRow(['Penjualan Kredit', number_format((int) ($summary['sales_kredit'] ?? 0), 0, ',', '.')]);
            $writeRow([]);

            foreach (($report['groups'] ?? []) as $group) {
                $writeRow(['Part Number', $group['part_number'] ?? '-', 'Nama Barang', $group['part_name'] ?? '-']);
                $writeRow(['Kategori', $group['category'] ?? '-', 'Merek', $group['brand'] ?? '-', 'Unit', $group['unit'] ?? '-', 'Stok Aktif', (string) ((int) ($group['total_stock'] ?? 0))]);
                $writeRow(['Pembelian Admin']);

                foreach (($group['purchase_month_groups'] ?? []) as $monthGroup) {
                    $writeRow(['Bulan', $monthGroup['month_label'] ?? '-', 'Transaksi', (string) ((int) ($monthGroup['summary']['count'] ?? 0)), 'Nilai', $monthGroup['summary']['value'] ?? 'Rp 0']);
                    $writeRow(['Tanggal', 'Supplier', 'Kondisi', 'Diproses Oleh', 'Qty', 'Harga Beli', 'Total', 'DP', 'Sisa', 'Jatuh Tempo', 'Status']);

                    foreach (($monthGroup['rows'] ?? []) as $row) {
                        $writeRow([
                            $row['tanggal'] ?? '-',
                            $row['supplier'] ?? '-',
                            $row['condition'] ?? '-',
                            $row['processed_by'] ?? '-',
                            (string) ((int) ($row['qty'] ?? 0)),
                            $row['harga_beli'] ?? 'Rp 0',
                            $row['total'] ?? 'Rp 0',
                            $row['down_payment'] ?? 'Rp 0',
                            $row['sisa_kredit'] ?? 'Rp 0',
                            $row['jatuh_tempo'] ?? '-',
                            $row['status'] ?? '-',
                        ]);
                    }
                }

                $writeRow(['Transaksi Admin']);

                foreach (($group['sales_month_groups'] ?? []) as $monthGroup) {
                    $writeRow(['Bulan', $monthGroup['month_label'] ?? '-', 'Transaksi', (string) ((int) ($monthGroup['summary']['count'] ?? 0)), 'Nilai', $monthGroup['summary']['value'] ?? 'Rp 0']);
                    $writeRow(['Tanggal', 'Invoice', 'Pembeli / PT', 'Admin', 'Metode', 'Qty', 'Total', 'Kredit', 'Jatuh Tempo', 'Status', 'Retur']);

                    foreach (($monthGroup['rows'] ?? []) as $row) {
                        $writeRow([
                            $row['tanggal'] ?? '-',
                            $row['invoice'] ?? '-',
                            $row['customer'] ?? '-',
                            $row['cashier'] ?? '-',
                            $row['payment_method'] ?? '-',
                            (string) ((int) ($row['qty'] ?? 0)),
                            $row['total'] ?? 'Rp 0',
                            $row['kredit'] ?? 'Rp 0',
                            $row['jatuh_tempo'] ?? '-',
                            $row['status'] ?? '-',
                            (string) ((int) ($row['return_count'] ?? 0)),
                        ]);
                    }
                }

                $writeRow([]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
