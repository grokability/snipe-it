          <!-- Improvement Type -->
          <div class="form-group {{ $errors->has('asset_maintenance_type') ? ' has-error' : '' }}">
              <label for="asset_maintenance_type" class="col-md-3 control-label">{{ trans('admin/maintenances/form.asset_maintenance_type') }}
              </label>
              <div class="col-md-7">
                  <x-input.select
                      name="asset_maintenance_type"
                      :options="$maintenanceType"
                      :selected="old('asset_maintenance_type', $item->asset_maintenance_type)"
                      :required="Helper::checkIfRequired($item, 'asset_maintenance_type')"
                      data-placeholder="{{ trans('admin/maintenances/form.select_type')}}"
                      includeEmpty="true"
                      style="width:100%;"
                      aria-label="asset_maintenance_type"
                  />
                  {!! $errors->first('asset_maintenance_type', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
              </div>
          </div>
