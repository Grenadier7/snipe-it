@php
    $links = [
        ['route' => 'lendit.dashboard', 'label' => 'Inventar'],
        ['route' => 'lendit.checkouts', 'label' => 'Ausleihübersicht', 'teacher' => true],
        ['route' => 'lendit.user-history', 'label' => 'Benutzerhistorie', 'teacher' => true],
        ['route' => 'lendit.statistics', 'label' => 'Statistik', 'teacher' => true],
    ];
@endphp

<div class="box-tools pull-right">
    @foreach ($links as $link)
        @continue(($link['teacher'] ?? false) && ! auth()->user()->can('reports.view'))
        @php
            $isActive = request()->routeIs($link['route'])
                || ($link['route'] === 'lendit.user-history' && request()->routeIs('lendit.users.history'));
        @endphp
        <a class="btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-default' }}" href="{{ route($link['route']) }}">
            {{ $link['label'] }}
        </a>
    @endforeach
</div>
