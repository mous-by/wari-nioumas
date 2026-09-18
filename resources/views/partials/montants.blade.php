<script>
    // Montants en FCFA : entiers affichés par groupes de 3 chiffres ("3 200 000").
    // Les champs .champ-montant sont de type texte : un type="number" ne sait pas
    // afficher d'espaces, et montre "3200000,00" quand la valeur vient d'un
    // decimal:2 de la base ("3200000.00"). Le serveur retire les espaces avant
    // validation (middleware NormaliserMontants).
    (function () {
        function grouper(chiffres) {
            return chiffres.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }

        // Valeur brute ("3200000.00", "3 200 000", "3200000,5") -> "3 200 000".
        // À utiliser aussi quand on remplit un champ en JS : $('#x').val(formaterMontant(v)).
        window.formaterMontant = function (brut) {
            if (brut === null || brut === undefined) return '';
            const nombre = parseFloat(String(brut).replace(/[\s  ]/g, '').replace(',', '.'));
            return isNaN(nombre) ? '' : grouper(String(Math.round(nombre)));
        };

        // Saisie en direct : on ne garde que les chiffres et on regroupe, en
        // conservant la position du curseur (compte des chiffres avant lui).
        $(document).on('input', '.champ-montant', function (e) {
            const champ = this;
            const type = (e.originalEvent && e.originalEvent.inputType) || '';
            const colle = type === 'insertFromPaste' || type === 'insertFromDrop';
            const chiffresAvant = champ.value.slice(0, champ.selectionStart).replace(/\D/g, '').length;

            champ.value = colle ? window.formaterMontant(champ.value) : grouper(champ.value.replace(/\D/g, ''));

            let position = 0, vus = 0;
            while (position < champ.value.length && vus < chiffresAvant) {
                if (/\d/.test(champ.value[position])) vus++;
                position++;
            }
            champ.setSelectionRange(colle ? champ.value.length : position, colle ? champ.value.length : position);
        });

        // Valeurs déjà présentes au chargement (édition, old() après erreur).
        $(function () {
            $('.champ-montant').each(function () {
                this.value = window.formaterMontant(this.value);
            });
        });
    })();
</script>
