/**
 * DVLA PDF Generator - Reusable PDF generation for vehicle records
 * Usage: Include this file and call generateVehicleRecordPDF(recordId)
 */

class DVLAPDFGenerator {
    constructor() {
        this.apiBaseUrl = 'http://localhost/VIR/backend/api';
        this.isLibraryLoaded = false;
    }

    /**
     * Load html2pdf library if not already loaded
     */
    async loadPDFLibrary() {
        if (typeof html2pdf !== 'undefined') {
            this.isLibraryLoaded = true;
            return Promise.resolve();
        }

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
            script.onload = () => {
                this.isLibraryLoaded = true;
                console.log('✅ html2pdf library loaded successfully');
                resolve();
            };
            script.onerror = () => {
                console.error('❌ Failed to load html2pdf library');
                reject(new Error('Failed to load PDF library'));
            };
            document.head.appendChild(script);
        });
    }

    /**
     * Fetch complete record data from database
     */
    async fetchRecordData(recordId) {
        const response = await fetch(`${this.apiBaseUrl}/get_record.php?id=${recordId}`);
        const result = await response.json();

        if (result.status === 'success' && result.data) {
            return result.data;
        } else {
            throw new Error(result.message || 'Failed to fetch record data');
        }
    }

    /**
     * Format date for display
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';

        try {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        } catch (e) {
            return dateString;
        }
    }

    /**
     * Generate HTML content for PDF
     */
    generateHTMLContent(record) {
        console.log('🔄 Generating HTML content for record:', record);

        let htmlContent = '<!DOCTYPE html><html><head>';
        htmlContent += '<title>Vehicle Ownership Transfer Record</title>';
        htmlContent += '<style>';
        htmlContent += 'body { font-family: Arial, sans-serif; margin: 15px; color: #333; font-size: 12px; line-height: 1.4; }';
        htmlContent += '.print-date { text-align: right; font-size: 10px; color: #666; margin-bottom: 15px; }';
        htmlContent += '.header { text-align: center; border-bottom: 2px solid #16a085; padding-bottom: 15px; margin-bottom: 20px; }';
        htmlContent += '.org-name { font-size: 18px; font-weight: bold; color: #16a085; margin: 5px 0; }';
        htmlContent += '.document-title { font-size: 14px; font-weight: bold; margin-top: 8px; }';

        // Use flexbox instead of CSS Grid for better compatibility
        htmlContent += '.main-content { display: flex; gap: 20px; margin-bottom: 15px; }';
        htmlContent += '.main-content > .section { flex: 1; }';

        htmlContent += '.section { margin-bottom: 15px; }';
        htmlContent += '.section-title { font-size: 13px; font-weight: bold; color: #16a085; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-bottom: 8px; }';

        // Use table layout for better PDF compatibility
        htmlContent += '.info-grid { display: table; width: 100%; }';
        htmlContent += '.info-row { display: table-row; }';
        htmlContent += '.info-item { display: table-cell; padding: 4px 8px 4px 0; vertical-align: top; width: 50%; }';
        htmlContent += '.info-label { font-weight: bold; font-size: 11px; color: #555; display: block; }';
        htmlContent += '.info-value { font-size: 12px; margin-top: 2px; padding-bottom: 3px; border-bottom: 1px dotted #ccc; display: block; }';

        htmlContent += '.vehicle-section { clear: both; width: 100%; }';
        htmlContent += '.vehicle-grid { display: table; width: 100%; }';
        htmlContent += '.vehicle-row { display: table-row; }';
        htmlContent += '.vehicle-item { display: table-cell; padding: 4px 8px 4px 0; vertical-align: top; width: 33.33%; }';

        htmlContent += '.footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }';
        htmlContent += '</style></head><body>';

        // Print date
        htmlContent += '<div class="print-date">';
        htmlContent += 'Generated on: ' + new Date().toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        htmlContent += '</div>';

        // Header
        htmlContent += '<div class="header">';
        htmlContent += '<div class="org-name">DVLA</div>';
        htmlContent += '<div>Driver and Vehicle Licensing Authority</div>';
        htmlContent += '<div class="document-title">Vehicle Ownership Transfer Record</div>';
        htmlContent += '</div>';

        // Main content in two-column layout
        htmlContent += '<div class="main-content">';

        // Left Column - Current Owner
        htmlContent += '<div class="section">';
        htmlContent += '<div class="section-title">Current Owner Information</div>';
        htmlContent += '<div class="info-grid">';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Full Name</div><div class="info-value">' + (record.co_full_name || 'N/A') + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">Contact</div><div class="info-value">' + (record.co_contact || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' + (record.co_email || 'N/A') + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">TIN</div><div class="info-value">' + (record.co_tin || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Postal Address</div><div class="info-value">' + (record.co_postal_address || 'N/A') + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">Residential Address</div><div class="info-value">' + (record.co_residential_address || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '</div></div>';

        // Right Column - Previous Owner
        htmlContent += '<div class="section">';
        htmlContent += '<div class="section-title">Previous Owner Information</div>';
        htmlContent += '<div class="info-grid">';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Full Name</div><div class="info-value">' + (record.po_full_name || 'N/A') + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">Contact</div><div class="info-value">' + (record.po_contact || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' + (record.po_email || 'N/A') + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">TIN</div><div class="info-value">' + (record.po_tin || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Postal Address</div><div class="info-value">' + (record.po_postal_address || 'N/A') + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">Residential Address</div><div class="info-value">' + (record.po_residential_address || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '</div></div>';

        htmlContent += '</div>'; // End main-content

        // Vehicle Information Section (Full Width)
        htmlContent += '<div class="section vehicle-section">';
        htmlContent += '<div class="section-title">Vehicle Information</div>';
        htmlContent += '<div class="vehicle-grid">';
        htmlContent += '<div class="vehicle-row">';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Vehicle Make</div><div class="info-value">' + (record.vehicle_make || 'N/A') + '</div></div>';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Model Name</div><div class="info-value">' + (record.model_name || 'N/A') + '</div></div>';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Chassis Number</div><div class="info-value">' + (record.chassis_number || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="vehicle-row">';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Year</div><div class="info-value">' + (record.year_of_manufacture || 'N/A') + '</div></div>';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Body Type</div><div class="info-value">' + (record.body_type || 'N/A') + '</div></div>';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Color</div><div class="info-value">' + (record.color || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="vehicle-row">';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Vehicle Use</div><div class="info-value">' + (record.vehicle_use || 'N/A') + '</div></div>';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Fuel Type</div><div class="info-value">' + (record.fuel_type || 'N/A') + '</div></div>';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Cubic Capacity</div><div class="info-value">' + (record.cubic_capacity || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="vehicle-row">';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Engine Number</div><div class="info-value">' + (record.engine_number || 'N/A') + '</div></div>';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Cylinders</div><div class="info-value">' + (record.number_of_cylinders || 'N/A') + '</div></div>';
        htmlContent += '<div class="vehicle-item"><div class="info-label">Vehicle Number</div><div class="info-value">' + (record.vehicle_number || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '</div></div>';

        // Transfer Information Section
        htmlContent += '<div class="section">';
        htmlContent += '<div class="section-title">Transfer Information</div>';
        htmlContent += '<div class="info-grid">';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Transfer Date</div><div class="info-value">' + this.formatDate(record.transfer_date) + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">Record Created</div><div class="info-value">' + this.formatDate(record.created_at) + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Last Updated</div><div class="info-value">' + this.formatDate(record.updated_at) + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">Status</div><div class="info-value">' + (record.status || 'Active') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '<div class="info-row">';
        htmlContent += '<div class="info-item"><div class="info-label">Remarks</div><div class="info-value">' + (record.remarks || 'None') + '</div></div>';
        htmlContent += '<div class="info-item"><div class="info-label">Record ID</div><div class="info-value">' + (record.id || 'N/A') + '</div></div>';
        htmlContent += '</div>';
        htmlContent += '</div></div>';

        // Footer
        htmlContent += '<div class="footer">';
        htmlContent += '<p><strong>This is an official document generated by the DVLA Vehicle Registration System.</strong></p>';
        htmlContent += '<p>For verification purposes, please contact DVLA at info@dvla.gov.gh</p>';
        htmlContent += '<p>Document ID: VOR-' + Date.now() + '</p>';
        htmlContent += '<p>© ' + new Date().getFullYear() + ' Driver and Vehicle Licensing Authority. All rights reserved.</p>';
        htmlContent += '</div>';

        htmlContent += '</body></html>';
        return htmlContent;
    }

    /**
     * Generate PDF from record data
     */
    async generatePDF(record, filename = null) {
        console.log('🔄 Starting PDF generation...');
        await this.loadPDFLibrary();

        const htmlContent = this.generateHTMLContent(record);
        console.log('📄 HTML content generated, length:', htmlContent.length);

        // Create temporary element with better visibility for debugging
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlContent;
        tempDiv.style.position = 'fixed';
        tempDiv.style.top = '0';
        tempDiv.style.left = '0';
        tempDiv.style.width = '210mm'; // A4 width
        tempDiv.style.height = 'auto';
        tempDiv.style.zIndex = '-1000';
        tempDiv.style.opacity = '0.01'; // Almost invisible but still rendered
        tempDiv.style.pointerEvents = 'none';
        document.body.appendChild(tempDiv);

        console.log('📄 Temporary element created and added to DOM');

        // PDF options with better settings
        const opt = {
            margin: [0.5, 0.5, 0.5, 0.5],
            filename: filename || `vehicle-record-${record.chassis_number || 'unknown'}-${Date.now()}.pdf`,
            image: {
                type: 'jpeg',
                quality: 0.95
            },
            html2canvas: {
                scale: 1.5,
                useCORS: true,
                allowTaint: true,
                letterRendering: true,
                logging: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait',
                compress: true
            }
        };

        console.log('📄 PDF options configured:', opt);

        try {
            console.log('🔄 Generating PDF with html2pdf...');

            // Wait a bit for DOM to render
            await new Promise(resolve => setTimeout(resolve, 500));

            await html2pdf().set(opt).from(tempDiv).save();
            console.log('✅ PDF generated successfully');

            document.body.removeChild(tempDiv);
            return { success: true, message: 'PDF generated successfully' };
        } catch (error) {
            console.error('❌ PDF generation failed:', error);
            if (document.body.contains(tempDiv)) {
                document.body.removeChild(tempDiv);
            }
            throw error;
        }
    }

    /**
     * Generate PDF for print (opens in new window)
     */
    async generatePrintDocument(record) {
        const htmlContent = this.generateHTMLContent(record);

        const printWindow = window.open('', '_blank', 'width=800,height=600');
        printWindow.document.write(htmlContent);
        printWindow.document.close();
        printWindow.focus();

        // Print after a short delay
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 1000);
    }

    /**
     * Test HTML content generation (for debugging)
     */
    async testHTMLGeneration(recordId) {
        try {
            const record = await this.fetchRecordData(recordId);
            const htmlContent = this.generateHTMLContent(record);

            // Open HTML in new window for inspection
            const testWindow = window.open('', '_blank', 'width=800,height=600');
            testWindow.document.write(htmlContent);
            testWindow.document.close();

            console.log('✅ HTML test window opened');
            return { success: true, message: 'HTML test window opened' };
        } catch (error) {
            console.error('❌ HTML test failed:', error);
            throw error;
        }
    }

    /**
     * Main function to generate PDF by record ID
     */
    async generateVehicleRecordPDF(recordId, options = {}) {
        try {
            console.log('🔄 Generating PDF for record:', recordId);

            // Fetch record data
            const record = await this.fetchRecordData(recordId);
            console.log('✅ Record data fetched:', record);

            // Generate PDF or print
            if (options.print) {
                await this.generatePrintDocument(record);
                return { success: true, message: 'Print document opened' };
            } else {
                return await this.generatePDF(record, options.filename);
            }
        } catch (error) {
            console.error('❌ PDF generation failed:', error);
            throw error;
        }
    }
}

// Create global instance
window.DVLAPDFGenerator = new DVLAPDFGenerator();

// Convenience functions
window.generateVehicleRecordPDF = (recordId, options = {}) => {
    return window.DVLAPDFGenerator.generateVehicleRecordPDF(recordId, options);
};

window.printVehicleRecord = (recordId) => {
    return window.DVLAPDFGenerator.generateVehicleRecordPDF(recordId, { print: true });
};

window.testVehicleRecordHTML = (recordId) => {
    return window.DVLAPDFGenerator.testHTMLGeneration(recordId);
};