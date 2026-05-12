@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.checkout') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>

        .input-group {
            padding-left: 0px !important;
        }
    </style>

    <div class="row">
        <!-- left column -->
        <div class="col-md-7">
            <div class="box box-default">
                <form class="form-horizontal" method="post" action="" autocomplete="off">
                    <div class="box-header with-border">
                        <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} {{ $asset->asset_tag }}</h2>
                    </div>
                    <div class="box-body">
                        {{csrf_field()}}
                        @if ($asset->company)
                            <!-- accessory name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ trans('general.company') }}</label>
                                <div class="col-md-6">
                                    <p class="form-control-static">{!! $asset->company->present()->formattedNameLink  !!}</p>
                                </div>
                            </div>
                        @endif


                        @if ($asset->model->category)
                            <!-- category name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ trans('general.category') }}</label>
                                <div class="col-md-6">
                                    <p class="form-control-static">{!! $asset->model->category->present()->formattedNameLink  !!}</p>
                                </div>
                            </div>
                        @endif

                        <!-- AssetModel name -->
                        <div class="form-group">
                            <label for="model" class="col-md-3 control-label">
                                {{ trans('admin/hardware/form.model') }}
                            </label>
                            <div class="col-md-8">
                                <p class="form-control-static" style="padding-top: 7px;">
                                    @if (($asset->model) && ($asset->model->name))
                                        {{ $asset->model->name }}
                                    @else
                                        <span class="text-danger text-bold">
                                              <x-icon type="warning" />
                                              {{ trans('admin/hardware/general.model_invalid')}}
                                        </span>

                                        {{ trans('admin/hardware/general.model_invalid_fix')}}
                                        <a href="{{ route('hardware.edit', $asset->id) }}">
                                            <strong>{{ trans('admin/hardware/general.edit') }}</strong>
                                        </a>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Asset Name -->
                        <<div class="form-group {{ $errors->has('name') ? 'error' : '' }}">
                            <label for="name" class="col-md-3 control-label">{{ trans('admin/hardware/form.name') }}</label>
                            <div class="col-md-8">
                                @if (Auth::user()->hasAccess('assets.checkout'))
                                    <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $asset->name) }}" tabindex="1">
                                @else
                                    <p class="form-control-static" style="padding-top: 7px;">{{ $asset->name }}</p>
                                    <input type="hidden" name="name" value="{{ $asset->name }}">
                                @endif
                                {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}">
                            <label for="status_id" class="col-md-3 control-label">{{ trans('admin/hardware/form.status') }}</label>
                            <div class="col-md-7 required">
                                @if (Auth::user()->hasAccess('assets.checkout'))
                                    <x-input.select name="status_id" id="status_id" :options="$statusLabel_list" :selected="old('status_id', $asset->status_id)" style="width: 100%" />
                                @else
                                    <p class="form-control-static" style="padding-top: 7px;">{{ $asset->assetStatus->name ?? 'Standard' }}</p>
                                    <input type="hidden" name="status_id" value="{{ $asset->status_id }}">
                                @endif
                            </div>
                        </div>

                        @if ($asset->requestable)
                            <div class="form-group">
                                <div class="col-md-7 col-md-offset-3">
                                    <label class="form-control" for="set_not_requestable">
                                        <input
                                            type="checkbox"
                                            value="1"
                                            name="set_not_requestable"
                                            id="set_not_requestable"
                                            @checked((bool) old('set_not_requestable', true))
                                        >
                                        {{ trans('admin/hardware/general.not_requestable') }}
                                    </label>
                                </div>
                            </div>
                        @endif

                        @if (Auth::user()->hasAccess('assets.checkout'))
                            @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true'])
                            @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'style' => (session('checkout_to_type') ?: 'user') == 'user' ? '' : 'display: none;'])
                            @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.select_asset'), 'fieldname' => 'assigned_asset', 'company_id' => $asset->company_id, 'unselect' => 'true', 'style' => session('checkout_to_type') == 'asset' ? '' : 'display: none;'])
                            @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'style' => session('checkout_to_type') == 'location' ? '' : 'display: none;'])
                        @else
                            <input type="hidden" name="checkout_to_type" value="user">
                            <input type="hidden" name="assigned_user" value="{{ Auth::id() }}">

                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{ trans('general.checkout_to') }}</label>
                                <div class="col-md-8">
                                    <p class="form-control-static" style="padding-top: 7px;">
                                        <i class="fas fa-user"></i> {{ Auth::user()->present()->display_name }}
                                        <span class="text-muted"><em>(Self-Checkout)</em></span>
                                    </p>
                                </div>
                            </div>
                        @endif


                        <!-- Checkout/Checkin Date -->
                        <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                            <label for="checkout_at" class="col-md-3 control-label">
                                {{ trans('admin/hardware/form.checkout_date') }}
                            </label>
                            <div class="col-md-8">

                                <x-input.datepicker
                                        name="checkout_at"
                                        end_date="0d"
                                        col_size_class="col-md-7"
                                        :value="old('expected_checkin', date('Y-m-d'))"
                                        placeholder="{{ trans('general.select_date') }}"
                                        required="{{ Helper::checkIfRequired($item, 'checkout_at') }}"
                                />
                                {!! $errors->first('checkout_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Expected Checkin Date -->
                        <div class="form-group {{ $errors->has('expected_checkin') ? 'error' : '' }}">
                            <label for="expected_checkin" class="col-md-3 control-label">
                                {{ trans('admin/hardware/form.expected_checkin') }}
                            </label>

                            <div class="col-md-8">
                                <x-input.datepicker
                                        name="expected_checkin"
                                        col_size_class="col-md-7"
                                        :value="old('expected_checkin', $item->expected_checkin)"
                                        placeholder="{{ trans('general.select_date') }}"
                                        required="{{ Helper::checkIfRequired($item, 'expected_checkin') }}"
                                />
                                {!! $errors->first('expected_checkin', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            <label for="note" class="col-md-3 control-label">
                                {{ trans('general.notes') }}
                            </label>

                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note" @required($snipeSettings->require_checkinout_notes)
                                name="note">{{ old('note', $asset->note) }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Custom fields -->
                        @include("models/custom_fields_form", [
                                'model' => $asset->model,
                                'show_custom_fields_type' => 'checkout'
                        ])



                        @if ($asset->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1' || $asset->getEula() || ($snipeSettings->webhook_endpoint!=''))
                            <div class="form-group notification-callout" style="display:none;">
                                <div class="col-md-8 col-md-offset-3">
                                    <div class="callout callout-info">

                                        @if ($asset->requireAcceptance())
                                            <x-icon type="email"/>
                                            {{ trans('admin/categories/general.required_acceptance') }}
                                            <br>
                                        @endif

                                        @if ($asset->getEula())
                                            <x-icon type="email"/>
                                            {{ trans('admin/categories/general.required_eula') }}
                                            <br>
                                        @endif

                                        @if (($asset->model?->category) && ($asset->model->category->checkin_email))
                                            <x-icon type="email"/>
                                            {{ trans('admin/categories/general.checkin_email_notification') }}
                                            <br>
                                        @endif

                                        @if ($snipeSettings->webhook_endpoint!='')
                                            <i class="fab fa-slack" aria-hidden="true"></i>
                                            {{ trans('general.webhook_msg_note') }}
                                        @endif
                                    </div>
                                </div>

                                <!-- Sign in place checkbox -->
                                @if ($asset->requireAcceptance() || (string) $snipeSettings->require_accept_signature === '1')
                                <div id="sign_in_place_div" class="col-md-7 col-md-offset-3">
                                    <label class="form-control">
                                        <input type="checkbox" value="1" name="sign_in_place" @checked(old('sign_in_place', session('sign_in_place', false))) aria-label="sign_in_place">
                                        {{ trans('general.sign_in_place') }}
                                    </label>
                                    <p class="help-block">
                                        {{ trans('general.sign_in_place_help') }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        @endif


                    </div> <!--/.box-body-->

                    @if (Auth::user()->hasAccess('assets.checkout'))
                        <x-redirect_submit_options
                                index_route="hardware.index"
                                :button_label="trans('general.checkout')"
                                :disabled_select="!$asset->model"
                                :options="[
                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.assets')]),
                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
                'target' => $target_option ?? ''
            ]"
                        />
                    @else
                        <div class="box-footer text-right">
                            <a class="btn btn-link pull-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                            <input type="hidden" name="redirect_option" value="index">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkout') }}
                            </button>
                        </div>
                    @endif

                </form>
            </div>
        </div> <!--/.col-md-7-->

        <!-- right column -->
        <div class="col-md-5" id="current_assets_box" style="display:none;">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/users/general.current_assets') }}</h2>
                </div>
                <div class="box-body">
                    <div id="current_assets_content">
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    @include('partials/assets-assigned')
@stop
