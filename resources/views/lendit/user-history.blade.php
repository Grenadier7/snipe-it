@extends('layouts/default')

@section('title')
LendIT Benutzerhistorie @parent
@stop

@section('content')
<x-container>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">LendIT-Benutzerhistorie</h2>
                    @include('lendit.partials.nav')
                </div>
                <div class="box-body">
                    <form method="GET" action="{{ route('lendit.user-history') }}">
                        <div class="row">
                            <div class="col-md-5">
                                <label for="lendit-user-search">Benutzer suchen</label>
                                <div class="input-group">
                                    <input id="lendit-user-search" type="text" name="user_search" class="form-control" value="{{ $userSearch }}" placeholder="Name, Username oder E-Mail">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit" title="Benutzer suchen">
                                            <i class="fa fa-search" aria-hidden="true"></i>
                                            <span class="sr-only">Benutzer suchen</span>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="lendit-user-id">Benutzer</label>
                                <select id="lendit-user-id" name="user_id" class="form-control">
                                    <option value="">Benutzer auswählen</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" @selected($selectedUserId === $user->id)>
                                            {{ $user->display_name ?: trim($user->first_name.' '.$user->last_name) ?: $user->username }}
                                            ({{ $user->username }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <div>
                                    <button class="btn btn-primary" type="submit">Anzeigen</button>
                                    @if ($selectedUser || $userSearch !== '')
                                        <a class="btn btn-default" href="{{ route('lendit.user-history') }}">Zurücksetzen</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>

                    @if ($userSearch !== '')
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td>{{ $user->display_name ?: trim($user->first_name.' '.$user->last_name) ?: $user->username }}</td>
                                            <td>{{ $user->username }}</td>
                                            <td class="text-right">
                                                <a class="btn btn-sm btn-primary" href="{{ route('lendit.users.history', $user) }}">Historie öffnen</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3">Keine Benutzer gefunden.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($selectedUser)
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">
                            {{ $selectedUser->display_name ?: trim($selectedUser->first_name.' '.$selectedUser->last_name) ?: $selectedUser->username }}
                        </h2>
                        <div class="box-tools pull-right">
                            <a class="btn btn-sm btn-primary" href="{{ route('lendit.users.history', $selectedUser) }}">Direktlink</a>
                            <a class="btn btn-sm btn-default" href="{{ route('users.show', $selectedUser) }}">Snipe-IT Benutzerprofil</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <dl class="dl-horizontal">
                            <dt>Username</dt>
                            <dd>{{ $selectedUser->username }}</dd>
                            <dt>E-Mail</dt>
                            <dd>{{ $selectedUser->email ?: '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>{{ $assets->count() }}</h3>
                        <p>Aktuelle Assets</p>
                    </div>
                    <div class="icon"><i class="fas fa-laptop"></i></div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>{{ $accessoryCheckouts->count() }}</h3>
                        <p>Aktuelles Zubehör</p>
                    </div>
                    <div class="icon"><i class="fas fa-plug"></i></div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>{{ $history->count() }}</h3>
                        <p>Historieneinträge</p>
                    </div>
                    <div class="icon"><i class="fas fa-history"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">Aktuell ausgeliehene Assets</h2>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Artikel</th>
                                    <th>Kategorie</th>
                                    <th>Ausgeliehen seit</th>
                                    <th>Geplante Rückgabe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assets as $asset)
                                    @php
                                        $lastCheckout = $asset->last_checkout ? \Illuminate\Support\Carbon::parse($asset->last_checkout) : null;
                                        $expectedCheckin = $asset->expected_checkin ? \Illuminate\Support\Carbon::parse($asset->expected_checkin) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('hardware.show', $asset) }}">
                                                {{ $asset->name ?: $asset->asset_tag }}
                                            </a>
                                        </td>
                                        <td>{{ optional(optional($asset->model)->category)->name ?: '-' }}</td>
                                        <td>{{ $lastCheckout ? $lastCheckout->format('d.m.Y H:i') : '-' }}</td>
                                        <td>{{ $expectedCheckin ? $expectedCheckin->format('d.m.Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">Keine aktuell ausgeliehenen Assets gefunden.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title">Aktuell ausgeliehenes Zubehör</h2>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Artikel</th>
                                    <th>Kategorie</th>
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
                                        <td>{{ $checkout->created_at ? $checkout->created_at->format('d.m.Y H:i') : '-' }}</td>
                                        <td>{{ $checkout->note ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">Kein aktuell ausgeliehenes Zubehör gefunden.</td></tr>
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
                        <h2 class="box-title">Ausleihverlauf</h2>
                    </div>
                    <div class="box-body">
                        <form method="GET" action="{{ route('lendit.users.history', $selectedUser) }}">
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="lendit-history-search">Verlauf durchsuchen</label>
                                    <input id="lendit-history-search" type="text" name="history_search" class="form-control" value="{{ $historySearch }}" placeholder="Artikel, Inventarnummer, Seriennummer, Aktion oder Notiz">
                                </div>
                                <div class="col-md-4">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button class="btn btn-primary" type="submit">Suchen</button>
                                        @if ($historySearch !== '')
                                            <a class="btn btn-default" href="{{ route('lendit.users.history', $selectedUser) }}">Zurücksetzen</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Aktion</th>
                                    <th>Artikel</th>
                                    <th>Typ</th>
                                    <th>Durchgeführt von</th>
                                    <th>Notiz</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($history as $log)
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
                                        <td>
                                            @if ($log->action_type === 'checkout')
                                                <span class="label label-success">Ausgeliehen</span>
                                            @else
                                                <span class="label label-default">Zurückgegeben</span>
                                            @endif
                                        </td>
                                        <td>{{ $itemName }}</td>
                                        <td>{{ class_basename($log->item_type) }}</td>
                                        <td>{{ optional($log->adminuser)->display_name ?: '-' }}</td>
                                        <td>{{ $log->note ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6">Keine bisherigen Ausleihvorgänge gefunden.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        Bitte zuerst einen Benutzer auswählen.
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-container>
@stop
