<?php
session_start();
include('../db_connect.php');

/* =============================================
   SESSION USER INFO
============================================= */
$user_id   = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['full_name'] ?? 'Committee Member';
$committee_role = $_SESSION['committee_role'] ?? 'Committee Member';

/* =============================================
   HANDLE AJAX REQUESTS
============================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    header('Content-Type: application/json');

    $action = $_POST['action'];

    $title = mysqli_real_escape_string($conn, $_POST['event_title']);
    $venue = mysqli_real_escape_string($conn, $_POST['event_venue']);
    $capacity = intval($_POST['event_capacity']);
    $description = mysqli_real_escape_string($conn, $_POST['event_description']);

    $start_datetime = $_POST['event_date_start'] . ' ' . $_POST['event_time_start'];
    $end_datetime   = $_POST['event_date_end'] . ' ' . $_POST['event_time_end'];

    $lat = $_POST['event_latitude'] ?? null;
    $lng = $_POST['event_longitude'] ?? null;

    $contact_name  = mysqli_real_escape_string($conn, $_POST['event_contact_name']);
    $contact_phone = mysqli_real_escape_string($conn, $_POST['event_contact_phone']);
    $contact_email = mysqli_real_escape_string($conn, $_POST['event_contact_email']);

    $image_path = '';

    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === 0) {

        $upload_dir = '../uploads/events/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_name = time() . '_' . basename($_FILES['event_image']['name']);
        $target_file = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['event_image']['tmp_name'], $target_file)) {
            $image_path = 'uploads/events/' . $image_name;
        }
    }

    /* ================= CREATE ================= */
    if ($action === 'create') {

        $sql = "
        INSERT INTO events_comm (
            event_name,
            event_location,
            event_capacity,
            event_description,
            event_start_datetime,
            event_end_datetime,
            event_latitude,
            event_longitude,
            contact_name,
            contact_phone,
            contact_email,
            event_image
        ) VALUES (
            '$title',
            '$venue',
            '$capacity',
            '$description',
            '$start_datetime',
            '$end_datetime',
            '$lat',
            '$lng',
            '$contact_name',
            '$contact_phone',
            '$contact_email',
            '$image_path'
        )";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    /* ================= UPDATE ================= */
    if ($action === 'update') {

        $id = intval($_POST['event_id']);

        $sql = "
        UPDATE events_comm SET
            event_name = '$title',
            event_location = '$venue',
            event_capacity = '$capacity',
            event_description = '$description',
            event_start_datetime = '$start_datetime',
            event_end_datetime = '$end_datetime',
            event_latitude = '$lat',
            event_longitude = '$lng',
            contact_name = '$contact_name',
            contact_phone = '$contact_phone',
            contact_email = '$contact_email'
        WHERE event_id = $id
        ";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    /* ================= DELETE ================= */
    if ($action === 'delete') {

        $id = intval($_POST['event_id']);

        $sql = "DELETE FROM events_comm WHERE event_id = $id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}

/* =============================================
   FETCH EVENTS
============================================= */
$result = mysqli_query($conn, "SELECT * FROM events_comm ORDER BY event_start_datetime DESC");
$events_list = [];

while ($row = mysqli_fetch_assoc($result)) {
    $events_list[] = $row;
}

$total_events = count($events_list);

$upcoming_events = 0;
$ongoing_events = 0;
$completed_events = 0;

$current = date('Y-m-d H:i:s');

foreach ($events_list as $event) {

    if (!empty($event['event_start_datetime']) && !empty($event['event_end_datetime'])) {

        $start = $event['event_start_datetime'];
        $end   = $event['event_end_datetime'];

        if ($current < $start) {
            $upcoming_events++;
        }
        elseif ($current >= $start && $current <= $end) {
            $ongoing_events++;
        }
        else {
            $completed_events++;
        }
    }
}

$total_participants = 0;
?>
    <title>Event Management</title>
    
    <?php include '../Includes/header_comm.php'; ?>
    <!-- Include system sidebar navigation -->
    <?php include '../Includes/sidebar_comm.php'; ?>

    <link rel="stylesheet" href="../CSS_Comm/manage_events.css">

<!-- FIX 2: Layout Parent Flex Boundary wrapper -->
<div id="app-layout-container">

    <!-- MAIN RIGHT CONTENT FRAMEWORK -->
    <div id="main-content-wrapper">

        <!-- =============================================
             TOPBAR
        ============================================= -->
        <div class="topbar">
            <div class="profile-menu">
                <button type="button" class="profile-btn" id="profileButton">
                    <div class="profile-info">
                        <span class="profile-name">
                            <?php echo htmlspecialchars($user_name); ?>
                        </span>
                        <span class="profile-role">
                            <?php echo htmlspecialchars($committee_role); ?>
                        </span>
                    </div>
                    <div class="profile-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                </button>

                <!-- Dropdown -->
                <div class="dropdown-content" id="profileDropdown">
                    <a href="profile_committee.php">
                        <i class="fa-solid fa-user"></i> Manage Profile
                    </a>
                    <a href="../logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- =============================================
             MAIN APP INTERFACE
        ============================================= -->
        <div class="main-content">

            <!-- WELCOME BANNER -->
            <div class="events-banner">
                <div class="events-banner-content">
                    <h1>Event Management 📅</h1>
                    <p>Create, manage, and track all your club events</p>
                </div>
                <div class="events-banner-icon">
                    <i class="fa-solid fa-calendar"></i>
                </div>
            </div>

            <!-- STATISTICS CARDS -->
            <div class="events-stats">

                <div class="event-stat-card">
                    <div class="event-stat-icon stat-total">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div class="event-stat-details">
                        <h4>Total Events</h4>
                        <p><?php echo $total_events; ?></p>
                    </div>
                </div>

                <div class="event-stat-card">
                    <div class="event-stat-icon stat-upcoming">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div class="event-stat-details">
                        <h4>Upcoming Events</h4>
                        <p><?php echo $upcoming_events; ?></p>
                    </div>
                </div>

                <div class="event-stat-card">
                    <div class="event-stat-icon stat-ongoing">
                        <i class="fa-solid fa-play-circle"></i>
                    </div>
                    <div class="event-stat-details">
                        <h4>Ongoing Events</h4>
                        <p><?php echo $ongoing_events; ?></p>
                    </div>
                </div>

                <div class="event-stat-card">
                    <div class="event-stat-icon stat-completed">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <div class="event-stat-details">
                        <h4>Completed Events</h4>
                        <p><?php echo $completed_events; ?></p>
                    </div>
                </div>

                <div class="event-stat-card">
                    <div class="event-stat-icon stat-participants">
                        <i class="fa-solid fa-people-group"></i>
                    </div>
                    <div class="event-stat-details">
                        <h4>Total Participants</h4>
                        <p><?php echo $total_participants; ?></p>
                    </div>
                </div>

            </div>

            <!-- EVENTS DATA INTERFACE -->
            <div class="events-header">
                <h2><i class="fa-solid fa-list"></i> Events List</h2>
                <button class="create-event-btn" onclick="openCreateEventModal()">
                    <i class="fa-solid fa-plus"></i> Create New Event
                </button>
            </div>

            <?php if (count($events_list) > 0) { ?>
                <div class="events-table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Venue</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events_list as $event) { ?>
                                    <?php
                                    $current = date('Y-m-d H:i:s');

                                    $start = $event['event_start_datetime'];
                                    $end   = $event['event_end_datetime'];

                                    if ($current < $start) {
                                        $status = 'Upcoming';
                                        $statusClass = 'status-upcoming';
                                    }
                                    elseif ($current >= $start && $current <= $end) {
                                        $status = 'Ongoing';
                                        $statusClass = 'status-ongoing';
                                    }
                                    else {
                                        $status = 'Completed';
                                        $statusClass = 'status-completed';
                                    }
                                    ?>

                                    <tr data-event-id="<?php echo $event['event_id']; ?>">

                                        <td>
                                            <?php echo htmlspecialchars($event['event_name']); ?>
                                        </td>

                                        <td>
                                            <?php echo date(
                                                'd M Y h:i A',
                                                strtotime($event['event_start_datetime'])
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo date(
                                                'd M Y h:i A',
                                                strtotime($event['event_end_datetime'])
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($event['event_location']); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($event['event_capacity']); ?>
                                        </td>

                                        <td>
                                            <span class="event-status <?php echo $statusClass; ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>

                                        <td class="event-actions">

                                            <button class="action-btn btn-view"
                                                data-id="<?php echo $event['event_id']; ?>">
                                                <i class="fa-solid fa-eye"></i>
                                                View
                                            </button>

                                            <button class="action-btn btn-edit"
                                                data-id="<?php echo $event['event_id']; ?>">
                                                <i class="fa-solid fa-pen"></i>
                                                Edit
                                            </button>

                                            <button class="action-btn btn-delete"
                                                data-id="<?php echo $event['event_id']; ?>">
                                                <i class="fa-solid fa-trash"></i>
                                                Delete
                                            </button>

                                        </td>

                                    </tr>

                                    <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="events-table-card">
                    <div class="empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <h3>No Events Yet</h3>
                        <p>Create your first event to get started</p>
                    </div>
                </div>
            <?php } ?>

        </div> <!-- /.main-content -->
    </div> <!-- /#main-content-wrapper -->
</div> <!-- /#app-layout-container -->

<!-- =============================================
     MODAL LAYOUTS
============================================= -->
<div class="modal-overlay" id="eventModalOverlay" 
     style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; background: rgba(0, 0, 0, 0.5) !important; z-index: 999998 !important; display: none;" 
     onclick="closeCreateEventModal()">
</div>

<div class="modal-dialog" id="createEventModal" 
     style="position: fixed !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; width: 90% !important; max-width: 700px !important; max-height: 90vh !important; overflow-y: auto !important; background: #ffffff !important; border-radius: 20px !important; z-index: 999999 !important; display: none; pointer-events: auto !important; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
    
    <div class="modal-header">
        <h5 class="modal-title">Create New Event</h5>
        <button class="modal-close" type="button" onclick="closeCreateEventModal()">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>

    <div class="modal-body">
        <form id="createEventForm" style="position: relative !important; pointer-events: auto !important;" onclick="event.stopPropagation()">
            <input type="hidden" id="eventIdHidden" name="event_id" value="">

            <div class="form-group">
                <label class="form-label">Event Title</label>
                <input type="text" class="form-control" name="event_title" style="position: relative !important; z-index: 1000000 !important;" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Venue / Location Name</label>
                    <input type="text" class="form-control" name="event_venue" placeholder="e.g. Main Auditorium" style="position: relative !important; z-index: 1000000 !important;" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Capacity</label>
                    <input type="number" class="form-control" name="event_capacity" style="position: relative !important; z-index: 1000000 !important;" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="text" class="form-control" id="eventLatitude" name="event_latitude" readonly required>
                </div>
                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="text" class="form-control" id="eventLongitude" name="event_longitude" readonly required>
                </div>
            </div>

            <div class="form-group">
                <button type="button" class="btn-reset" onclick="setCurrentLocation()" style="width:100%; background:#e8f0fe; color:#0d6efd; margin-bottom: 10px; cursor: pointer;">
                    <i class="fa-solid fa-location-crosshairs"></i> Use My Current Location
                </button>
            </div>

            <div class="form-group">
                <label class="form-label">Pinpoint Location on Map</label>
                <div id="locationMap" style="height: 250px; border-radius: 8px; border: 1px solid #d1d3e2; position: relative !important; z-index: 10;"></div>
                <div class="location-hint">Click anywhere on the map to automatically capture the coordinates.</div>
            </div>

            <input type="hidden" id="eventLocationHidden" name="event_location">

            <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="event_date_start" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="event_date_end" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-control" name="event_time_start" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-control" name="event_time_end" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="event_description" rows="3"></textarea>
                </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Contact Name</label>
                    <input type="text" class="form-control" name="event_contact_name" style="position: relative !important; z-index: 1000000 !important;">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Phone</label>
                    <input type="tel" class="form-control" name="event_contact_phone" style="position: relative !important; z-index: 1000000 !important;">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Contact Email</label>
                <input type="email" class="form-control" name="event_contact_email" style="position: relative !important; z-index: 1000000 !important;">
            </div>

            <div class="form-group">
                <label class="form-label">Event Image</label>
                <input type="file" class="form-control" name="event_image" accept="image/*">
            </div>

        </form>
    </div>

    <div class="modal-footer">
        <button class="btn-reset" type="button" onclick="resetCreateEventForm()">Reset</button>
        <button class="btn-submit" type="button" onclick="submitCreateEvent()">Save Event</button>
    </div>
</div>
<!-- =============================================
     SCRIPT ENGINE RUNNERS
============================================= -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    
    function openCreateEventModal() {
        const overlay = document.getElementById('eventModalOverlay');
        const modal = document.getElementById('createEventModal');

        if (overlay) overlay.style.display = 'block';
        if (modal) modal.style.display = 'block';

        // Initialize map settings if not done yet
        if (!locationMapInitialized) {
            initLocationMap();
        }

        setTimeout(() => {
            if (locationMap) {
                locationMap.invalidateSize();
            }
        }, 300);
    }

    function closeCreateEventModal() {
        const overlay = document.getElementById('eventModalOverlay');
        const modal = document.getElementById('createEventModal');

        if (overlay) overlay.style.display = 'none';
        if (modal) modal.style.display = 'none';
    }

    function resetCreateEventForm() {
        document.getElementById('createEventForm').reset();
        const idField = document.getElementById('eventIdHidden');
        if (idField) idField.value = '';
        document.querySelector('#createEventModal .modal-title').textContent = 'Create New Event';
        document.querySelector('#createEventModal .btn-submit').textContent = 'Create Event';
    }

    // Add this helper function to your script block
    function handleOverlayClick(event) {
        // Only close if the background overlay itself was clicked, not the internal dialog form box
        if (event.target.id === 'eventModalOverlay') {
            closeCreateEventModal();
        }
    }

    const eventsData = <?php echo json_encode(array_values($events_list)); ?>;

    function submitCreateEvent() {
        const form = document.getElementById('createEventForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const eventId = document.getElementById('eventIdHidden').value;
        const action = eventId ? 'update' : 'create';
        formData.append('action', action);

        if (eventId) {
            formData.set('event_id', eventId);
        }

        fetch(window.location.pathname, {
            method: 'POST',
            body: formData
        }).then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(action === 'create' ? 'Event created successfully!' : 'Event updated successfully!');
                closeCreateEventModal();
                resetCreateEventForm();
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unable to save event'));
            }
        }).catch(err => {
            console.error(err);
            alert('Request failed');
        });
    }

    let locationMap;
    let locationMarker;
    let locationMapInitialized = false;

    function setCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const latitude = position.coords.latitude.toFixed(6);
                const longitude = position.coords.longitude.toFixed(6);
                setLocationFromCoords(latitude, longitude);
                if (locationMapInitialized) {
                    locationMap.setView([latitude, longitude], 15);
                }
            },
            function (error) {
                alert('Unable to capture location settings cleanly.');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function setLocationFromCoords(latitude, longitude) {
        document.getElementById('eventLongitude').value = longitude;
        document.getElementById('eventLatitude').value = latitude;
        document.getElementById('eventLocationHidden').value = longitude + ', ' + latitude;

        if (!locationMapInitialized) return;

        if (locationMarker) {
            locationMarker.setLatLng([latitude, longitude]);
        } else {
            locationMarker = L.marker([latitude, longitude], { draggable: true }).addTo(locationMap);
            locationMarker.on('dragend', function (event) {
                const position = event.target.getLatLng();
                setLocationFromCoords(position.lat.toFixed(6), position.lng.toFixed(6));
            });
        }
    }

    function initLocationMap() {
        if (locationMapInitialized) return;

        locationMap = L.map('locationMap').setView([4.2105, 101.9758], 6); // Default set to central Malaysia grid coordinates

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(locationMap);

        locationMap.on('click', function (e) {
            setLocationFromCoords(e.latlng.lat.toFixed(6), e.latlng.lng.toFixed(6));
        });

        locationMapInitialized = true;
    }

    function updateHiddenLocationField() {
        const lat = document.getElementById('eventLatitude').value.trim();
        const lng = document.getElementById('eventLongitude').value.trim();
        if (lng !== '' && lat !== '') {
            document.getElementById('eventLocationHidden').value = lng + ', ' + lat;
        }
    }

    function updateMapFromInputs() {
        const lat = parseFloat(document.getElementById('eventLatitude').value);
        const lng = parseFloat(document.getElementById('eventLongitude').value);

        if (!isNaN(lat) && !isNaN(lng) && locationMapInitialized) {
            setLocationFromCoords(lat.toFixed(6), lng.toFixed(6));
            locationMap.setView([lat, lng], 15);
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const profileBtn = document.getElementById("profileButton");
        const profileDropdown = document.getElementById("profileDropdown");

        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener("click", function (event) {
                event.stopPropagation();
                profileDropdown.classList.toggle("show");
            });

            document.addEventListener("click", function (event) {
                if (!profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.classList.remove("show");
                }
            });
        }

        document.getElementById('eventLatitude').addEventListener('change', function () {
            updateHiddenLocationField();
            updateMapFromInputs();
        });
        document.getElementById('eventLongitude').addEventListener('change', function () {
            updateHiddenLocationField();
            updateMapFromInputs();
        });

        function findEventById(id) {
            return eventsData.find(function (ev) { return Number(ev.event_id) === Number(id); }) || null;
        }

        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const ev = findEventById(id);
                if (!ev) return alert('Event not found');

                document.getElementById('eventIdHidden').value = ev.event_id;
                document.querySelector('input[name="event_title"]').value = ev.event_name || '';
                document.querySelector('input[name="event_venue"]').value = ev.event_location || '';
                document.querySelector('input[name="event_capacity"]').value = ev.event_capacity || '';
                document.querySelector('textarea[name="event_description"]').value = ev.event_description || '';
                document.querySelector('input[name="event_date_start"]').value = ev.event_date || '';
                document.querySelector('input[name="event_contact_name"]').value = ev.contact_name || '';
                document.querySelector('input[name="event_contact_phone"]').value = ev.contact_phone || '';
                document.querySelector('input[name="event_contact_email"]').value = ev.contact_email || '';

                if (ev.event_coords) {
                    const parts = ev.event_coords.split(',');
                    if (parts.length === 2) {
                        document.getElementById('eventLongitude').value = parts[0].trim();
                        document.getElementById('eventLatitude').value = parts[1].trim();
                        updateHiddenLocationField();
                        updateMapFromInputs();
                    }
                }
                document.querySelector('#createEventModal .modal-title').textContent = 'Edit Event';
                document.querySelector('#createEventModal .btn-submit').textContent = 'Save Changes';
                openCreateEventModal();
            });
        });

        document.querySelectorAll('.btn-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                if (!confirm('Are you sure you want to delete this event?')) return;
                const fd = new FormData();
                fd.append('action', 'delete');
                fd.append('event_id', id);
                fetch(window.location.pathname, { method: 'POST', body: fd })
                    .then(r => r.json()).then(data => {
                        if (data.status === 'success') {
                            alert('Event deleted successfully');
                            window.location.reload();
                        } else {
                            alert('Error deleting event: ' + (data.message || 'Unknown'));
                        }
                    }).catch(err => { console.error(err); alert('Request failed'); });
            });
        });

        document.querySelectorAll('.btn-view').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const ev = findEventById(id);
                if (!ev) return alert('Event not found');
                let msg = 'Title: ' + (ev.event_name || '') + '\n';
                msg += 'Date: ' + (ev.event_date || '') + '\n';
                msg += 'Venue: ' + (ev.event_location || '') + '\n';
                msg += 'Capacity: ' + (ev.event_capacity || '') + '\n';
                msg += 'Description: ' + (ev.event_description || '') + '\n';
                msg += 'Contact: ' + (ev.contact_name || '') + ' (' + (ev.contact_phone || '') + ')\n';
                alert(msg);
            });
        });
    });
</script>

<?php include '../Includes/footer.php'; ?>