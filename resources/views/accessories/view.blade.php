@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ $accessory->name }}
    {{ trans('general.accessory') }}
    @if ($accessory->model_number!='')
        ({{ $accessory->model_number }})
    @endif

    @parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.checkedout-tab :item="$accessory" count="{{ $accessory->checkouts_count }}" />
                    <x-tabs.files-tab :item="$accessory" count="{{ $accessory->uploads()->count() }}"/>
                    <x-tabs.history-tab count="{{ $accessory->history()->count() }}" :model="$accessory"/>
                    <x-tabs.upload-tab :item="$accessory"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>
                    <x-tabs.pane name="assigned">
                        <x-slot:table_header>
                            {{ trans('general.checked_out') }}
                        </x-slot:table_header>

                        <x-table
                                api_url="{{ route('api.accessories.checkedout', $accessory->id) }}"
                                :presenter="\App\Presenters\AccessoryPresenter::assignedDataTableLayout()"
                                export_filename="export-{{ str_slug($accessory->name) }}-assets-{{ date('Y-m-d') }}"
                        />
                    </x-tabs.pane>
                    <x-tabs.pane name="history">
                        <x-table.history :model="$accessory" :route="route('api.accessories.history', $accessory)"/>
                    </x-tabs.pane>
                    <x-tabs.pane name="files">
                        <x-table.files object_type="accessories" :object="$accessory"/>
                    </x-tabs.pane>
                </x-slot:tabpanes>
            </x-tabs>
        </x-page-column>

        <x-page-column class="col-md-3">

            @if ($accessory->numRemaining() > 0)
                @if (Auth::user()->can('checkout', $accessory) || Auth::user()->can('checkoutSelf', $accessory))
                    <div style="margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 3px;">
                        <a href="{{ route('accessories.checkout.show', $accessory->id) }}" class="btn btn-sm bg-maroon btn-checkout btn-lg btn-block" style="font-size: 18px; padding: 15px; white-space: normal; color: #fff;">
                            <x-icon type="checkout" class="fa-fw" style="margin-right: 8px; font-size: 20px;"/>
                            <strong>{{ trans('general.checkout') }}</strong>
                        </a>
                    </div>
                @endif
            @endif

            @php
                // Prüfen, ob der aktuelle User DIESES Zubehör hat
                $myCheckout = \App\Models\AccessoryCheckout::where('accessory_id', $accessory->id)
                                ->where('assigned_type', \App\Models\User::class)
                                ->where('assigned_to', Auth::id())
                                ->first();
            @endphp

            @if ($myCheckout)
                @if (Auth::user()->can('checkin', $accessory) || Auth::user()->can('checkinSelf', $accessory))
                    <div style="margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 3px;">
                        <a href="{{ route('accessories.checkin.show', $myCheckout->id) }}" class="btn bg-purple btn-lg btn-block" style="font-size: 18px; padding: 15px; white-space: normal; color: #fff;">
                            <x-icon type="checkin" class="fa-fw" style="margin-right: 8px; font-size: 20px;"/>
                            <strong>{{ trans('general.checkin') }}</strong>
                        </a>
                    </div>
                @endif
            @endif
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$accessory" img_path="{{ app('accessories_upload_url') }}">
                    <x-slot:buttons>
                        <x-button.edit :item="$accessory" :route="route('accessories.edit', $accessory->id)"/>
                        <x-button.clone :item="$accessory" :route="route('clone/accessories', $accessory->id)"/>

                        <a href="{{ route('accessories.print', $accessory->id) }}" class="btn btn-sm btn-default" target="_blank" data-tooltip="true" title="Label drucken">
                            <i class="fas fa-print"></i>
                        </a>

                        @can('checkout', $accessory)
                            <x-button.checkout permission="checkout" :item="$accessory" :route="route('accessories.checkout.show', $accessory->id)" />
                        @endcan
                        <x-button.delete :item="$accessory" />
                    </x-slot:buttons>
                </x-info-panel>
            </x-box>

        </x-page-column>
    </x-container>

@endsection

@section('moar_scripts')
    @can('files', $accessory)
        @include ('modals.upload-file', ['item_type' => 'accessories', 'item_id' => $accessory->id])
    @endcan

    @include ('partials.bootstrap-table')
@endsection