@extends('layouts/default')

@section('title')
LendIT @parent
@stop

@section('content')
@php
    $canManageLenditTags = auth()->user()->can('reports.view');

    $tagNames = function (string $type, int $id) use ($itemTags) {
        return ($itemTags->get($type.':'.$id) ?? collect())->pluck('name');
    };

    $tagList = function (string $type, int $id) use ($tagNames) {
        return $tagNames($type, $id)->implode(', ');
    };
@endphp

<x-container>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">LendIT-Inventaruebersicht</h2>
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
                                <select id="lendit-tag" name="tag_id" class="form-control">
                                    <option value="">Alle Tags</option>
                                    @foreach ($tags as $tag)
                                        <option value="{{ $tag->id }}" @selected($tagId === $tag->id)>{{ $tag->name }}</option>
                                    @endforeach
                                </select>
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
                                        <option value="{{ $location->id }}" @selected($locationId === $location->id)>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="lendit-availability">Verfuegbarkeit</label>
                                <select id="lendit-availability" name="availability" class="form-control">
                                    <option value="">Alle</option>
                                    <option value="available" @selected($availability === 'available')>Verfuegbar</option>
                                    <option value="unavailable" @selected($availability === 'unavailable')>Nicht verfuegbar</option>
                                </select>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-12">
                                <button class="btn btn-primary" type="submit">Filtern</button>
                                @if ($search !== '' || $tagId || $categoryId || $locationId || $availability)
                                    <a class="btn btn-default" href="{{ route('lendit.dashboard') }}">Zuruecksetzen</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('lendit.partials.inventory-table', [
        'title' => 'Geraete / Assets',
        'type' => 'asset',
        'items' => $assets,
        'emptyText' => 'Keine Assets gefunden.',
        'canManageLenditTags' => $canManageLenditTags,
        'tagNames' => $tagNames,
        'tagList' => $tagList,
    ])

    @include('lendit.partials.inventory-table', [
        'title' => 'Zubehoer / Accessories',
        'type' => 'accessory',
        'items' => $accessories,
        'emptyText' => 'Kein Zubehoer gefunden.',
        'canManageLenditTags' => $canManageLenditTags,
        'tagNames' => $tagNames,
        'tagList' => $tagList,
    ])

    @include('lendit.partials.inventory-table', [
        'title' => 'Verbrauchsmaterial / Consumables',
        'type' => 'consumable',
        'items' => $consumables,
        'emptyText' => 'Kein Verbrauchsmaterial gefunden.',
        'canManageLenditTags' => $canManageLenditTags,
        'tagNames' => $tagNames,
        'tagList' => $tagList,
    ])
</x-container>
@stop
