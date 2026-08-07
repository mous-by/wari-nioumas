<div class="card">
    <div class="card-header card-header-brand text-white d-flex align-items-center gap-2">
        <i class='bx bx-cog'></i>
        <span class="fw-semibold">Configuration</span>
    </div>
    <div class="card-body">
        <div class="list-group list-group-flush">
            @can('utilisateurs.voir')
                <a href="{{ route('users.index') }}" class="list-group-item py-2 {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class='bx bx-user me-2'></i><span>Liste Utilisateur</span>
                </a>
            @endcan
            @role('superadmin')
                <a href="{{ route('permissions.index') }}" class="list-group-item py-2 {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                    <i class='bx bx-shield-alt-2 me-2'></i><span>Permissions</span>
                </a>
            @endrole
            @can('roles.gerer')
                <a href="{{ route('user-permissions.index') }}" class="list-group-item py-2 {{ request()->routeIs('user-permissions.*') ? 'active' : '' }}">
                    <i class='bx bx-user-check me-2'></i><span>Assigner permissions</span>
                </a>
            @endcan
            <a href="{{ route('signature.edit') }}" class="list-group-item py-2 {{ request()->routeIs('signature.*') ? 'active' : '' }}">
                <i class='bx bx-pen me-2'></i><span>Signature &amp; cachet</span>
            </a>
        </div>
    </div>
</div>
