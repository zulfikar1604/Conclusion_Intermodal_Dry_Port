// =============================================================================
// CIDP Export Utilities — SheetJS (Excel) & jsPDF (PDF) Enterprise Reporting
// Conclusion Intermodal Dry Port — Yard Management System
// =============================================================================

const CIDPExport = {
    // Company info for letterhead
    company: {
        name: 'CONCLUSION SUPPLY CHAIN CONSULTANT',
        subtitle: 'PT Multi Terminal Indonesia — Kawasan Industri Jababeka, Cikarang',
        phone: '+62 21 8911 2345',
        email: 'ops@conclusion-dryport.co.id',
        web: 'conclusion-intermodal-dry-port.odoo.com'
    },

    /**
     * Export table data to Excel (.xlsx)
     * @param {Array} headers - Column header names
     * @param {Array} rows - Array of row arrays
     * @param {string} sheetName - Excel sheet name
     * @param {string} fileName - Output file name
     */
    toExcel: function(headers, rows, sheetName, fileName) {
        if (typeof XLSX === 'undefined') {
            alert('Library SheetJS belum dimuat. Pastikan koneksi internet aktif.');
            return;
        }
        const data = [headers, ...rows];
        const ws = XLSX.utils.aoa_to_sheet(data);
        
        // Auto-width columns
        const colWidths = headers.map((h, i) => {
            const maxLen = Math.max(h.length, ...rows.map(r => String(r[i] || '').length));
            return { wch: Math.min(maxLen + 2, 40) };
        });
        ws['!cols'] = colWidths;
        
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, sheetName || 'Data');
        XLSX.writeFile(wb, (fileName || 'CIDP_Export') + '_' + new Date().toISOString().slice(0,10) + '.xlsx');
    },

    /**
     * Export table data to PDF with professional letterhead
     * @param {string} title - Report title
     * @param {Array} headers - Column header names  
     * @param {Array} rows - Array of row arrays
     * @param {string} fileName - Output file name
     * @param {string} orientation - 'portrait' or 'landscape'
     */
    toPDF: function(title, headers, rows, fileName, orientation) {
        if (typeof jspdf === 'undefined' && typeof window.jspdf === 'undefined') {
            alert('Library jsPDF belum dimuat. Pastikan koneksi internet aktif.');
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: orientation || 'landscape', unit: 'mm', format: 'a4' });
        const pageWidth = doc.internal.pageSize.getWidth();
        
        // Header band
        doc.setFillColor(0, 47, 94); // #002f5e
        doc.rect(0, 0, pageWidth, 28, 'F');
        
        // Company name
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.text(this.company.name, 14, 12);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.text(this.company.subtitle, 14, 18);
        doc.text(this.company.phone + ' | ' + this.company.email, 14, 23);
        
        // Date stamp
        doc.setFontSize(8);
        doc.text('Tanggal Cetak: ' + new Date().toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric' }), pageWidth - 14, 12, { align: 'right' });
        doc.text('Yard Management System — CEISA 4.0 Connected', pageWidth - 14, 18, { align: 'right' });
        
        // Report title
        doc.setTextColor(0, 47, 94);
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.text(title || 'Laporan Data Operasional', 14, 38);
        
        // Divider line
        doc.setDrawColor(1, 112, 185); // #0170b9
        doc.setLineWidth(0.5);
        doc.line(14, 41, pageWidth - 14, 41);
        
        // Auto table
        doc.autoTable({
            head: [headers],
            body: rows,
            startY: 45,
            theme: 'grid',
            styles: { fontSize: 7, cellPadding: 2, font: 'helvetica' },
            headStyles: { fillColor: [0, 47, 94], textColor: 255, fontStyle: 'bold', fontSize: 7 },
            alternateRowStyles: { fillColor: [248, 250, 252] },
            margin: { left: 14, right: 14 },
            didDrawPage: function(data) {
                // Footer
                doc.setFontSize(7);
                doc.setTextColor(150);
                doc.text('© 2026 Conclusion Supply Chain Consultant — Dokumen ini digenerate otomatis oleh sistem YMS CIDP', 14, doc.internal.pageSize.getHeight() - 8);
                doc.text('Halaman ' + doc.internal.getCurrentPageInfo().pageNumber, pageWidth - 14, doc.internal.pageSize.getHeight() - 8, { align: 'right' });
            }
        });
        
        doc.save((fileName || 'CIDP_Report') + '_' + new Date().toISOString().slice(0,10) + '.pdf');
    }
};
