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
            this.style.opacity = '0.5';
            
            makeRequest(window.location.origin + '/role-permission-matrix/update-role-permissions', 'POST', {
                role_id: roleId,
                permission_id: permissionId,
                granted: granted
            }).then(function(response) {
                if (response.success) {
                    showNotification('Permission berhasil diupdate', 'success');
                } else {
                    // Revert checkbox state
                    const checkbox = document.querySelector('.permission-checkbox[data-role-id="' + roleId + '"][data-permission-id="' + permissionId + '"]');
                    checkbox.checked = !granted;
                    showNotification('Gagal mengupdate permission: ' + (response.message || 'Unknown error'), 'error');
                }
            }).catch(function(error) {
                // Revert checkbox state
                const checkbox = document.querySelector('.permission-checkbox[data-role-id="' + roleId + '"][data-permission-id="' + permissionId + '"]');
                checkbox.checked = !granted;
                showNotification('Terjadi kesalahan saat mengupdate permission', 'error');
                console.error('Error:', error);
            }).finally(function() {
                // Re-enable checkbox
                const checkbox = document.querySelector('.permission-checkbox[data-role-id="' + roleId + '"][data-permission-id="' + permissionId + '"]');
                checkbox.disabled = false;
                checkbox.style.opacity = '1';
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
                alert('Pilih role terlebih dahulu');
                return;
            }
            
            makeRequest(window.location.origin + '/role-permission-matrix/bulk-update-role-permissions', 'POST', {
                role_id: roleId,
                permissions: permissions
            }).then(function(response) {
                alert(response.message || 'Permissions berhasil diupdate');
                // Refresh page to update matrix
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }).catch(function() {
                alert('Gagal mengupdate permissions');
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
            alert('Gagal memuat permissions role');
        });
}

// Make HTTP request
function makeRequest(url, method, data) {
    const options = {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    return fetch(url, options)
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        });
}

// Show notification
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Get notification icon based on type
function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'times-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}
