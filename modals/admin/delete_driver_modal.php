<!-- Delete Driver Confirmation Modal -->
<div class="modal" id="deleteDriverModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Delete Driver</h3>
            <button class="modal-close" onclick="closeDeleteDriverModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="delete-warning">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="warning-content">
                    <h4>Are you sure you want to delete this driver?</h4>
                    <p>This action cannot be undone. The following driver will be permanently removed:</p>
                    <div class="driver-to-delete" id="driverToDelete">
                        <!-- Driver info will be populated here -->
                    </div>
                    <div class="warning-note">
                        <i class="fas fa-info-circle"></i>
                        <span>If this driver has active parking sessions, deletion will be prevented for safety.</span>
                    </div>
                    <div class="warning-note">
                        <i class="fas fa-warning"></i>
                        <span>This will also delete all associated vehicles and parking history.</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeDeleteDriverModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="confirmDeleteDriver()">
                <i class="fas fa-trash"></i> Delete Driver
            </button>
        </div>
    </div>
</div>
