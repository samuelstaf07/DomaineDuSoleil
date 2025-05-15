$(document).ready(function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const rawData = calendarEl.dataset.reservations;
    let reservations = [];

    reservations = JSON.parse(rawData);

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: reservations,
    });

    calendar.render();
});
