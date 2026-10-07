@php
    $validationsEnAttente = auth()->user()->can('validations.voir')
        ? \App\Models\Validation::enAttente()->with('demandeur')->latest()->limit(10)->get()
        : collect();

    // Documents expirés ou arrivant bientôt à échéance (≤ 30 jours) : les
    // expirés d'abord, puis par échéance la plus proche.
    $documentsASurveiller = auth()->user()->can('documents.voir')
        ? \App\Models\Document::with('documentable')
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '<=', now()->copy()->addDays(\App\Models\Document::SEUIL_ATTENTION_JOURS))
            ->orderBy('date_expiration')
            ->limit(10)
            ->get()
        : collect();
    $nbNotifications = $validationsEnAttente->count() + $documentsASurveiller->count();
@endphp
<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>

            <div class="search-bar flex-grow-1">
                <div class="position-relative search-bar-box">
                    <input type="text" class="form-control search-control" placeholder="Rechercher...">
                    <span class="position-absolute top-50 search-show translate-middle-y"><i class='bx bx-search'></i></span>
                    <span class="position-absolute top-50 search-close translate-middle-y"><i class='bx bx-x'></i></span>
                </div>
            </div>

            <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class='bx bx-bell'></i>
                            @if ($nbNotifications > 0)
                                <span class="position-absolute translate-middle badge rounded-pill bg-danger" style="top:8px; left:26px; font-size:.62rem;">
                                    {{ $nbNotifications }}
                                    <span class="visually-hidden">notifications</span>
                                </span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:;">
                                <div class="msg-header">
                                    <p class="msg-header-title">Notifications</p>
                                    @if ($nbNotifications > 0)
                                        <span class="badge bg-warning text-dark ms-auto">{{ $nbNotifications }}</span>
                                    @endif
                                </div>
                            </a>
                            <div class="header-notifications-list">
                                @forelse ($validationsEnAttente as $validation)
                                    <a class="dropdown-item" href="{{ route('validations.index') }}">
                                        <div class="d-flex align-items-center">
                                            <div class="notify bg-light-warning text-warning"><i class="bx bx-been-here"></i></div>
                                            <div class="flex-grow-1">
                                                <h6 class="msg-name mb-0">Validation requise</h6>
                                                <span class="msg-info">{{ \Illuminate\Support\Str::limit($validation->libelle, 48) }}</span>
                                                <span class="msg-info d-block text-muted" style="font-size:.72rem;">
                                                    {{ $validation->demandeur?->name ?? '' }} · {{ $validation->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                @endforelse

                                @foreach ($documentsASurveiller as $document)
                                    @php
                                        $expire = $document->statut === 'expire';
                                        $lien = $document->documentable instanceof \App\Models\Vehicule
                                            ? route('vehicules.show', $document->documentable)
                                            : ($document->documentable ? route('chauffeurs.show', $document->documentable) : route('documents.index'));
                                    @endphp
                                    <a class="dropdown-item" href="{{ $lien }}">
                                        <div class="d-flex align-items-center">
                                            <div class="notify {{ $expire ? 'bg-light-danger text-danger' : 'bg-light-warning text-warning' }}">
                                                <i class="bx {{ $expire ? 'bx-error-circle' : 'bx-time-five' }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="msg-name mb-0">
                                                    {{ $expire ? 'Document expiré' : 'Échéance proche' }}
                                                </h6>
                                                <span class="msg-info">
                                                    {{ $document->type_libelle }} — {{ $document->proprietaire_libelle }}
                                                    {{ $expire ? 'expiré depuis '.abs($document->jours_restants).' j' : 'dans '.$document->jours_restants.' j' }}
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                                @if ($validationsEnAttente->isEmpty() && $documentsASurveiller->isEmpty())
                                    <div class="dropdown-item text-center text-muted py-4">
                                        Aucune notification pour le moment
                                    </div>
                                @endif
                            </div>
                            @if ($documentsASurveiller->isNotEmpty())
                                <a href="{{ route('documents.index') }}">
                                    <div class="text-center msg-footer fw-semibold text-primary py-2">Voir tous les documents</div>
                                </a>
                            @elseif ($validationsEnAttente->isNotEmpty())
                                <a href="{{ route('validations.index') }}">
                                    <div class="text-center msg-footer fw-semibold text-primary py-2">Voir toutes les validations</div>
                                </a>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>

            <div class="user-box dropdown">
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if (auth()->user()->photo_url)
                        <img src="{{ auth()->user()->photo_url }}" class="user-img" style="object-fit: cover;" alt="Photo de profil">
                    @else
                        <div class="user-img d-flex align-items-center justify-content-center bg-primary text-white fw-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="user-info ps-3">
                        <p class="user-name mb-0">{{ auth()->user()->name }}</p>
                        <p class="designattion mb-0">{{ ucfirst(str_replace('_', ' ', auth()->user()->roles->first()?->name ?? '')) }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bx bx-user"></i><span>Profil</span></a></li>
                    <li><div class="dropdown-divider mb-0"></div></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class='bx bx-log-out-circle'></i><span>Déconnexion</span>
                        </a>
                    </li>
                </ul>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </nav>
    </div>
</header>
