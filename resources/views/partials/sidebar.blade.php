<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <span class="logo-icon logo-3d-mini">WN</span>
        </div>
        <div>
            <h4 class="logo-text logo-3d mb-0"><span class="w3d-w">WARI</span><span class="w3d-n">NIOUMA</span></h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i></div>
    </div>

    <ul class="metismenu" id="menu">
        <li>
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'mm-active' : '' }}">
                <div class="parent-icon"><i class='bx bx-home-circle'></i></div>
                <div class="menu-title">Tableau de bord</div>
            </a>
        </li>

        @can('chauffeurs.voir')
            <li>
                <a href="{{ route('chauffeurs.index') }}" class="{{ request()->routeIs('chauffeurs.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-id-card'></i></div>
                    <div class="menu-title">Chauffeurs</div>
                </a>
            </li>
        @endcan

        @can('vehicules.voir')
            <li>
                <a href="{{ route('vehicules.index') }}" class="{{ request()->routeIs('vehicules.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-car'></i></div>
                    <div class="menu-title">Véhicules</div>
                </a>
            </li>
        @endcan

        @can('affectations.voir')
            <li>
                <a href="{{ route('affectations.index') }}" class="{{ request()->routeIs('affectations.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-transfer-alt'></i></div>
                    <div class="menu-title">Affectations</div>
                </a>
            </li>
        @endcan

        @can('recettes.voir')
            <li>
                <a href="{{ route('recettes.index') }}" class="{{ request()->routeIs('recettes.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-money'></i></div>
                    <div class="menu-title">Recettes</div>
                </a>
            </li>
        @endcan

        @can('absences.voir')
            <li>
                <a href="{{ route('absences.index') }}" class="{{ request()->routeIs('absences.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-calendar-x'></i></div>
                    <div class="menu-title">Absences</div>
                </a>
            </li>
        @endcan

        @can('depenses.voir')
            <li>
                <a href="{{ route('depenses.index') }}" class="{{ request()->routeIs('depenses.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-wallet'></i></div>
                    <div class="menu-title">Dépenses</div>
                </a>
            </li>
        @endcan

        @can('accidents.voir')
            <li>
                <a href="{{ route('accidents.index') }}" class="{{ request()->routeIs('accidents.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bxs-error'></i></div>
                    <div class="menu-title">Accidents</div>
                </a>
            </li>
        @endcan

        @can('incidents.voir')
            <li>
                <a href="{{ route('incidents.index') }}" class="{{ request()->routeIs('incidents.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-flag'></i></div>
                    <div class="menu-title">Incidents</div>
                </a>
            </li>
        @endcan

        @can('caisse.voir')
            <li>
                <a href="{{ route('caisse.index') }}" class="{{ request()->routeIs('caisse.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-money-withdraw'></i></div>
                    <div class="menu-title">Caisse</div>
                </a>
            </li>
        @endcan

        @can('finances.voir')
            <li>
                <a href="{{ route('finances.index') }}" class="{{ request()->routeIs('finances.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-line-chart'></i></div>
                    <div class="menu-title">Finances</div>
                </a>
            </li>
        @endcan

        @can('rapports.voir')
            <li>
                <a href="{{ route('statistiques.index') }}" class="{{ request()->routeIs('statistiques.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                    <div class="menu-title">Statistiques</div>
                </a>
            </li>
        @endcan

        @can('personnel.voir')
            <li>
                <a href="{{ route('personnel.index') }}" class="{{ request()->routeIs('personnel.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-group'></i></div>
                    <div class="menu-title">Personnel</div>
                </a>
            </li>
        @endcan

        @can('bulletins.voir')
            <li>
                <a href="{{ route('bulletins.index') }}" class="{{ request()->routeIs('bulletins.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-receipt'></i></div>
                    <div class="menu-title">Bulletins de paie</div>
                </a>
            </li>
        @endcan

        @can('mandats.voir')
            <li>
                <a href="{{ route('mandats.index') }}" class="{{ request()->routeIs('mandats.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-file'></i></div>
                    <div class="menu-title">Mandats de paiement</div>
                </a>
            </li>
        @endcan

        @can('utilisateurs.voir')
            <li>
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'mm-active' : '' }}">
                    <div class="parent-icon"><i class='bx bx-cog bx-spin'></i></div>
                    <div class="menu-title">Configuration</div>
                </a>
            </li>
        @endcan

        <li>
            <a href="{{ route('documentation') }}" class="{{ request()->routeIs('documentation') ? 'mm-active' : '' }}">
                <div class="parent-icon"><i class='bx bx-book-open'></i></div>
                <div class="menu-title">Documentation</div>
            </a>
        </li>
    </ul>
</div>
