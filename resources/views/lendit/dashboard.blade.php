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
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Inventar suchen">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="submit">Suchen</button>
                                @if ($search !== '')
                                    <a class="btn btn-default" href="{{ route('lendit.dashboard') }}">Zurucksetzen</a>
                                @endif
                            </span>
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
                                    <td>{{ optional($asset->status)->name ?: '-' }}</td>
                                    <td>{{ optional($asset->location ?: $asset->defaultLoc)->name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Keine Assets gefunden.</td></tr>
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
                                    <td>{{ $accessory->numRemaining() }} / {{ $accessory->qty }}</td>
                                    <td>{{ optional($accessory->location)->name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Kein Zubehor gefunden.</td></tr>
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
                                    <td>{{ $consumable->numRemaining() }} / {{ $consumable->qty }}</td>
                                    <td>{{ optional($consumable->location)->name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Kein Verbrauchsmaterial gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-container>
@stop
