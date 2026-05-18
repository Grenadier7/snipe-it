@extends('layouts/default')

@section('title')
LendIT @parent
@stop

@section('content')
<x-container>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">LendIT-Inventarübersicht</h2>
                    @include('lendit.partials.nav')
                </div>
                <div class="box-body">
                    <form method="GET" action="{{ route('lendit.dashboard') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="lendit-search">Suche</label>
                                <input id="lendit-search" type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Name, Beschreibung oder Nummer">
                            </div>
                            <div class="col-md-2">
                                <label for="lendit-tag">Tag</label>
                                <input id="lendit-tag" type="text" name="tag" class="form-control" value="{{ $tag }}" placeholder="z.B. #arduino">
                            </div>
                            <div class="col-md-3">
                                <label for="lendit-category">Kategorie</label>
                                <select id="lendit-category" name="category_id" class="form-control">
                                    <option value="">Alle Kategorien</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($categoryId === $category->id)>
                                            {{ $category->name }} ({{ $category->category_type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="lendit-location">Standort</label>
                                <select id="lendit-location" name="location_id" class="form-control">
                                    <option value="">Alle Standorte</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" @selected($locationId === $location->id)>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="lendit-availability">Verfügbarkeit</label>
                                <select id="lendit-availability" name="availability" class="form-control">
                                    <option value="">Alle</option>
                                    <option value="available" @selected($availability === 'available')>Verfügbar</option>
                                    <option value="unavailable" @selected($availability === 'unavailable')>Nicht verfügbar</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-12">
                                <button class="btn btn-primary" type="submit">Filtern</button>
                                @if ($search !== '' || $tag !== '' || $categoryId || $locationId || $availability)
                                    <a class="btn btn-default" href="{{ route('lendit.dashboard') }}">Zurücksetzen</a>
                                @endif
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
                    <h2 class="box-title">Geräte / Assets</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Kategorie</th>
                                <th>Verfügbarkeit</th>
                                <th>Rückgabe</th>
                                <th>Frist</th>
                                <th>Standort</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assets as $asset)
                                @php
                                    $assetAvailable = !$asset->assigned_to && optional($asset->status)->deployable && !optional($asset->status)->archived;
                                    $expectedCheckin = $asset->expected_checkin ? \Illuminate\Support\Carbon::parse($asset->expected_checkin) : null;
                                    $isOverdue = $expectedCheckin && $expectedCheckin->lt(\Illuminate\Support\Carbon::today());
                                @endphp
                                <tr class="{{ $isOverdue ? 'danger' : '' }}">
                                    <td>
                                        <a href="{{ route('hardware.show', $asset) }}">
                                            {{ $asset->name ?: $asset->asset_tag }}
                                        </a>
                                    </td>
                                    <td>{{ optional(optional($asset->model)->category)->name ?: '-' }}</td>
                                    <td>
                                        @if ($assetAvailable)
                                            <span class="label label-success">Verfügbar</span>
                                        @elseif ($asset->assigned_to)
                                            <span class="label label-warning">Ausgeliehen</span>
                                        @else
                                            <span class="label label-default">{{ optional($asset->status)->name ?: 'Nicht verfügbar' }}</span>
                                        @endif
                                    </td>
                                    <td><span class="label label-info">Ja</span></td>
                                    <td>
                                        @if ($isOverdue)
                                            <span class="label label-danger">{{ $expectedCheckin->format('d.m.Y') }}</span>
                                        @elseif ($expectedCheckin)
                                            {{ $expectedCheckin->format('d.m.Y') }}
                                        @else
                                            Beim Checkout
                                        @endif
                                    </td>
                                    <td>{{ optional($asset->location ?: $asset->defaultLoc)->name ?: '-' }}</td>
                                    <td>
                                        <a class="btn btn-xs btn-default" href="{{ route('hardware.show', $asset) }}">Öffnen</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7">Keine Assets gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Zubehör / Accessories</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Kategorie</th>
                                <th>Verfügbarkeit</th>
                                <th>Rückgabe</th>
                                <th>Frist</th>
                                <th>Standort</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accessories as $accessory)
                                @php
                                    $remaining = $accessory->numRemaining();
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('accessories.show', $accessory) }}">
                                            {{ $accessory->name }}
                                        </a>
                                    </td>
                                    <td>{{ optional($accessory->category)->name ?: '-' }}</td>
                                    <td>
                                        @if ($remaining > 0)
                                            <span class="label label-success">{{ $remaining }} / {{ $accessory->qty }} verfügbar</span>
                                        @else
                                            <span class="label label-warning">Ausgeliehen</span>
                                        @endif
                                    </td>
                                    <td><span class="label label-info">Ja</span></td>
                                    <td>Nicht definiert</td>
                                    <td>{{ optional($accessory->location)->name ?: '-' }}</td>
                                    <td>
                                        <a class="btn btn-xs btn-default" href="{{ route('accessories.show', $accessory) }}">Öffnen</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7">Kein Zubehör gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Verbrauchsmaterial / Consumables</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Kategorie</th>
                                <th>Verfügbarkeit</th>
                                <th>Rückgabe</th>
                                <th>Frist</th>
                                <th>Standort</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($consumables as $consumable)
                                @php
                                    $remaining = $consumable->numRemaining();
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('consumables.show', $consumable) }}">
                                            {{ $consumable->name }}
                                        </a>
                                    </td>
                                    <td>{{ optional($consumable->category)->name ?: '-' }}</td>
                                    <td>
                                        @if ($remaining > 0)
                                            <span class="label label-success">{{ $remaining }} / {{ $consumable->qty }} verfügbar</span>
                                        @else
                                            <span class="label label-danger">Aufgebraucht</span>
                                        @endif
                                    </td>
                                    <td><span class="label label-default">Nein</span></td>
                                    <td>Keine Rückgabe</td>
                                    <td>{{ optional($consumable->location)->name ?: '-' }}</td>
                                    <td>
                                        <a class="btn btn-xs btn-default" href="{{ route('consumables.show', $consumable) }}">Öffnen</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7">Kein Verbrauchsmaterial gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-container>
@stop
