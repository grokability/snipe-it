<!-- Requestable -->
<div class="form-group">
    <div class="col-sm-offset-3 col-md-9">
        <label class="form-control" for="requestable">
        <input type="checkbox" value="1" name="requestable" id="requestable" {{ old('requestable', $item->requestable) == '1' ? ' checked="checked"' : '' }}> {{ $requestable_text }}
        </label>

    </div>
</div>
