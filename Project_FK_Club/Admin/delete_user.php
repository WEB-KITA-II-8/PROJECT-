<!-- =============================================
DELETE BUTTON COLUMN
FILE: admin/delete_user.php
============================================= -->
<a href="javascript:void(0);" 
   class="delete-btn"
   onclick="confirmDelete(<?= $row['user_id']; ?>)">
   
   <!-- Trash icon -->
   <i class="fas fa-trash"></i>
</a>

<!-- =============================================
CUSTOM DELETE POPUP MODAL
============================================= -->
<div id="deletePopup" class="popup-overlay" style="display: none;">

    <!-- Popup Box -->
    <div class="delete-popup">

        <!-- Warning Icon -->
        <div class="delete-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <!-- Popup Title -->
        <h2>Delete User?</h2>

        <!-- Popup Description -->
        <p>
            Are you sure you want to permanently delete this user?<br>
            This action cannot be undone.
        </p>

        <!-- Action Buttons -->
        <div class="popup-buttons">

            <!-- Cancel Button -->
            <button type="button"
                    class="cancel-btn"
                    onclick="closePopup()">
                Cancel
            </button>

            <!-- Confirm Delete Button -->
            <a href="#"
               id="deleteConfirmBtn"
               class="delete-btn-popup">
                Delete
            </a>

        </div>
    </div>
</div>

<script>
// =============================================
// FUNCTION: OPEN DELETE POPUP
// =============================================
function confirmDelete(userId) {

    // Display popup modal
    document.getElementById("deletePopup").style.display = "flex";

    // Assign selected user ID to delete button
    document.getElementById("deleteConfirmBtn").href =
        "delete_user.php?id=" + userId;
}

// =============================================
// FUNCTION: CLOSE POPUP
// =============================================
function closePopup() {

    document.getElementById("deletePopup").style.display = "none";

}

// =============================================
// FUNCTION: CLOSE POPUP WHEN CLICK OUTSIDE
// =============================================
window.onclick = function(event) {

    const popup = document.getElementById("deletePopup");

    // If click outside popup box
    if (event.target === popup) {
        closePopup();
    }

};
</script>