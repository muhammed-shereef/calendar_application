



<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <style>
        /* Modal styling */
        #eventModal, #addEventModal, #messageModal, #updateTitleModal, #deleteEventModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            background: white;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        #modalOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .modal-button {
            margin: 5px;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .modal-button.delete {
            background: #dc3545;
        }
        .modal-button.cancel {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <div id="calendar"></div>

    <!-- Modal for event actions -->
    <div id="modalOverlay"></div>
    <div id="eventModal">
        <h4 id="eventTitle"></h4>
        <button class="modal-button update">Update</button>
        <button class="modal-button delete">Delete</button>
    </div>

    <!-- Modal for adding events -->
    <div id="addEventModal">
        <h3>Add Event</h3>
        <label for="newEventTitle">Event Title:</label>
        <input type="text" id="newEventTitle" style="width: 100%; padding: 5px; margin: 10px 0;">
        <button id="saveEvent" class="modal-button">Save</button>
        <button id="cancelEvent" class="modal-button delete">Cancel</button>
    </div>

    <!-- Modal for updating title -->
    <div id="updateTitleModal">
        <h3>Update Event Title</h3>
        <label for="updateEventTitle">New Title:</label>
        <input type="text" id="updateEventTitle" style="width: 100%; padding: 5px; margin: 10px 0;">
        <button id="confirmUpdateTitle" class="modal-button">Update</button>
        <button id="cancelUpdateTitle" class="modal-button cancel">Cancel</button>
    </div>

    <!-- Modal for deleting event -->
    <div id="deleteEventModal">
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete this event?</p>
        <button id="confirmDeleteEvent" class="modal-button delete">Delete</button>
        <button id="cancelDeleteEvent" class="modal-button cancel">Cancel</button>
    </div>

    <!-- Modal for success or error messages -->
    <div id="messageModal">
        <p id="messageText"></p>
        <button class="modal-button close">Close</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '/api/events',
        editable: true,
        selectable: true,

        eventTimeFormat: {
    hour: undefined,
    minute: undefined,
    meridiem: false
},
        eventDisplay: 'block',
        // Handle clicking on a date
        dateClick: function(info) {
            $('#addEventModal').show();
            $('#modalOverlay').show();

            $('#saveEvent').off('click').on('click', function() {
                var title = $('#newEventTitle').val();
                if (title) {
                    $.post('/api/events', {
                        title: title,
                        start: info.dateStr,
                        end: info.dateStr
                    }, function() {
                        showMessage('Event added successfully!');
                        calendar.refetchEvents();
                        closeAddModal();
                    }).fail(function() {
                        showMessage('Error adding event', 'error');
                    });
                }
            });

            $('#cancelEvent').off('click').on('click', function() {
                closeAddModal();
            });
        },

                // Handle clicking on an event
                eventClick: function(info) {
                    $('#eventModal').show();
                    $('#modalOverlay').show();
                    $('#eventTitle').text(info.event.title);

                    $('.modal-button.update').off('click').on('click', function() {
                        $('#updateTitleModal').show();
                        $('#eventModal').hide();
                        $('#updateEventTitle').val(info.event.title);

                        $('#confirmUpdateTitle').off('click').on('click', function() {
                            var newTitle = $('#updateEventTitle').val();
                            if (newTitle) {
                                $.ajax({
                                    url: `/api/events/${info.event.id}`,
                                    method: 'PUT',
                                    data: {
                                        title: newTitle,
                                        start: info.event.start.toISOString(),
                                        end: info.event.end ? info.event.end.toISOString() : null,
                                    },
                                    success: function() {
                                        showMessage('Event updated successfully!');
                                        calendar.refetchEvents();
                                        closeUpdateModal();
                                    },
                                    error: function() {
                                        showMessage('Error updating event', 'error');
                                    }
                                });
                            }
                        });

                        $('#cancelUpdateTitle').off('click').on('click', function() {
                            closeUpdateModal();
                        });
                    });

                    $('.modal-button.delete').off('click').on('click', function() {
                        $('#deleteEventModal').show();
                        $('#eventModal').hide();

                        $('#confirmDeleteEvent').off('click').on('click', function() {
                            $.ajax({
                                url: `/api/events/${info.event.id}`,
                                method: 'DELETE',
                                success: function() {
                                    showMessage('Event deleted successfully!');
                                    calendar.refetchEvents();
                                    closeDeleteModal();
                                },
                                error: function() {
                                    showMessage('Error deleting event', 'error');
                                }
                            });
                        });

                        $('#cancelDeleteEvent').off('click').on('click', function() {
                            closeDeleteModal();
                        });
                    });
                },
            });

            calendar.render();

            // Modal management functions
            function closeModal() {
    $('#eventModal').hide();
    $('#modalOverlay').hide();
}

function closeUpdateModal() {
    $('#updateTitleModal').hide();
    $('#modalOverlay').hide();
}

function closeDeleteModal() {
    $('#deleteEventModal').hide();
    $('#modalOverlay').hide();
}

function closeAddModal() {
    $('#addEventModal').hide();
    $('#modalOverlay').hide();
    $('#newEventTitle').val(''); // Clear input field
}

function showMessage(message, type = 'success') {
    $('#messageText').text(message);
    $('#messageModal').show();
    $('#modalOverlay').show();

    if (type === 'error') {
        $('#messageText').css('color', 'red');
    } else {
        $('#messageText').css('color', 'green');
    }

    $('.modal-button.close').off('click').on('click', function() {
        $('#messageModal').hide();
        $('#modalOverlay').hide();
    });
}

// Unified click event for overlay
$('#modalOverlay').on('click', function() {
    closeModal();
    closeUpdateModal();
    closeDeleteModal();
    closeAddModal(); // Ensure add event modal also closes
    $('#messageModal').hide(); // Ensure message modal closes
});

        });
    </script>
</body>
</html>
<?php /**PATH D:\shereef\mechine test\CalendarApp\resources\views/calendar.blade.php ENDPATH**/ ?>