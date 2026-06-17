(function () {
  function esc(value) {
    return String(value ?? '-')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function printMonthlyPurchaseNota(button) {
    const monthGroup = JSON.parse(button?.dataset?.printMonthPurchase || '{}');
    const lunasRows = Array.isArray(monthGroup?.lunas) ? monthGroup.lunas : [];
    const utangRows = Array.isArray(monthGroup?.utang) ? monthGroup.utang : [];
    const allRows = [...lunasRows, ...utangRows];

    if (!monthGroup?.month_label || allRows.length === 0) {
      return;
    }

    const rowMarkup = allRows.map((row, index) => `
      <tr>
        <td>${index + 1}</td>
        <td>${esc(row.tanggal)}</td>
        <td>${esc(row.supplier)}</td>
        <td>${esc(row.barang)}</td>
        <td class="num">${esc(row.qty)}</td>
        <td class="num">${esc(row.harga_satuan)}</td>
        <td class="num">${esc(row.total)}</td>
        <td class="num">${esc(row.down_payment || 'Rp 0')}</td>
        <td class="num">${esc(row.sisa_kredit || 'Rp 0')}</td>
        <td>${esc(row.status)}</td>
      </tr>
    `).join('');

    const summary = monthGroup.summary || {};
    const printFrame = document.createElement('iframe');
    printFrame.setAttribute('aria-hidden', 'true');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.opacity = '0';
    printFrame.style.pointerEvents = 'none';
    document.body.appendChild(printFrame);

    const cleanup = () => {
      if (printFrame.parentNode) {
        printFrame.parentNode.removeChild(printFrame);
      }
    };

    const printDocument = printFrame.contentDocument || printFrame.contentWindow?.document;
    if (!printDocument) {
      cleanup();
      return;
    }

    printDocument.open();
    printDocument.write(`
      <!doctype html>
      <html lang="id">
        <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <title>Nota Bulanan ${esc(monthGroup.month_label)}</title>
          <style>
            * { box-sizing: border-box; }
            body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #111827; background: #fff; }
            .page { padding: 24px; }
            .header { display: flex; justify-content: space-between; gap: 16px; border-bottom: 2px solid #0b6b4a; padding-bottom: 16px; margin-bottom: 18px; }
            .title { font-size: 24px; font-weight: 700; margin: 0 0 6px; }
            .muted { color: #4b5563; margin: 2px 0; font-size: 13px; }
            .summary { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-end; align-content: flex-start; }
            .chip { padding: 8px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #f3f4f6; color: #111827; white-space: nowrap; }
            .chip.green { background: #e6fff3; color: #006948; }
            .chip.blue { background: #ececff; color: #4648d4; }
            .chip.amber { background: #fff4dd; color: #a36700; }
            .section { margin-top: 20px; page-break-inside: avoid; }
            .section h2 { font-size: 16px; margin: 0 0 8px; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
            th, td { border: 1px solid #d1d5db; padding: 8px 10px; vertical-align: top; }
            th { background: #f9fafb; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }
            td.num { text-align: right; white-space: nowrap; }
            .footer { margin-top: 18px; font-size: 12px; color: #4b5563; }
            @media print { .page { padding: 0; } .no-print { display: none !important; } }
          </style>
        </head>
        <body>
          <div class="page">
            <div class="header">
              <div>
                <p class="title">Nota Pembelian Bulanan</p>
                <p class="muted">Surya Duta Multindo</p>
                <p class="muted">Periode: ${esc(monthGroup.month_label)}</p>
                <p class="muted">Total transaksi: ${esc(summary.total_transaksi ?? 0)} | Total nilai: ${esc(summary.total_nilai ?? 'Rp 0')}</p>
              </div>
              <div class="summary">
                <span class="chip green">Lunas ${esc(summary.lunas_count ?? 0)}</span>
                <span class="chip amber">Utang ${esc(summary.utang_count ?? 0)}</span>
                <span class="chip blue">Sisa ${esc(summary.total_sisa ?? 'Rp 0')}</span>
              </div>
            </div>

            <div class="section">
              <h2>Daftar Barang Bulan Ini</h2>
              <table>
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Barang</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Total</th>
                    <th>DP</th>
                    <th>Sisa</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  ${rowMarkup}
                </tbody>
              </table>
            </div>

            <div class="footer">Dicetak otomatis dari laporan kredit & utang bulanan.</div>
          </div>
        </body>
      </html>
    `);
    printDocument.close();

    const printWindow = printFrame.contentWindow;
    if (!printWindow) {
      cleanup();
      return;
    }

    const finalize = () => {
      try {
        cleanup();
      } catch (_) {
        // ignore cleanup errors
      }
    };

    printWindow.onafterprint = finalize;
    setTimeout(() => {
      try {
        printWindow.focus();
        printWindow.print();
      } catch (_) {
        finalize();
      }
      setTimeout(finalize, 1000);
    }, 100);
  }

  window.printMonthlyPurchaseNota = printMonthlyPurchaseNota;
})();
