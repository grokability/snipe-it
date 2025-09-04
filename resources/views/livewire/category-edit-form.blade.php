<div>
    <!-- EULA text -->
    <div class="form-group {{ $errors->has('eula_text') ? 'error' : '' }}">
        <label for="eula_text" class="col-md-3 control-label">{{ trans('admin/categories/general.eula_text') }}</label>
        <div class="col-md-7">
            <x-input.textarea
                name="eula_text"
                wire:model.live="eulaText"
                aria-label="eula_text"
                :disabled="$this->eulaTextDisabled"
            />
            <p class="help-block">{!! trans('admin/categories/general.eula_text_help') !!} </p>
            <p class="help-block">{!! trans('admin/settings/general.eula_markdown') !!} </p>
            {!! $errors->first('eula_text', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
        </div>
        @if ($this->eulaTextDisabled)
            <input type="hidden" name="eula_text" wire:model.live="eulaText" />
        @endif
    </div>

    <!-- Use default checkbox -->
    <div class="form-group">
        <div class="col-md-9 col-md-offset-3">
            @if ($defaultEulaText!='')
                <label class="form-control">
                    <input
                        type="checkbox"
                        name="use_default_eula"
                        value="1"
                        wire:model.live="useDefaultEula"
                        aria-label="use_default_eula"
                    />
                    <span>{!! trans('admin/categories/general.use_default_eula') !!}</span>
                </label>
            @else
                <label class="form-control form-control--disabled">
                    <input
                        type="checkbox"
                        name="use_default_eula"
                        value="0"
                        wire:model.live="useDefaultEula"
                        aria-label="use_default_eula"
                        disabled
                    />
                    <span>{!! trans('admin/categories/general.use_default_eula_disabled') !!}</span>
                </label>
            @endif
        </div>
    </div>

    <!-- Require Acceptance -->
    <div class="form-group">
        <div class="col-md-9 col-md-offset-3">
            <label class="form-control">
                <input
                    type="checkbox"
                    name="require_acceptance"
                    value="1"
                    wire:model.live="requireAcceptance"
                    aria-label="require_acceptance"
                />
                {{ trans('admin/categories/general.require_acceptance') }}
            </label>
        </div>
    </div>

    @if ($requireAcceptance)
        <div class="form-group">
            <div class="col-md-9 col-md-offset-3">
                <label class="form-control">
                    <input
                        type="checkbox"
                        name="alert_on_response"
                        value="1"
                        wire:model="alertOnResponse"
                    />
                    {{ trans('admin/categories/general.email_to_initiator') }}
                </label>
            </div>
        </div>
    @endif

    <!-- Email on Checkin -->
    <div class="form-group">
        <div class="col-md-9 col-md-offset-3">
            <label class="form-control">
                <input
                    type="checkbox"
                    name="checkin_email"
                    value="1"
                    wire:model.live="sendCheckInEmail"
                    aria-label="checkin_email"
                    @disabled($this->sendCheckInEmailDisabled)
                />
                {{ trans('admin/categories/general.checkin_email') }}
            </label>
            @if ($this->shouldDisplayEmailMessage)
                <div class="callout callout-info">
                    <i class="far fa-envelope"></i>
                    <span>{{ $this->emailMessage }}</span>
                </div>
            @endif
            @if ($this->sendCheckInEmailDisabled)
                <input type="hidden" name="checkin_email" wire:model.live="sendCheckInEmail" />
            @endif
        </div>
    </div>
</div>
