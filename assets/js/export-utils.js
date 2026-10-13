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
        
        // Render Official Digital Stamp & Signature Block
        let finalY = doc.lastAutoTable ? doc.lastAutoTable.finalY + 10 : 150;
        const pageHeight = doc.internal.pageSize.getHeight();

        // If not enough space on current page for validation block (needs ~42mm), add new page
        if (finalY + 45 > pageHeight) {
            doc.addPage();
            finalY = 25;
        }

        // 1. Digital Security Hash Box (Left side)
        doc.setDrawColor(203, 213, 225);
        doc.setFillColor(248, 250, 252);
        doc.roundedRect(14, finalY, 82, 28, 2, 2, 'FD');

        doc.setFontSize(7.5);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 47, 94);
        doc.text('KEABSAHAN DOKUMEN ELEKTRONIK', 18, finalY + 6);

        doc.setFontSize(6.5);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(71, 85, 105);
        doc.text('Dokumen ini sah dan terdaftar pada sistem CEISA 4.0 Hub.', 18, finalY + 11);
        doc.text('ID Laporan: ' + (fileName || 'CIDP') + '-' + Math.floor(100000 + Math.random() * 900000), 18, finalY + 15);
        doc.setFont('courier', 'bold');
        doc.text('SEC-HASH: SHA256-V98A2F' + Math.floor(1000 + Math.random() * 9000), 18, finalY + 20);
        doc.setFont('helvetica', 'normal');
        doc.text('Verifikasi: e-port.conclusion-dryport.co.id/verify', 18, finalY + 24);

        // 2. Official Signature & Digital Stamp (Right side)
        const sigX = pageWidth - 65;
        doc.setFontSize(8);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(71, 85, 105);
        doc.text('DISETUJUI & DIVALIDASI OLEH:', sigX + 22, finalY, { align: 'center' });
        doc.setFontSize(7);
        doc.setFont('helvetica', 'normal');
        doc.text('Kepala Divisi Operasional (Head of Yard)', sigX + 22, finalY + 4, { align: 'center' });

        // Dual-Circle Vector Digital Stamp
        const stampX = sigX + 22;
        const stampY = finalY + 17;
        doc.setDrawColor(0, 47, 94);
        doc.setLineWidth(0.6);
        doc.circle(stampX, stampY, 12, 'S'); // Outer circle
        doc.setLineWidth(0.2);
        doc.setLineDashPattern([1, 1], 0);
        doc.circle(stampX, stampY, 10.5, 'S'); // Dashed middle circle
        doc.setLineDashPattern([], 0);
        doc.circle(stampX, stampY, 7.5, 'S'); // Inner circle

        doc.setFontSize(4.5);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(0, 47, 94);
        doc.text('PT CONCLUSION DRY PORT', stampX, stampY - 4.5, { align: 'center' });
        doc.setFontSize(5.5);
        doc.text('APPROVED', stampX, stampY, { align: 'center' });
        doc.setFontSize(3.8);
        doc.text('★ YARD OPERATIONS ★', stampX, stampY + 3.2, { align: 'center' });
        doc.text(new Date().toLocaleDateString('id-ID'), stampX, stampY + 5.5, { align: 'center' });

        // Signer name & credentials
        doc.setFontSize(8);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(15, 23, 42);
        doc.text('ARMANSYAH MAULANA, S.T.', sigX + 22, finalY + 33, { align: 'center' });
        doc.setFontSize(6);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(100, 116, 139);
        doc.text('Head of Operations • Certified Logistics Master', sigX + 22, finalY + 36.5, { align: 'center' });

        doc.save((fileName || 'CIDP_Report') + '_' + new Date().toISOString().slice(0,10) + '.pdf');
    }
};
