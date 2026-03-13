@once
    @push('css')
        <style>
            :root {
                --l2p-height: 200px;
                --l2p-background-color: aliceblue;
            }

            .l2p-root,
            .l2p-root * {
                box-sizing: border-box;
            }

            .l2p-root {
                width: 100%;
                height: var(--l2p-height);
                display: flex;
                flex-direction: column;
            }

            .l2p-root > .l2p-top {
                display: flex;
                flex-direction: row;
                align-items: end;
            }

            .l2p-root > .l2p-top > label {
                flex: 1;
                font-size: 0.9em;
                padding: 0;
                margin: 0;
            }

            .l2p-root > .l2p-top > .l2p-pop-button {
                padding: 3px 6px;
            }

            .l2p-root > iframe {
                flex: 1;
                overflow: auto;
                background-color: var(--l2p-background-color);
            }
        </style>
    @endpush
@endonce

@push('js')
    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.data('label2_preview', () => ({

                _form: null,
                _init: function() {
                    this._form = this.$root.closest('form');
                    this._form.addEventListener('change', this.updateURL.bind(this));
                },

                updateURL: function() {

                    let params = {
                        settings: Object.assign({}, ...$(this._form)
                            .serializeArray()
                            .filter((value, index, all) => value.name.includes('label2_'))
                            .map((value, index, all) => ({[value.name]: value.value}))
                            )
                    };

                    let template = params.settings.label2_template;
                    if (!template) return;

                    this.previewURL = '{{ route("labels.show", ["labelName" => ":label"]) }}'
                        .replace(':label', template.replaceAll('\\', '/'))
                        .concat('?', $.param(params), '#toolbar=0');
                },

                _previewURL: '',
                get previewURL() { return this._previewURL; },
                set previewURL(url) {
                    this._previewURL = url;
                    if (this._popped) this._popped.location = this.previewURL;
                },

                _popped: null,
                popout: function() { this._popped = window.open(this.previewURL); }
            }));

        });
    </script>
@endpush

<div x-data="label2_preview" x-init="_init" class="l2p-root">
    <div class="l2p-top">
        <button class="l2p-pop-button btn btn-default" x-on:click.prevent="popout" title="Pop Out"><i class="fa-solid fa-maximize"></i></button>
    </div>
    <iframe id="label2-preview" x-bind:src="previewURL"></iframe>
</div>
