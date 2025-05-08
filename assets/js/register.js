document.addEventListener('DOMContentLoaded', () => {
    const todayRegister = new Date();
    const eighteenYearsAgo = new Date();
    eighteenYearsAgo.setFullYear(todayRegister.getFullYear() - 18);

    document.getElementById('birthDate').setAttribute('type', 'text');

    const dateBirth = MCDatepicker.create({
        el: '#birthDate',
        dateFormat: 'yyyy-mm-dd',
        autoClose: true,
        closeOnBlur: true,
        selectedDate: eighteenYearsAgo,
        maxDate: eighteenYearsAgo,
        customCancelBTN: 'ANNULER',
        customClearBTN: 'Réinitialiser',
        customOkBTN: 'CONFIRMER',
        customMonths: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        customWeekDays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        theme: {
            theme_color: '#006633',
        },
    });
});