$(document).ready(function() {
    let filtreBase = {
        'shower': false,
        'toilet': false,
        'kitchen': false,
        'fridge': false,
        'heating': false,
        'pets': false,
        'isonpromotion': false,
        'nbSimpleBedMin': 0,
        'nbSimpleBedMax': Infinity,
        'nbDoubleBedMin': 0,
        'nbDoubleBedMax': Infinity,
        'nbPersonne': 0,
        'prixMax': Infinity,
        'triPar' : null,
    };

    let filtres = Object.assign({}, filtreBase);
    let nbLogement = 9;
    let nbLocationFiltre = 0;

    // Click events for filters
    $('.filtre').click(function() {
        filtres[this.dataset.filtre] = !filtres[this.dataset.filtre];

        if (filtres[this.dataset.filtre]) {
            $(this).removeClass('btn-outline-success').addClass('btn-success');
        } else {
            $(this).removeClass('btn-success').addClass('btn-outline-success');
        }

        afficherLocations(nbLogement);
    });

    // Reset filters
    $('.filter-reset').click(function() {
        filtres = Object.assign({}, filtreBase);
        $('.filtre').each(function() {
            if (this.classList.contains('btn-success')) {
                $(this).removeClass('btn-success').addClass('btn-outline-success');
            }
        });

        $('#simple-bed-min').val('');
        $('#simple-bed-max').val('');
        $('#double-bed-min').val('');
        $('#double-bed-max').val('');
        $('#nb-personne').val('');
        $('#prix').val('');

        nbLogement = 9;
        afficherLocations(nbLogement);
    });

    // See more button
    $('.voir-plus').click(function() {
        nbLogement += 9;
        afficherLocations(nbLogement);
    });

    // Number of beds filter
    $('.filtre-nombre').click(function() {
        filtres.nbSimpleBedMin = parseInt($('#simple-bed-min').val()) || 0;
        filtres.nbSimpleBedMax = parseInt($('#simple-bed-max').val()) || Infinity;
        filtres.nbDoubleBedMin = parseInt($('#double-bed-min').val()) || 0;
        filtres.nbDoubleBedMax = parseInt($('#double-bed-max').val()) || Infinity;

        if (filtres.nbSimpleBedMin < 0 ||
            filtres.nbSimpleBedMax < 0 ||
            filtres.nbDoubleBedMin < 0 ||
            filtres.nbDoubleBedMax < 0) {
            alert('Un des champs est inférieur à 0.');
        } else if (filtres.nbSimpleBedMin > filtres.nbSimpleBedMax) {
            alert('Le nombre de lits simples minimum est plus grand que le maximum.');
            $('#simple-bed-min').val('');
            $('#simple-bed-max').val('');
        } else if (filtres.nbDoubleBedMin > filtres.nbDoubleBedMax) {
            alert('Le nombre de lits doubles minimum est plus grand que le maximum.');
            $('#double-bed-min').val('');
            $('#double-bed-max').val('');
        } else {
            afficherLocations(nbLogement);
        }
    });

    // Price filter
    $('.filtre-prix').click(function(){
      filtres.prixMax = parseInt($('#prix').val()) || Infinity;

      if(filtres.prixMax < 0){
          alert('Le prix demandé est inférieur à 0');
          $('#prix').val('');
      }else{
          afficherLocations(nbLogement);
      }
    })

    // Number of people filter
    $('.filtre-personne').click(function() {
        filtres.nbPersonne = parseInt($('#nb-personne').val()) || Infinity;
        if (filtres.nbPersonne < 0) {
            alert('Le nombre de personne recherché est inférieur à 0.');
        }
        afficherLocations(nbLogement);
    });

    // Sort rentals
    $('#triLogements').change(function() {
        filtres.triPar = $(this).val();

        afficherLocations(nbLogement);
    });

    // Function to display filtered locations
    function afficherLocations(nbLocation) {
        let i = 0;
        nbLocationFiltre = 0;
        let logements = $('.logement').get();

        if (filtres.triPar) {
            logements.sort(function(a, b) {
                let aValue = $(a).data(filtres.triPar.split('_')[0]); // Récupère le nom du critère sans asc/desc
                let bValue = $(b).data(filtres.triPar.split('_')[0]);
                let ordre = filtres.triPar.split('_')[1]; // Récupère "asc" ou "desc"

                if (filtres.triPar.startsWith('prix') || filtres.triPar.startsWith('nbpersonne') || filtres.triPar.startsWith('nbsimplebed') || filtres.triPar.startsWith('nbdoublebed')) {
                    if (ordre === 'asc') {
                        return parseFloat(aValue) - parseFloat(bValue);
                    } else {
                        return parseFloat(bValue) - parseFloat(aValue);
                    }
                }
            });

            $('#logementsContainer').empty();
            $.each(logements, function(index, logement) {
                $('#logementsContainer').append(logement);
            });
        }

        $('.logement').each(function() {
            let afficher = true;

            if (filtres.shower && this.dataset.shower === "false") {
                afficher = false;
            }

            if (filtres.toilet && this.dataset.toilet === "false") {
                afficher = false;
            }

            if (filtres.kitchen && this.dataset.kitchen === "false") {
                afficher = false;
            }

            if (filtres.fridge && this.dataset.fridge === "false") {
                afficher = false;
            }

            if (filtres.heating && this.dataset.heating === "false") {
                afficher = false;
            }

            if (filtres.pets && this.dataset.pets === "false") {
                afficher = false;
            }

            if (filtres.isonpromotion && this.dataset.isonpromotion === "false") {
                afficher = false;
            }

            if (filtres.nbSimpleBedMin > Number(this.dataset.nbsimplebed)) {
                afficher = false;
            }

            if (filtres.nbSimpleBedMax < Number(this.dataset.nbsimplebed)) {
                afficher = false;
            }

            if (filtres.nbDoubleBedMin > Number(this.dataset.nbdoublebed)) {
                afficher = false;
            }

            if (filtres.nbDoubleBedMax < Number(this.dataset.nbdoublebed)) {
                afficher = false;
            }

            if (filtres.nbPersonne > Number(this.dataset.nbpersonne)) {
                afficher = false;
            }

            if (filtres.prixMax < parseFloat(this.dataset.prix)){
                afficher = false;
            }

            if (afficher) {
                nbLocationFiltre++;
                if (i < nbLocation) {
                    $(this).show();
                    i++;
                } else {
                    $(this).hide();
                }
            } else {
                $(this).hide();
            }
        });

        if (nbLocationFiltre === 0) {
            if ($('#logementsContainer p.alertRentals').length === 0) {
                $('#logementsContainer').append('<p class="alert alert-secondary alertRentals text-center mt-3 font-weight-bolder p-2">Aucune location ne correspond aux critères de recherche.</p>');
            }
            $('.voir-plus').hide();
        } else {
            $('#logementsContainer p.alertRentals').remove(); // Supprime le message si des résultats sont trouvés
            if (nbLogement < nbLocationFiltre) {
                $('.voir-plus').show();
            } else {
                $('.voir-plus').hide();
            }
        }
    }

    afficherLocations(nbLogement);
});