@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ $consumable->name }}
  {{ trans('general.consumable') }} -
  ({{ trans('general.remaining_var', ['count' => $consumable->numRemaining()])  }})
  @parent
@endsection

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')

    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>

                    <x-tabs.nav-item
                            name="assigned"
                            class="active"
                            icon_type="checkedout"
                            label="{{ trans('general.assigned') }}"
                            count="{{ $consumable->numCheckedOut() }}"
                    />

                    <x-tabs.files-tab :item="$consumable" count="{{ $consumable->uploads()->count() }}"/>
                    <x-tabs.history-tab count="{{ $consumable->history()->count() }}" :model="$consumable"/>
                    <x-tabs.upload-tab :item="$consumable"/>

                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <x-tabs.pane name="assigned">

                        <x-table
                            :presenter="\App\Presenters\ConsumablePresenter::checkedOut()"
                            :api_url="route('api.consumables.show.users', $consumable->id)"
                        />

                    </x-tabs.pane>

                    <x-tabs.pane name="files">
                        <x-table.files object_type="consumables" :object="$consumable"/>
                    </x-tabs.pane>

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history :model="$consumable" :route="route('api.consumables.history', $consumable)"/>
                    </x-tabs.pane>
                    <!-- end history tab pane -->

                </x-slot:tabpanes>

            </x-tabs>
        </x-page-column>

        <x-page-column class="col-md-3">
            @if ($consumable->numRemaining() > 0)
                @if (Auth::user()->can('checkout', $consumable) || Auth::user()->can('checkoutSelf', $consumable))
                    <div style="margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-radius: 3px;">
                        <a href="{{ route('consumables.checkout.show', $consumable->id) }}" class="btn btn-sm bg-maroon btn-checkout btn-lg btn-block" style="font-size: 18px; padding: 15px; white-space: normal; color: #fff;">
                            <x-icon type="checkout" class="fa-fw" style="margin-right: 8px; font-size: 20px;"/>
                            <strong>{{ trans('general.checkout') }}</strong>
                        </a>
                    </div>
                @endif
            @endif

            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$consumable" img_path="{{ app('consumables_upload_url') }}">

                    <x-slot:buttons>
                        <x-button.edit :item="$consumable" :route="route('consumables.edit', $consumable->id)"/>
                        <x-button.clone :item="$consumable" :route="route('consumables.clone.create', $consumable->id)"/>
                        <a href="{{ route('consumables.print', $consumable->id) }}" class="btn btn-sm btn-default" target="_blank" data-tooltip="true" title="Label drucken">
                            <i class="fas fa-print"></i>
                        </a>
                        <x-button.delete :item="$consumable"/>
                        <x-button.checkout :item="$consumable" :route="route('consumables.checkout.show', $consumable->id)" />
                    </x-slot:buttons>
                    <x-slot:buttons>
                        <x-button.edit :item="$consumable" :route="route('consumables.edit', $consumable->id)"/>

                        <x-button.clone :item="$consumable" :route="route('consumables.clone.create', $consumable->id)"/>

                        <a href="{{ route('consumables.print', $consumable->id) }}" class="btn btn-sm btn-default" target="_blank" data-tooltip="true" title="Label drucken">
                            <i class="fas fa-print"></i>
                        </a>

                        <x-button.checkout permission="checkout" :item="$consumable" :route="route('consumables.checkout.show', $consumable->id)" />
                        <x-button.delete :item="$consumable" />
                    </x-slot:buttons>

                </x-info-panel>
            </x-box>
        </x-page-column>
    </x-container>

@endsection

@section('moar_scripts')
    @can('files', $consumable)
        @include ('modals.upload-file', ['item_type' => 'consumables', 'item_id' => $consumable->id])
    @endcan

    @include ('partials.bootstrap-table', ['exportFile' => 'consumable-' . $consumable->name . '-export', 'search' => false])
@endsection

