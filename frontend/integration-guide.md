# DVLA PDF Generator Integration Guide

## Overview
The DVLA PDF Generator is a reusable JavaScript class that provides consistent PDF generation and printing functionality across your application.

## Features
- ✅ Fetches complete record data from database
- ✅ Same design as your current print functionality
- ✅ Proper date formatting
- ✅ Professional DVLA branding
- ✅ Reusable across different pages
- ✅ Both PDF download and print functionality
- ✅ Custom filename support
- ✅ Error handling and logging

## Quick Start

### 1. Include the Script
Add this to your HTML head section:
```html
<script src="js/dvla-pdf-generator.js"></script>
```

### 2. Basic Usage

#### Generate PDF
```javascript
// Simple PDF generation
generateVehicleRecordPDF(recordId);

// With custom filename
generateVehicleRecordPDF(recordId, { 
    filename: 'vehicle-record-custom.pdf' 
});
```

#### Print Document
```javascript
// Open print dialog
printVehicleRecord(recordId);
```

## Integration Examples

### In Records Page
Replace your existing savePDF function:
```javascript
// Old way (in edit modal)
async function savePDF() {
    const recordId = document.getElementById('edit_record_id').value;
    await generateVehicleRecordPDF(recordId);
}
```

### In Dashboard
Add PDF buttons to your dashboard:
```html
<button onclick="generateVehicleRecordPDF(${record.id})">
    <span class="material-icons">picture_as_pdf</span>
    Download PDF
</button>

<button onclick="printVehicleRecord(${record.id})">
    <span class="material-icons">print</span>
    Print
</button>
```

### In Admin Panel
Bulk operations:
```javascript
async function generateBulkPDFs(recordIds) {
    for (const id of recordIds) {
        try {
            await generateVehicleRecordPDF(id, {
                filename: `vehicle-record-${id}.pdf`
            });
        } catch (error) {
            console.error(`Failed to generate PDF for record ${id}:`, error);
        }
    }
}
```

## Advanced Usage

### Custom Error Handling
```javascript
try {
    await generateVehicleRecordPDF(recordId);
    showSuccessModal('PDF Generated!', 'Document saved successfully.');
} catch (error) {
    showErrorModal('PDF Failed', error.message);
}
```

### With Loading States
```javascript
async function downloadPDF(recordId) {
    const button = event.target;
    const originalText = button.textContent;
    
    button.textContent = 'Generating...';
    button.disabled = true;
    
    try {
        await generateVehicleRecordPDF(recordId);
    } finally {
        button.textContent = originalText;
        button.disabled = false;
    }
}
```

## API Reference

### DVLAPDFGenerator Class

#### Methods

##### `generateVehicleRecordPDF(recordId, options)`
- **recordId**: Database record ID
- **options**: Object with optional settings
  - `filename`: Custom filename for PDF
  - `print`: Set to true for print mode
- **Returns**: Promise with success/error result

##### `printVehicleRecord(recordId)`
- **recordId**: Database record ID
- **Returns**: Promise that opens print dialog

##### `fetchRecordData(recordId)`
- **recordId**: Database record ID
- **Returns**: Promise with complete record data

## File Structure
```
frontend/
├── js/
│   └── dvla-pdf-generator.js    # Main PDF generator
├── pdf-demo.html                # Demo page
├── integration-guide.md         # This guide
└── records.html                 # Your existing page
```

## Styling Customization

The PDF uses the same styling as your print functionality:
- DVLA branding and colors
- Professional layout
- Proper date formatting
- Official footer information

To customize styling, modify the `generateHTMLContent()` method in the DVLAPDFGenerator class.

## Error Handling

The generator includes comprehensive error handling:
- Network errors when fetching data
- PDF library loading failures
- Invalid record IDs
- Date formatting errors

## Browser Compatibility

- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 12+
- ✅ Edge 79+

## Dependencies

- html2pdf.js (loaded automatically)
- Modern browser with ES6 support

## Troubleshooting

### Common Issues

1. **"Record not found"**
   - Check if the record ID exists in database
   - Verify API endpoint is accessible

2. **"PDF library failed to load"**
   - Check internet connection
   - Verify CDN access

3. **"Invalid date format"**
   - Check database date fields
   - Verify date formatting in API response

### Debug Mode
Enable console logging to see detailed information:
```javascript
// The generator automatically logs to console
// Check browser DevTools → Console tab
```

## Support

For issues or questions:
1. Check browser console for error messages
2. Verify record ID exists in database
3. Test with the demo page first
4. Check network connectivity to API endpoints