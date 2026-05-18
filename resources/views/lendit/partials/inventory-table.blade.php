<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">{{ $title }}</h2>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Kategorie</th>
                            <th>Verfuegbarkeit</th>
                            <th>Rueckgabe</th>
                            <th>Frist</th>
                            <th>Standort</th>
                            <th>Tags</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php
                                $tagsForItem = $tagNames($item::class, $item->id);

                                if ($type === 'asset') {
                                    $name = $item->name ?: $item->asset_tag;
                                    $showRoute = route('hardware.show', $item);
                                    $category = optional(optional($item->model)->category)->name ?: '-';
                                    $location = optional($item->location ?: $item->defaultLoc)->name ?: '-';
                                    $available = ! $item->assigned_to && optional($item->status)->deployable && ! optional($item->status)->archived;
                                    $expectedCheckin = $item->expected_checkin ? \Illuminate\Support\Carbon::parse($item->expected_checkin) : null;
                                    $isOverdue = $expectedCheckin && $expectedCheckin->lt(\Illuminate\Support\Carbon::today());
                                    $availabilityLabel = $available
                                        ? '<span class="label label-success">Verfuegbar</span>'
                                        : ($item->assigned_to
                                            ? '<span class="label label-warning">Ausgeliehen</span>'
                                            : '<span class="label label-default">'.e(optional($item->status)->name ?: 'Nicht verfuegbar').'</span>');
                                    $returnable = '<span class="label label-info">Ja</span>';
                                    $deadline = $isOverdue
                                        ? '<span class="label label-danger">'.$expectedCheckin->format('d.m.Y').'</span>'
                                        : ($expectedCheckin ? $expectedCheckin->format('d.m.Y') : 'Beim Checkout');
                                } elseif ($type === 'accessory') {
                                    $name = $item->name;
                                    $showRoute = route('accessories.show', $item);
                                    $category = optional($item->category)->name ?: '-';
                                    $location = optional($item->location)->name ?: '-';
                                    $remaining = $item->numRemaining();
                                    $isOverdue = false;
                                    $availabilityLabel = $remaining > 0
                                        ? '<span class="label label-success">'.$remaining.' / '.$item->qty.' verfuegbar</span>'
                                        : '<span class="label label-warning">Ausgeliehen</span>';
                                    $returnable = '<span class="label label-info">Ja</span>';
                                    $deadline = 'Nicht definiert';
                                } else {
                                    $name = $item->name;
                                    $showRoute = route('consumables.show', $item);
                                    $category = optional($item->category)->name ?: '-';
                                    $location = optional($item->location)->name ?: '-';
                                    $remaining = $item->numRemaining();
                                    $isOverdue = false;
                                    $availabilityLabel = $remaining > 0
                                        ? '<span class="label label-success">'.$remaining.' / '.$item->qty.' verfuegbar</span>'
                                        : '<span class="label label-danger">Aufgebraucht</span>';
                                    $returnable = '<span class="label label-default">Nein</span>';
                                    $deadline = 'Keine Rueckgabe';
                                }
                            @endphp
                            <tr class="{{ $isOverdue ? 'danger' : '' }}">
                                <td><a href="{{ $showRoute }}">{{ $name }}</a></td>
                                <td>{{ $category }}</td>
                                <td>{!! $availabilityLabel !!}</td>
                                <td>{!! $returnable !!}</td>
                                <td>{!! $deadline !!}</td>
                                <td>{{ $location }}</td>
                                <td style="min-width: 220px;">
                                    @forelse ($tagsForItem as $tagName)
                                        <span class="label label-primary">{{ $tagName }}</span>
                                    @empty
                                        <span class="text-muted">-</span>
                                    @endforelse

                                    @if ($canManageLenditTags)
                                        <form method="POST" action="{{ route('lendit.tags.item.update') }}" class="form-inline" style="margin-top: 6px;">
                                            @csrf
                                            <input type="hidden" name="item_type" value="{{ $type }}">
                                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="tags" class="form-control" value="{{ $tagList($item::class, $item->id) }}" placeholder="arduino, sensor">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="submit" title="Tags speichern">
                                                        <i class="fa fa-save" aria-hidden="true"></i>
                                                        <span class="sr-only">Tags speichern</span>
                                                    </button>
                                                </span>
                                            </div>
                                        </form>
                                    @endif
                                </td>
                                <td><a class="btn btn-xs btn-default" href="{{ $showRoute }}">Oeffnen</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8">{{ $emptyText }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
