<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/plugins/metismenu/js/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/widgets.js') }}"></script>

<script src="{{ asset('assets/plugins/Drag-And-Drop/dist/imageuploadify.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/pace.min.js') }}"></script>

<script>
    // Rendre TOUS les tableaux DataTables lisibles sur mobile : défilement
    // horizontal (toutes les colonnes + boutons restent accessibles au doigt)
    // + libellés FR.
    // Pas de scrollX : avec cette version de DataTables (1.10.18, antérieure
    // à Bootstrap 5), l'en-tête "cloné" qu'il crée pour rester fixe au
    // défilement se superposait mal à l'en-tête réel — visible en clair si on
    // sélectionne/copie la page (en-tête présent deux fois), et sous la forme
    // d'une bande vide à l'écran. Le défilement horizontal passe plutôt par
    // .table-responsive (CSS pur, déjà utilisé pour les tableaux hors
    // DataTables juste plus bas), fiable quelle que soit la version.
    if ($.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            autoWidth: false,
            language: {
                search: 'Rechercher :',
                lengthMenu: 'Afficher _MENU_ éléments',
                info: '_START_ à _END_ sur _TOTAL_',
                infoEmpty: '0 élément',
                infoFiltered: '(filtré sur _MAX_)',
                zeroRecords: 'Aucun résultat',
                emptyTable: 'Aucune donnée disponible',
                paginate: { first: '«', previous: '‹', next: '›', last: '»' },
            },
        });
    }

    // Tous les tableaux (DataTables compris, scrollX étant désactivé ci-dessus)
    // sont rendus scrollables horizontalement sur mobile en les enveloppant
    // dans .table-responsive : les colonnes restent accessibles au doigt sans
    // que la page entière ne défile.
    window.addEventListener('load', function () {
        document.querySelectorAll('table.table').forEach(function (t) {
            if (t.closest('.table-responsive')) return;
            const wrap = document.createElement('div');
            wrap.className = 'table-responsive';
            t.parentNode.insertBefore(wrap, t);
            wrap.appendChild(t);
        });
    });
</script>

<script>
    $(function () {
        $('.single-select').each(function () {
            const modal = $(this).closest('.modal');
            $(this).select2({
                theme: 'bootstrap4',
                allowClear: false,
                dropdownParent: modal.length ? modal : $(document.body),
            });
        });
    });
</script>
