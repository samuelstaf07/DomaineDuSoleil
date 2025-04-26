$(document).ready(function() {
    $('.popup-gallery').magnificPopup({
        delegate: 'a:not(.not-image)' +
            ':not(.fc-daygrid-day-number)' +
            ':not(.fc-col-header-cell-cushion)' +
            ':not(.fc-daygrid-event)' +
            ':not(.btn-yellow)',
        type: 'image',
        gallery: {
            enabled: true,
            tCounter: '%curr% sur %total%'
        }
    });

    const reservedDates = window.reservedDates;

    function getAllDates(events) {
        const allDates = new Set();
        events.forEach(event => {
            const start = new Date(event.start);
            const end = new Date(event.end);
            let currentDate = new Date(start);
            while (currentDate <= end) {
                allDates.add(new Date(currentDate));
                currentDate.setDate(currentDate.getDate() + 1);
            }
            allDates.add(new Date(end));
        });
        return Array.from(allDates);
    }

    const allDates = getAllDates(reservedDates);

    const today = new Date();

    const dateStartPicker = MCDatepicker.create({
        el: '#dateStart',
        dateFormat: 'dd/mm/yyyy',
        autoClose: true,
        closeOnBlur: true,
        selectedDate: today,
        minDate: today,
        customCancelBTN: 'ANNULER',
        customClearBTN: 'Réinitialiser',
        customOkBTN: 'CONFIRMER',
        customMonths: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        customWeekDays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        theme: {
            theme_color: '#006633',
        },
        disableDates: allDates
    });


    const dateEndPicker = MCDatepicker.create({
        el: '#dateEnd',
        dateFormat: 'dd/mm/yyyy',
        autoClose: true,
        closeOnBlur: true,
        selectedDate: today,
        minDate: dateStartPicker.getDate() instanceof Date ? dateStartPicker.getDate() : today,
        customCancelBTN: 'ANNULER',
        customClearBTN: 'Réinitialiser',
        customOkBTN: 'CONFIRMER',
        customMonths: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        customWeekDays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        theme: {
            theme_color: '#006633',
        },
        disableDates: allDates
    });

    dateStartPicker.onClose(()=>{
        isGoodDates();
    })

    dateEndPicker.onClose(()=>{
        isGoodDates();
    })

    function isGoodDates(){
        if(dateStartPicker == null ||
            dateEndPicker == null){
            notGood();
        }

        if(dateStartPicker.getFullDate() > dateEndPicker.getFullDate()){
            notGood();
        }else{
            good();
        }

    }

    function notGood(){
        let message = document.querySelector('#dates .alert');

        if(message.classList.contains('d-none')){
            message.classList.remove('d-none');
        }
    }

    function good(){
        let message = document.querySelector('#dates .alert');

        message.classList.add('d-none');
    }
})