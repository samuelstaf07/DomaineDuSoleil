$(document).ready(function() {
    const reservedDates = window.reservedDates;
    console.log(reservedDates);
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
        });
        return Array.from(allDates);
    }

    const allDates = getAllDates(reservedDates);

    const today = new Date();
    let newReservedDates = [{
        'start' : null,
        'end' : null,
    }];

    const dateStartPicker = MCDatepicker.create({
        el: '#reservations_rentals_full_date_start',
        dateFormat: 'yyyy-mm-dd',
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
        el: '#reservations_rentals_full_date_end',
        dateFormat: 'yyyy-mm-dd',
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

    function checkBetweenDates(allDatesSelected) {
        for (let i = 0; i < allDatesSelected.length; i++) {
            let firstDate = allDatesSelected[i];
            let day1 = String(firstDate.getDate()).padStart(2, '0');
            let month1 = String(firstDate.getMonth() + 1).padStart(2, '0');
            let year1 = firstDate.getFullYear();
            let formattedFirst = `${day1}-${month1}-${year1}`;

            for (let j = 0; j < allDates.length; j++) {
                let secondDate = allDates[j];
                let day2 = String(secondDate.getDate()).padStart(2, '0');
                let month2 = String(secondDate.getMonth() + 1).padStart(2, '0');
                let year2 = secondDate.getFullYear();
                let formattedSecond = `${day2}-${month2}-${year2}`;

                if (formattedFirst === formattedSecond) {
                    return true;
                }
            }
        }
        return false;
    }


    function isGoodDates(){

        newReservedDates[0].start = dateStartPicker.getFullDate();
        newReservedDates[0].end = dateEndPicker.getFullDate();

        let allDatesSelected = getAllDates(newReservedDates);

        if((dateStartPicker == null || dateEndPicker == null) ||
            (dateStartPicker.getFullDate() > dateEndPicker.getFullDate()) ||
            checkBetweenDates(allDatesSelected)){
            notGood();
        }else{
            good();
        }
    }

    function notGood(){
        let message = document.querySelector('#dates .alert');
        let addToCart = document.querySelector('.add');

        if(message.classList.contains('d-none')){
            message.classList.remove('d-none');
        }

        if(!addToCart.classList.contains('disabledItem')){
            addToCart.classList.add('disabledItem');
        }
    }

    function good(){
        let message = document.querySelector('#dates .alert');
        let addToCart = document.querySelector('.add');

        if(!message.classList.contains('d-none')){
            message.classList.add('d-none');
        }

        if(addToCart.classList.contains('disabledItem')){
            addToCart.classList.remove('disabledItem');
        }
    }
})