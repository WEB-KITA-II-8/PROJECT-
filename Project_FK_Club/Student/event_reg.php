<?php
// =============================================
// FILE: student/event_reg.php
// =============================================
session_start();

$mock_registered_events = [
    [
        'registration_id' => 'REG-2026-0041',
        'event_name' => 'Lakeside Theater Production (Scene Teater Tepi Tasik)',
        'event_date' => '2026-05-25',
        'event_location' => 'Main Auditorium, Campus A',
        'status' => 'confirmed'
    ],
    [
        'registration_id' => 'REG-2026-0089',
        'event_name' => 'Annual Campus Softball Friendly Match',
        'event_date' => '2026-06-15',
        'event_location' => 'Sports Complex Field',
        'status' => 'confirmed'
    ],
    [
        'registration_id' => 'REG-2026-0112',
        'event_name' => 'Outdoor Camping & Survival Briefing Session',
        'event_date' => '2026-07-02',
        'event_location' => 'Seminar Room 3, Block B',
        'status' => 'pending'
    ]
];
?>

<title>Event Registration</title>

<?php include('../Includes/header_stud.php'); ?>
<?php include('../Includes/sidebar_stud.php'); ?>


<div class="main-content">
    <h1>My Registered Events</h1>
    <p style="color: #64748b; margin-top: -5px;">View your upcoming entries, confirmation slips, and digital check-in vouchers.</p>

    <div class="events-view-container">
        <div class="events-grid">
            <?php foreach ($mock_registered_events as $event): 
                $reg_id = $event['registration_id'];
                $title = $event['event_name'];
                $date = date("F d, Y", strtotime($event['event_date']));
                $venue = $event['event_location'];
                $status = $event['status'];
                
                $is_confirmed = ($status === 'confirmed');
                $badge_class = $is_confirmed ? 'badge-confirmed' : 'badge-pending';
                $display_status = $is_confirmed ? 'Confirmed' : 'Pending Approval';
            ?>
                
                <div class="custom-event-card">
                    <div class="card-banner">
                        <i class="bi bi-image-fill banner-icon"></i>
                        <span class="card-banner-overlay-badge <?php echo $badge_class; ?>">
                            <?php echo $display_status; ?>
                        </span>
                    </div>
                    <div class="card-details-wrapper">
                        <div class="event-timestamp">
                            <i class="bi bi-calendar-event"></i> <?php echo $date; ?>
                        </div>
                        <h3 class="event-heading-title"><?php echo $title; ?></h3>
                        <p class="event-location-text">
                            <i class="bi bi-geo-alt-fill" style="color: #ef4444;"></i> <?php echo $venue; ?>
                        </p>
                        
                        <div class="card-action-bar">
                            <?php if ($is_confirmed): ?>
                                <button class="btn-view-ticket" onclick="showTicket('<?php echo $reg_id; ?>', '<?php echo addslashes($title); ?>', '<?php echo $date; ?>', '<?php echo addslashes($venue); ?>')">
                                    <i class="bi bi-qr-code"></i> View Ticket
                                </button>
                            <?php else: ?>
                                <button class="btn-view-ticket" disabled>
                                    <i class="bi bi-hourglass-split"></i> Processing
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn-cancel-reg" onclick="alert('Cancellation initiated for <?php echo $reg_id; ?>')">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="ticket-modal-overlay" id="ticketModal">
    <div class="ticket-modal-box">
        <button class="modal-close-trigger" onclick="closeTicket()"><i class="bi bi-x-lg"></i></button>
        <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; letter-spacing: 1px;">DIGITAL INLET PASS</span>
        <h2 id="m-title" style="margin: 10px 0 5px 0; font-size: 1.3rem; color: #1e293b;">Event Title</h2>
        <p id="m-reg-id" style="font-size: 0.85rem; color: #2d5fd3; font-weight: 600; margin-bottom: 15px;">REG-ID</p>
        
        <div class="qr-placeholder-frame">
            <img id="m-qr" src="" alt="Live Scanner QR Pass">
        </div>
        
        <div style="background: #f8fafc; border-radius: 8px; padding: 12px; font-size: 0.85rem; color: #475569; text-align: left; line-height: 1.6;">
            <div><i class="bi bi-calendar3" style="margin-right: 8px;"></i> <span id="m-date">Date</span></div>
            <div style="margin-top: 4px;"><i class="bi bi-geo-alt" style="margin-right: 8px;"></i> <span id="m-venue">Location</span></div>
        </div>
    </div>
</div>

<script>
function toggleDropdown() {
    document.getElementById("profileDropdown").classList.toggle("show");
}

window.onclick = function(event) {
    if (!event.target.closest('.profile-menu')) {
        const d = document.getElementById("profileDropdown");
        if (d) d.classList.remove("show");
    }
};

// Functions to open the modal pass window and reveal the QR Code graphic securely
function showTicket(regId, title, date, venue) {
    document.getElementById('m-title').innerText = title;
    document.getElementById('m-reg-id').innerText = "TICKET SLIP ID: " + regId;
    document.getElementById('m-date').innerText = date;
    document.getElementById('m-venue').innerText = venue;
    
    // Generates a real visual QR code inside your app automatically
    document.getElementById('m-qr').src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + encodeURIComponent(regId);
    
    document.getElementById('ticketModal').classList.add('active');
}

function closeTicket() {
    document.getElementById('ticketModal').classList.remove('active');
}
</script>
</body>
</html>