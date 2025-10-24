<!-- Export Preview Modal -->
<div id="exportPreviewModal" class="modal">
    <div class="modal-content export-preview-modal">
        <div class="modal-header">
            <h3 class="modal-title">Export Preview</h3>
            <button class="modal-close" onclick="closeExportPreviewModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="export-preview-container">
                <!-- Loading State -->
                <div id="exportPreviewLoading" class="export-loading">
                    <div class="loading-spinner"></div>
                    <p>Generating preview...</p>
                </div>
                
                <!-- Preview Content -->
                <div id="exportPreviewContent" class="export-preview-content" style="display: none;">
                    <!-- Report Header -->
                    <div class="preview-header">
                        <h2 class="preview-title">Parking Management System Earnings Report</h2>
                        <p class="preview-date">Generated on <span id="previewDate"></span></p>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="preview-stats-grid">
                        <div class="preview-stat-card">
                            <div class="preview-stat-value" id="previewTotalEarnings">₱0.00</div>
                            <div class="preview-stat-label">Total Earnings</div>
                        </div>
                        <div class="preview-stat-card">
                            <div class="preview-stat-value" id="previewAverageEarnings">₱0.00</div>
                            <div class="preview-stat-label" id="previewAverageLabel">Average Per Hour</div>
                        </div>
                        <div class="preview-stat-card">
                            <div class="preview-stat-value" id="previewPeakHour">--:--</div>
                            <div class="preview-stat-label" id="previewPeakLabel">Peak Hour</div>
                        </div>
                    </div>
                    
                    <!-- Data Table -->
                    <div class="preview-data-section">
                        <h3 class="preview-section-title">Earnings Data</h3>
                        <div class="preview-table-container">
                            <table class="preview-data-table" id="previewDataTable">
                                <thead>
                                    <tr>
                                        <th id="previewTableHeader">Time</th>
                                        <th>Earnings</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody">
                                    <!-- Data will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Additional Statistics -->
                    <div class="preview-additional-stats">
                        <h3 class="preview-section-title">Additional Statistics</h3>
                        <div class="preview-stats-table">
                            <div class="preview-stat-row">
                                <span class="preview-stat-name">Total Sessions:</span>
                                <span class="preview-stat-value-small" id="previewTotalSessions">0</span>
                            </div>
                            <div class="preview-stat-row">
                                <span class="preview-stat-name">Unique Drivers:</span>
                                <span class="preview-stat-value-small" id="previewUniqueDrivers">0</span>
                            </div>
                            <div class="preview-stat-row">
                                <span class="preview-stat-name">Average Payment:</span>
                                <span class="preview-stat-value-small" id="previewAvgPayment">₱0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Error State -->
                <div id="exportPreviewError" class="export-error" style="display: none;">
                    <div class="error-icon">⚠️</div>
                    <p class="error-message">Failed to generate preview. Please try again.</p>
                    <button class="btn-retry" onclick="generateExportPreview()">Retry</button>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeExportPreviewModal()">Cancel</button>
            <button class="btn btn-primary" id="downloadPdfBtn" onclick="downloadPDF()" disabled>
                <i class="fas fa-download"></i>
                Download PDF
            </button>
        </div>
    </div>
</div>

<style>
/* Export Preview Modal Styles */
.export-preview-modal {
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.export-preview-container {
    min-height: 400px;
}

.export-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 300px;
    color: #666;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.export-preview-content {
    font-family: Arial, sans-serif;
    line-height: 1.6;
}

.preview-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.preview-title {
    color: #2c3e50;
    margin: 0 0 10px 0;
    font-size: 24px;
}

.preview-period {
    color: #3498db;
    font-size: 18px;
    font-weight: 600;
    margin: 5px 0;
}

.preview-date {
    color: #7f8c8d;
    font-size: 14px;
    margin: 5px 0;
}

.preview-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.preview-stat-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.preview-stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 8px;
}

.preview-stat-label {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
}

.preview-data-section {
    margin: 30px 0;
}

.preview-section-title {
    color: #2c3e50;
    font-size: 18px;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
}

.preview-table-container {
    overflow-x: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.preview-data-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.preview-data-table th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 12px;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
}

.preview-data-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #dee2e6;
    color: #495057;
}

.preview-data-table tr:hover {
    background: #f8f9fa;
}

.preview-additional-stats {
    margin: 30px 0;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.preview-stats-table {
    display: grid;
    gap: 12px;
}

.preview-stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.preview-stat-row:last-child {
    border-bottom: none;
}

.preview-stat-name {
    color: #495057;
    font-weight: 500;
}

.preview-stat-value-small {
    color: #2c3e50;
    font-weight: 600;
}

.export-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 300px;
    color: #dc3545;
}

.error-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.error-message {
    margin-bottom: 20px;
    text-align: center;
}

.btn-retry {
    background: #dc3545;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
}

.btn-retry:hover {
    background: #c82333;
}

/* Responsive Design */
@media (max-width: 768px) {
    .export-preview-modal {
        width: 95%;
        margin: 10px;
    }
    
    .preview-stats-grid {
        grid-template-columns: 1fr;
    }
    
    .preview-stat-card {
        padding: 15px;
    }
    
    .preview-stat-value {
        font-size: 24px;
    }
}
</style>
