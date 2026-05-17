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
                    <h2 class="box-title">LendIT Inventar</h2>
                </div>
                <div class="box-body">
                    <form method="GET" action="{{ route('lendit.dashboard') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="lendit-search">Suche</label>
                                <input id="lendit-search" type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Name, Inventarnummer oder Seriennummer">
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
                            <div class="col-md-3">
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
                                <label for="lendit-availability">Verfugbarkeit</label>
                                <select id="lendit-availability" name="availability" class="form-control">
                                    <option value="">Alle</option>
                                    <option value="available" @selected($availability === 'available')>Verfugbar</option>
                                    <option value="unavailable" @selected($availability === 'unavailable')>Nicht verfugbar</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-12">
                                <button class="btn btn-primary" type="submit">Filtern</button>
                                @if ($search !== '' || $categoryId || $locationId || $availability)
                                    <a class="btn btn-default" href="{{ route('lendit.dashboard') }}">Zurucksetzen</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Assets</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Kategorie</th>
                                <th>Status</th>
                                <th>Standort</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assets as $asset)
                                <tr>
                                    <td>
                                        <a href="{{ route('hardware.show', $asset) }}">
                                            {{ $asset->name ?: $asset->asset_tag }}
                                        </a>
                                    </td>
                                    <td>{{ optional(optional($asset->model)->category)->name ?: '-' }}</td>
                                    <td>{{ optional($asset->status)->name ?: '-' }}</td>
                                    <td>{{ optional($asset->location ?: $asset->defaultLoc)->name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">Keine Assets gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Zubehor</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Kategorie</th>
                                <th>Verfugbar</th>
                                <th>Standort</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($accessories as $accessory)
                                <tr>
                                    <td>
                                        <a href="{{ route('accessories.show', $accessory) }}">
                                            {{ $accessory->name }}
                                        </a>
                                    </td>
                                    <td>{{ optional($accessory->category)->name ?: '-' }}</td>
                                    <td>{{ $accessory->numRemaining() }} / {{ $accessory->qty }}</td>
                                    <td>{{ optional($accessory->location)->name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">Kein Zubehor gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Verbrauchsmaterial</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Kategorie</th>
                                <th>Verfugbar</th>
                                <th>Standort</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($consumables as $consumable)
                                <tr>
                                    <td>
                                        <a href="{{ route('consumables.show', $consumable) }}">
                                            {{ $consumable->name }}
                                        </a>
                                    </td>
                                    <td>{{ optional($consumable->category)->name ?: '-' }}</td>
                                    <td>{{ $consumable->numRemaining() }} / {{ $consumable->qty }}</td>
                                    <td>{{ optional($consumable->location)->name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">Kein Verbrauchsmaterial gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-container>
@stop
