@extends('layouts/default')

@section('title')
LendIT Ausleihübersicht @parent
@stop

@section('content')
<x-container>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">LendIT-Ausleihübersicht</h2>
                    <div class="box-tools pull-right">
                        <a class="btn btn-sm btn-default" href="{{ route('lendit.statistics') }}">Statistik</a>
                        <a class="btn btn-sm btn-default" href="{{ route('lendit.user-history') }}">Benutzerhistorie</a>
                        <a class="btn btn-sm btn-default" href="{{ route('lendit.dashboard') }}">Inventar</a>
                    </div>
                </div>
                <div class="box-body">
                    <form method="GET" action="{{ route('lendit.checkouts') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="lendit-checkout-search">Suche</label>
                                <input id="lendit-checkout-search" type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Artikel oder Benutzer suchen">
                            </div>
                            <div class="col-md-3">
                                <label for="lendit-checkout-status">Status</label>
                                <select id="lendit-checkout-status" name="status" class="form-control">
                                    <option value="all" @selected($status === 'all')>Alle aktuellen Ausleihen</option>
                                    <option value="overdue" @selected($status === 'overdue')>Nur überfällig</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div>
                                    <button class="btn btn-primary" type="submit">Filtern</button>
                                    @if ($search !== '' || $status !== 'all')
                                        <a class="btn btn-default" href="{{ route('lendit.checkouts') }}">Zurücksetzen</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Ausgeliehene Assets</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Artikel</th>
                                <th>Kategorie</th>
                                <th>Ausgeliehen an</th>
                                <th>Ausgeliehen seit</th>
                                <th>Geplante Rückgabe</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assets as $asset)
                                @php
                                    $lastCheckout = $asset->last_checkout ? \Illuminate\Support\Carbon::parse($asset->last_checkout) : null;
                                    $expectedCheckin = $asset->expected_checkin ? \Illuminate\Support\Carbon::parse($asset->expected_checkin) : null;
                                    $isOverdue = $expectedCheckin && $expectedCheckin->lt($today);
                                @endphp
                                <tr class="{{ $isOverdue ? 'danger' : '' }}">
                                    <td>
                                        <a href="{{ route('hardware.show', $asset) }}">
                                            {{ $asset->name ?: $asset->asset_tag }}
                                        </a>
                                    </td>
                                    <td>{{ optional(optional($asset->model)->category)->name ?: '-' }}</td>
                                    <td>{{ optional($asset->assignedTo)->display_name ?: '-' }}</td>
                                    <td>{{ $lastCheckout ? $lastCheckout->format('d.m.Y H:i') : '-' }}</td>
                                    <td>{{ $expectedCheckin ? $expectedCheckin->format('d.m.Y') : '-' }}</td>
                                    <td>
                                        @if ($isOverdue)
                                            <span class="label label-danger">Überfällig</span>
                                        @else
                                            <span class="label label-success">Aktuell</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">Keine ausgeliehenen Assets gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Ausgeliehenes Zubehör</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Artikel</th>
                                <th>Kategorie</th>
                                <th>Ausgeliehen an</th>
                                <th>Ausgeliehen seit</th>
                                <th>Notiz</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accessoryCheckouts as $checkout)
                                <tr>
                                    <td>
                                        @if ($checkout->accessory)
                                            <a href="{{ route('accessories.show', $checkout->accessory) }}">
                                                {{ $checkout->accessory->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ optional(optional($checkout->accessory)->category)->name ?: '-' }}</td>
                                    <td>{{ optional($checkout->assignedTo)->display_name ?: '-' }}</td>
                                    <td>{{ $checkout->created_at ? $checkout->created_at->format('d.m.Y H:i') : '-' }}</td>
                                    <td>{{ $checkout->note ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">Kein ausgeliehenes Zubehör gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-container>
@stop
