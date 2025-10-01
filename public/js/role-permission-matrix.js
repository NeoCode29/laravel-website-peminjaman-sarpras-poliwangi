// Role Permission Matrix JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Handle individual permission checkbox changes
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const roleId = this.getAttribute('data-role-id');
            const permissionId = this.getAttribute('data-permission-id');
            const granted = this.checked;
            
            // Show loading state
            this.disabled = true;
            
            makeRequest(window.location.origin + '/role-permission-matrix/update-role-permissions', 'POST', {
                role_id: roleId,
                permission_id: permissionId,
                granted: granted
            }).then(function(response) {
                if (response.success) {
                    showToast('success', response.message);
                } else {
                    // Revert checkbox state
                    document.querySelector('.permission-checkbox[data-role-id="' + roleId + '"][data-permission-id="' + permissionId + '"]').checked = !granted;
                    showToast('error', 'Gagal mengupdate permission');
                }
            }).catch(function() {
                // Revert checkbox state
                document.querySelector('.permission-checkbox[data-role-id="' + roleId + '"][data-permission-id="' + permissionId + '"]').checked = !granted;
                showToast('error', 'Terjadi kesalahan saat mengupdate permission');
            }).finally(function() {
                // Re-enable checkbox
                document.querySelector('.permission-checkbox[data-role-id="' + roleId + '"][data-permission-id="' + permissionId + '"]').disabled = false;
            });
        });
    });

    // Handle bulk update form
    const bulkUpdateForm = document.getElementById('bulkUpdateForm');
    if (bulkUpdateForm) {
        bulkUpdateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const roleId = document.getElementById('bulkRoleId').value;
            const permissions = Array.from(document.querySelectorAll('.bulk-permission-checkbox:checked')).map(function(checkbox) {
                return checkbox.value;
            });
            
            if (!roleId) {
                showToast('error', 'Pilih role terlebih dahulu');
                return;
            }
            
            makeRequest(window.location.origin + '/role-permission-matrix/bulk-update-role-permissions', 'POST', {
                role_id: roleId,
                permissions: permissions
            }).then(function(response) {
                showToast('success', response.message || 'Permissions berhasil diupdate');
                // Refresh page to update matrix
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }).catch(function() {
                showToast('error', 'Gagal mengupdate permissions');
            });
        });
    }

    // Handle category checkbox
    document.querySelectorAll('.category-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const category = this.getAttribute('data-category');
            const isChecked = this.checked;
            
            document.querySelectorAll('.bulk-permission-checkbox[data-category="' + category + '"]').forEach(function(permissionCheckbox) {
                permissionCheckbox.checked = isChecked;
            });
        });
    });

    // Load role permissions when role is selected
    const bulkRoleId = document.getElementById('bulkRoleId');
    if (bulkRoleId) {
        bulkRoleId.addEventListener('change', function() {
            loadRolePermissions();
        });
    }
});

function selectAllPermissions() {
    document.querySelectorAll('.bulk-permission-checkbox').forEach(function(checkbox) {
        checkbox.checked = true;
    });
    document.querySelectorAll('.category-checkbox').forEach(function(checkbox) {
        checkbox.checked = true;
    });
}

function clearAllPermissions() {
    document.querySelectorAll('.bulk-permission-checkbox').forEach(function(checkbox) {
        checkbox.checked = false;
    });
    document.querySelectorAll('.category-checkbox').forEach(function(checkbox) {
        checkbox.checked = false;
    });
}

function loadRolePermissions() {
    const roleId = document.getElementById('bulkRoleId').value;
    
    if (!roleId) {
        clearAllPermissions();
        return;
    }
    
    makeRequest(window.location.origin + '/role-permission-matrix/get-role-permissions/' + roleId, 'GET')
        .then(function(response) {
            // Clear all first
            clearAllPermissions();
            
            // Check permissions for this role
            response.permissions.forEach(function(permissionId) {
                const checkbox = document.querySelector('.bulk-permission-checkbox[value="' + permissionId + '"]');
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
            
            // Update category checkboxes
            document.querySelectorAll('.category-checkbox').forEach(function(categoryCheckbox) {
                const category = categoryCheckbox.getAttribute('data-category');
                const categoryPermissions = document.querySelectorAll('.bulk-permission-checkbox[data-category="' + category + '"]');
                const checkedPermissions = Array.from(categoryPermissions).filter(function(checkbox) {
                    return checkbox.checked;
                });
                
                categoryCheckbox.checked = categoryPermissions.length === checkedPermissions.length;
            });
        })
        .catch(function() {
            showToast('error', 'Gagal memuat permissions role');
        });
}
