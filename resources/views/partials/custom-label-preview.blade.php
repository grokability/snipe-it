@once
    @push('css')
        <style>
            :root {
                --clp-height: 400px;
                --clp-background-color: aliceblue;
            }

            .clp-root,
            .clp-root * {
                box-sizing: border-box;
            }

            .clp-root {
                width: 100%;
                height: var(--clp-height);
                display: flex;
                flex-direction: column;
            }

            .clp-root > .clp-top {
                display: flex;
                flex-direction: row;
                align-items: end;
                justify-content: flex-end;
                margin-bottom: 6px;
            }

            .clp-root > .clp-top > .clp-pop-button {
                padding: 3px 6px;
            }

            .clp-root > iframe {
                flex: 1;
                width: 100%;
                border: 0;
                overflow: auto;
                background-color: var(--clp-background-color);
            }
        </style>
    @endpush
@endonce

@push('js')
    <script>
        document.addEventListener('alpine:init', () => {

            Alpine.data('custom_label_preview', () => ({

                _form: null,

                _init: function () {
                    const shell = document.querySelector('.label-customizer-shell');
                    this._form = document.querySelector('.label-customizer-shell form');

                    if (!this._form) {
                        console.warn('custom_label_preview: form not found');
                        return;
                    }

                    this.updateURL();

                    this._form.addEventListener('change', this.updateURL.bind(this));
                    this._form.addEventListener('input', this.updateURL.bind(this));
                },

                updateURL: function () {
                    const fields = $(this._form).serializeArray();

                    const payload = Object.assign({}, ...fields
                        .filter((value) => value.name !== '_token')
                        .map((value) => ({[value.name]: value.value}))
                    );

                    const template = payload.template;

                    if (!template) {
                        console.warn('custom_label_preview: missing template');
                        return;
                    }

                    this.previewURL = '{{ route("labels.customizer-preview", ["labelName" => ":label"]) }}'
                        .replace(':label', template.replaceAll('\\', '/'))
                        .concat('?' + $.param(payload) + '#toolbar=0');
                },

                _previewURL: '',
                get previewURL() {
                    return this._previewURL;
                },
                set previewURL(url) {
                    this._previewURL = url;

                    if (this._popped && !this._popped.closed) {
                        this._popped.location = this.previewURL;
                    }
                },

                _popped: null,
                popout: function () {
                    if (!this.previewURL) return;

                    this._popped = window.open(this.previewURL);
                }

            }));

        });
    </script>
@endpush

<div x-data="custom_label_preview()" x-init="_init()" class="clp-root">
    <div class="clp-top">
        <button class="clp-pop-button btn btn-default" x-on:click.prevent="popout()" title="Pop Out">
            <i class="fa-solid fa-maximize"></i>
        </button>
    </div>
    <iframe x-bind:src="previewURL"></iframe>
</div>