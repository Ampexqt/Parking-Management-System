<!-- Edit Driver Modal -->
<div class="modal" id="editDriverModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Driver</h3>
            <button class="modal-close" onclick="closeEditDriverModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="editDriverForm">
                <input type="hidden" id="editDriverId" name="user_id">
                <div class="form-group">
                    <label for="editDriverFirstName">First Name *</label>
                    <input type="text" id="editDriverFirstName" name="first_name" required maxlength="75">
                    <small class="form-help">Driver's first name</small>
                </div>
                <div class="form-group">
                    <label for="editDriverLastName">Last Name *</label>
                    <input type="text" id="editDriverLastName" name="last_name" required maxlength="75">
                    <small class="form-help">Driver's last name</small>
                </div>
                <div class="form-group">
                    <label for="editDriverEmail">Email *</label>
                    <input type="email" id="editDriverEmail" name="email" required maxlength="150">
                    <small class="form-help">Driver's email address</small>
                </div>
                <div class="form-group">
                    <label for="editDriverStatus">Status *</label>
                    <select id="editDriverStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <small class="form-help">Current status of this driver</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeEditDriverModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="updateDriver()">
                <i class="fas fa-save"></i> Update Driver
            </button>
        </div>
    </div>
</div>
