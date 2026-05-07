<div class="row">
    <div class="col-md-12">
        <dl {{ $attributes->merge(['class' => 'table-display']) }}>
            {{ $slot }}
        </dl>
    </div>
</div>