@extends('layouts/default')

@section('title')
LendIT Statistik @parent
@stop

@section('content')
<x-container>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">LendIT-Statistik</h2>
                    <div class="box-tools pull-right">
                        <a class="btn btn-sm btn-default" href="{{ route('lendit.checkouts') }}">Ausleihübersicht</a>
                        <a class="btn btn-sm btn-default" href="{{ route('lendit.user-history') }}">Benutzerhistorie</a>
                        <a class="btn btn-sm btn-default" href="{{ route('lendit.dashboard') }}">Inventar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3>{{ $assetTotal }}</h3>
                    <p>Assets gesamt</p>
                </div>
                <div class="icon"><i class="fas fa-laptop"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-green">
                <div class="inner">
                    <h3>{{ $assetAvailable }}</h3>
                    <p>Assets verfügbar</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3>{{ $assetCheckedOut }}</h3>
                    <p>Aktuelle Asset-Ausleihen</p>
                </div>
                <div class="icon"><i class="fas fa-upload"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-red">
                <div class="inner">
                    <h3>{{ $assetOverdue }}</h3>
                    <p>Überfällige Assets</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Assets</h2>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Gesamt</dt>
                        <dd>{{ $assetTotal }}</dd>
                        <dt>Verfügbar</dt>
                        <dd>{{ $assetAvailable }}</dd>
                        <dt>Ausgeliehen</dt>
                        <dd>{{ $assetCheckedOut }}</dd>
                        <dt>Überfällig</dt>
                        <dd>{{ $assetOverdue }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Zubehör</h2>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Gesamtmenge</dt>
                        <dd>{{ $accessoryTotalQty }}</dd>
                        <dt>Verfügbar</dt>
                        <dd>{{ $accessoryAvailable }}</dd>
                        <dt>Ausgeliehen</dt>
                        <dd>{{ $accessoryCheckedOut }}</dd>
                        <dt>Frist</dt>
                        <dd>Nicht definiert</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Verbrauchsmaterial</h2>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Gesamtmenge</dt>
                        <dd>{{ $consumableTotalQty }}</dd>
                        <dt>Verfügbar</dt>
                        <dd>{{ $consumableAvailable }}</dd>
                        <dt>Entnommen</dt>
                        <dd>{{ $consumableIssued }}</dd>
                        <dt>Rückgabe</dt>
                        <dd>Nein</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Häufig ausgeliehene Artikel</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Artikel</th>
                                <th>Typ</th>
                                <th>Ausleihen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topLoanedItems as $entry)
                                @php
                                    $item = $entry->item;
                                    $itemName = $item
                                        ? ($item->name ?? $item->asset_tag ?? class_basename($entry->item_type).' #'.$entry->item_id)
                                        : class_basename($entry->item_type).' #'.$entry->item_id;
                                @endphp
                                <tr>
                                    <td>{{ $itemName }}</td>
                                    <td>{{ class_basename($entry->item_type) }}</td>
                                    <td>{{ $entry->checkout_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Noch keine Ausleihdaten vorhanden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Letzte Ausleihen</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Artikel</th>
                                <th>Benutzer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentCheckouts as $log)
                                @php
                                    $logDate = $log->action_date ?: $log->created_at;
                                    $logDate = $logDate ? \Illuminate\Support\Carbon::parse($logDate) : null;
                                    $item = $log->item;
                                    $itemName = $item
                                        ? ($item->name ?? $item->asset_tag ?? class_basename($log->item_type).' #'.$log->item_id)
                                        : class_basename($log->item_type).' #'.$log->item_id;
                                @endphp
                                <tr>
                                    <td>{{ $logDate ? $logDate->format('d.m.Y H:i') : '-' }}</td>
                                    <td>{{ $itemName }}</td>
                                    <td>{{ optional($log->target)->display_name ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Noch keine Ausleihen gefunden.</td></tr>
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
                    <h2 class="box-title">Bestand nach Kategorien</h2>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kategorie</th>
                                <th>Bereich</th>
                                <th>Menge</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categoryStats as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->type }}</td>
                                    <td>{{ $category->total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">Keine Kategorien gefunden.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-container>
@stop
