<!-- View Driver Details Modal -->
<div class="modal" id="viewDriverModal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Driver Details</h3>
            <button class="modal-close" onclick="closeViewDriverModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="viewDriverContent">
                <!-- Driver details will be populated here -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeViewDriverModal()">Close</button>
            <button type="button" class="btn btn-primary" onclick="openEditDriverFromView()">
                <i class="fas fa-edit"></i> Edit Driver
            </button>
        </div>
    </div>
</div>
