
$.fn.serializeObject = function() {
    var obj = {};
    $.each(this.serializeArray(), function(_, kv) {
        obj[kv.name] = kv.value;
    });
    return obj;
};

$(document).ready(function () {
    const API_BASE = 'http://localhost:8080'; // Change if needed

    let timeSlots = [];
    let rooms = [];

    let authToken = localStorage.getItem('authToken');


// Show login if no token
    if (!authToken) {
        $('#loginModal').modal({ backdrop: 'static', keyboard: false });
    }

// Login Submit
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        const data = $(this).serializeObject(); // You may need a plugin or write manually

        $.ajax({
            url: 'http://localhost:8080/login',
            method: 'POST',
            contentType: 'application/json',
            JSON.stringify(data),
            success: function (res) {
                authToken = res.token;
                localStorage.setItem('authToken', authToken);
                $('#loginModal').modal('hide');
                setupAuthHeaders();
                alert('Logged in as ' + res.user.username);
            },
            error: function (xhr) {
                alert('Login failed: ' + (xhr.responseJSON?.error || 'Invalid credentials'));
            }
        });
    });

// Add token to all future requests
    function setupAuthHeaders() {
        $.ajaxSetup({
            beforeSend: function (xhr) {
                if (authToken) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + authToken);
                }
            }
        });
    }

// Logout
    function logout() {
        localStorage.removeItem('authToken');
        authToken = null;
        $('#loginModal').modal('show');
    }

    // Load time slots and rooms for dropdowns
    function loadReferenceData() {
        $.getJSON(`${API_BASE}/time-slots?active=true`, function (res) {
            timeSlots = res.timeSlots;
            const $timeSelect = $('#editClassForm [name="time_slot_id"]');
            $timeSelect.empty();
            res.timeSlots.forEach(ts => {
                $timeSelect.append(`<option value="${ts.id}">${ts.label} (${ts.start_time}–${ts.end_time})</option>`);
            });
        });

        $.getJSON(`${API_BASE}/rooms`, function (res) {
            rooms = res.rooms;
            const $roomSelect = $('#editClassForm [name="room_id"]');
            $roomSelect.empty();
            res.rooms.forEach(r => {
                $roomSelect.append(`<option value="${r.id}">${r.id} (Cap: ${r.capacity})</option>`);
            });
        });
    }

    // Generate Schedule
    $('#generateForm').on('submit', function (e) {
        e.preventDefault();
        const term = $(this).find('[name="term"]').val();

        $.ajax({
            url: `${API_BASE}/classes/generate`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ term }),
            success: function (res) {
                $('#generateResult').html(`
          <div class="alert alert-success">
            Generated ${res.classes.length} classes.
            <a href="#" class="btn btn-sm btn-info ms-2" onclick="$('#mainTabs a[href=\"#classes\"]').tab('show')">View</a>
          </div>
        `);
                loadClasses();
            },
            error: function (xhr) {
                $('#generateResult').html(`
          <div class="alert alert-danger">
            Error: ${xhr.responseJSON?.error || 'Unknown error'}
          </div>
        `);
            }
        });
    });

    // Load Classes
    function loadClasses() {
        $.getJSON(`${API_BASE}/classes`, function (res) {
            const $tbody = $('#classesList').empty();
            if (!res.classes || res.classes.length === 0) {
                $tbody.append('<tr><td colspan="6" class="text-center">No classes scheduled</td></tr>');
                return;
            }

            res.classes.forEach(cls => {
                const isAuto = !cls.is_override;
                const editBtn = isAuto
                    ? `<button class="btn btn-sm btn-outline-primary edit-class" data-id="${cls.id}">Edit</button>`
                    : `<span class="badge bg-warning">Manual</span>`;

                $tbody.append(`
          <tr>
            <td>${cls.subject_id}</td>
            <td>${cls.teacher_id}</td>
            <td>${cls.room_id}</td>
            <td>${cls.day}</td>
            <td>${getTimeLabel(cls.time_slot_id)}</td>
            <td>${editBtn}</td>
          </tr>
        `);
            });

            // Attach edit handlers
            $('.edit-class').on('click', function () {
                const id = $(this).data('id');
                $('#editClassModal [name="id"]').val(id);
                $('#editClassModal').modal('show');
            });
        });
    }

    // Get time slot label by ID
    function getTimeLabel(id) {
        const ts = timeSlots.find(t => t.id == id);
        return ts ? `${ts.start_time}–${ts.end_time}` : 'Unknown';
    }

    // Edit Class Modal Submit
    $('#editClassForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serializeArray();
        const data = {};
        formData.forEach(field => { data[field.name] = field.value; });

        const id = data.id;
        delete data.id;

        $.ajax({
            url: `${API_BASE}/classes/${id}`,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function () {
                $('#editClassModal').modal('hide');
                loadClasses();
                alert('Class updated!');
            },
            error: function (xhr) {
                alert('Update failed: ' + (xhr.responseJSON?.error || 'Unknown error'));
            }
        });
    });

    // Add Conflict Exemption
    $('#exemptionForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serializeArray();
        const data = {};
        formData.forEach(field => {
            if (field.value) data[field.name] = field.value;
        });

        $.ajax({
            url: `${API_BASE}/exemptions`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function () {
                loadExemptions();
                $(this)[0].reset();
                alert('Exemption added!');
            }.bind(this),
            error: function (xhr) {
                alert('Failed to add exemption: ' + (xhr.responseJSON?.error || 'Check console'));
            }
        });
    });

    // Load Exemptions
    function loadExemptions() {
        $.getJSON(`${API_BASE}/exemptions`, function (res) {
            const $list = $('#exemptionsList').empty();
            res.exemptions.forEach(ex => {
                const expires = ex.expires_at ? new Date(ex.expires_at).toLocaleString() : 'Never';
                $list.append(`
          <li class="list-group-item">
            <strong>${ex.type}</strong> (${ex.entity_id}) - ${ex.conflict_type}
            <br><small>Reason: ${ex.reason}</small>
            <br><small>Expires: ${expires}</small>
          </li>
        `);
            });
        });
    }

    // Tab change: reload data
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');
        if (target === '#classes') loadClasses();
        if (target === '#exemptions') loadExemptions();
    });

    // Initial load
    loadReferenceData();
    loadClasses();
    loadExemptions();
});