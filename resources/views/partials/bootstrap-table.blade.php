@push('css')
    <link rel="stylesheet" href="{{ url(mix('css/dist/bootstrap-table.css')) }}">

    {{-- Bootstrap's default a:hover paints links Bootstrap-blue on hover.
         For the advanced-search "clear all" pill and the individual
         tag-remove pills, we want the label chip's white text to stay
         white on hover (they already carry a colored background). --}}
    <style>
        .snipe-advanced-search-tags .snipe-advanced-search-tags-clear-all,
        .snipe-advanced-search-tags .snipe-advanced-search-tags-clear-all:hover,
        .snipe-advanced-search-tags .snipe-advanced-search-tags-clear-all:focus,
        .snipe-advanced-search-tags .snipe-advanced-search-tag-remove,
        .snipe-advanced-search-tags .snipe-advanced-search-tag-remove:hover,
        .snipe-advanced-search-tags .snipe-advanced-search-tag-remove:focus {
            color: #fff !important;
            text-decoration: none !important;
        }

        /* When the bulk-actions dropdown has a specific action picked,
           rows that don't support that action get this class so the
           operator can tell at a glance that the checkbox is inert
           for that specific action. Style is checkbox-only (dimmed +
           not-allowed cursor); the rest of the row stays fully
           legible so the operator can still read the row's data. */
        tr.bulk-action-incompatible input[type="checkbox"] {
            cursor: not-allowed;
            opacity: 0.4;
        }
    </style>
@endpush

@push('js')

<script src="{{ url(mix('js/dist/bootstrap-table.js')) }}"></script>
<script src="{{ url(mix('js/dist/bootstrap-table-locale-all.min.js')) }}"></script>

<!-- load english again here, even though it's in the all.js file, because if BS table doesn't have the translation, it otherwise defaults to chinese. See https://bootstrap-table.com/docs/api/table-options/#locale -->
<script src="{{ url(mix('js/dist/bootstrap-table-en-US.min.js')) }}"></script>

<script nonce="{{ csrf_token() }}">
    $(function () {

        if (!$.fn.bootstrapTable || $.fn.bootstrapTable.__snipeAdvancedSearchPatched) {
            return;
        }

        $.fn.bootstrapTable.__snipeAdvancedSearchPatched = true;

        var BootstrapTable = $.BootstrapTable;
        var baseBootstrapTablePrototype = Object.getPrototypeOf(BootstrapTable.prototype);
        var baseInitSearch = baseBootstrapTablePrototype.initSearch;
        var defaultAdvancedSearchOperator = 'and';
        var advancedSearchSearchText = @json(trans('general.search'));

        var advancedSearchOperatorLabel = @json(trans('general.search_operator'));
        var advancedSearchAndText = @json(trans('general.and'));
        var advancedSearchOrText = @json(trans('general.or'));
        var advancedSearchClearAllText = @json(trans('general.clear_all_filters'));
        var advancedSearchOperatorStorageKey = 'snipeit.bs.table.advancedSearchOperator';

        var normalizeAdvancedSearchOperator = function (operator) {
            return (operator || defaultAdvancedSearchOperator).toString().toLowerCase() === 'or' ? 'or' : 'and';
        };

        // Shared teardown for the transport-error state onLoadError puts up.
        // Called from onLoadSuccess (retry succeeded), onRefresh (user clicked
        // the reload button), and any future entry points that should return
        // the table to its clean baseline.
        var clearBootstrapTableLoadError = function (instance) {
            if (!instance || !instance.$el) {
                return;
            }
            var $wrap = instance.$el.closest('.bootstrap-table');
            $wrap.find('.table-load-error').remove();
            $wrap.find('.fixed-table-body').show();
            $wrap.find('.fixed-table-pagination').show();
        };

        // Both onLoadError banners (session-expired 401/419 and the generic
        // 500-level failure) share the same DOM shape, insertion point, and
        // cleanup rules. innerHtml must already be XSS-safe — callers HTML-
        // encode dynamic pieces before passing them in.
        //
        // aria-live=polite is always emitted. role="alert" alone implies
        // assertive, which interrupts screen readers mid-utterance; a table
        // that failed to load doesn't warrant that. Polite queues the
        // announcement for the next natural pause.
        var renderBootstrapTableLoadErrorCallout = function (options) {
            var $wrap = options.wrap;
            $wrap.find('.table-load-error').remove();
            $wrap.find('.fixed-table-body').hide();
            $wrap.find('.fixed-table-pagination').hide();

            // Insert below the advanced-search pill container if it's present
            // (populated by an active filter), otherwise below the toolbar.
            // Either way the banner ends up above .fixed-table-container.
            var $tagContainer = $wrap.children('.snipe-advanced-search-tags');
            var $anchor = $tagContainer.length && $tagContainer.children().length
                ? $tagContainer
                : $wrap.find('.fixed-table-toolbar').first();

            // Marker class table-load-error MUST live on the outermost wrapper
            // so the cleanup selector removes the whole banner in one shot.
            // A nested layout would leave orphan outer wells behind on each
            // re-render and stack up.
            $anchor.after(
                '<div class="row table-load-error" style="padding-left: 5px;right: 5px;"><div class="well well-sm "' +
                ' role="alert" aria-live="polite"' +
                ' style="margin: 10px; position: relative; z-index: 5; text-align: center;">' +
                options.innerHtml +
                '</div></div>',
            );
        };

        var getStoredAdvancedSearchOperator = function () {
            try {
                var storedOperator = localStorage.getItem(advancedSearchOperatorStorageKey);

                return storedOperator ? normalizeAdvancedSearchOperator(storedOperator) : null;
            } catch (error) {
                return null;
            }
        };

        var storeAdvancedSearchOperator = function (operator) {
            try {
                localStorage.setItem(advancedSearchOperatorStorageKey, normalizeAdvancedSearchOperator(operator));
            } catch (error) {
                // Ignore storage errors (private mode/quota), fallback remains in-memory.
            }
        };

            var escapeAdvancedSearchValue = function (value) {
                return $('<div/>').text(value == null ? '' : value).html();
            };

            // Attribute-context escape. Distinct from escapeAdvancedSearchValue
            // (text-context) because the text-serializer does NOT encode `"`,
            // so a value with a raw quote breaks out of `attr="..."` even after
            // .text().html() "escaping". Any concat like `data-field="' + val + '"`
            // MUST route through this, not escapeAdvancedSearchValue.
            var escapeAdvancedSearchAttr = function (value) {
                return String(value == null ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            };

            // Safely decode HTML entities in a string WITHOUT parsing it as HTML.
            // `<textarea>` innerHTML is RCDATA — the parser decodes entity
            // references like `&lt;` but does not interpret `<tag>` as an
            // element. Contrast with the naive `$('<div/>').html(value).text()`
            // pattern, which parses value as HTML into a detached div — an
            // entity-encoded payload like `&lt;img src=x onerror=alert(1)&gt;`
            // decodes into a real <img>, and browsers fire onerror even for
            // detached images. Using a textarea avoids that DOM instantiation
            // entirely.
            var decodeHtmlEntitiesSafely = function (value) {
                if (value == null) {
                    return '';
                }
                var textarea = document.createElement('textarea');
                textarea.innerHTML = String(value);
                return textarea.value;
            };

            Object.assign($.fn.bootstrapTable.locales, {
                formatAdvancedCloseButton: function () {
                    return advancedSearchSearchText;
                },
                formatAdvancedCancelButton: function () {
                    return $.fn.bootstrapTable.defaults.formatClearSearch();
                },
                formatAdvancedSearchOperator: function () {
                    return advancedSearchOperatorLabel;
                }
            });

            Object.assign($.fn.bootstrapTable.defaults, {
                advancedSearchOperator: defaultAdvancedSearchOperator,
                formatAdvancedCloseButton: $.fn.bootstrapTable.locales.formatAdvancedCloseButton,
                formatAdvancedCancelButton: $.fn.bootstrapTable.locales.formatAdvancedCancelButton,
                formatAdvancedSearchOperator: $.fn.bootstrapTable.locales.formatAdvancedSearchOperator
            });

            BootstrapTable.prototype.getAdvancedSearchOperator = function () {
                var operator = this.advancedSearchOperator || this.options.advancedSearchOperator || this.$el.data('advanced-search-filter-operator') || getStoredAdvancedSearchOperator() || defaultAdvancedSearchOperator;

                return normalizeAdvancedSearchOperator(operator);
            };

            BootstrapTable.prototype.setAdvancedSearchOperator = function (operator) {
                this.advancedSearchOperator = normalizeAdvancedSearchOperator(operator);
                this.$el.data('advanced-search-filter-operator', this.advancedSearchOperator);
                storeAdvancedSearchOperator(this.advancedSearchOperator);
            };

            BootstrapTable.prototype.collectAdvancedSearchFormData = function () {
                var filters = {};
                var operator = defaultAdvancedSearchOperator;

                $.each(this.$toolbarModal.find('.toolbar-model-form').serializeArray(), function (index, field) {
                    var value = $.trim(field.value);

                    if (field.name === '__advanced_search_operator') {
                        operator = value.toLowerCase() === 'or' ? 'or' : 'and';

                        return;
                    }

                    if (value !== '') {
                        filters[field.name] = value;
                    }
                });

                return {
                    filters: filters,
                    operator: operator
                };
            };

            BootstrapTable.prototype.getAdvancedSearchFieldTitle = function (fieldName) {
                for (var i = 0; i < this.columns.length; i++) {
                    var column = this.columns[i];

                    if (column.field === fieldName) {
                        return $('<div/>').html(column.title || fieldName).text().trim();
                    }
                }

                return fieldName;
            };

            BootstrapTable.prototype.getAdvancedSearchTagsContainer = function () {
                var $tableWrapper = this.$el.closest('.bootstrap-table');

                if (!$tableWrapper.length) {
                    return $();
                }

                var $container = $tableWrapper.children('.snipe-advanced-search-tags');

                if (!$container.length) {
                    $container = $('<div class="snipe-advanced-search-tags" style="margin: 8px 0;"></div>');

                    if ($tableWrapper.children('.fixed-table-container').length) {
                        $container.insertBefore($tableWrapper.children('.fixed-table-container').first());
                    } else {
                        $container.insertBefore(this.$el);
                    }
                }

                return $container;
            };

            BootstrapTable.prototype.getAdvancedSearchButton = function () {
                var $tableWrapper = this.$el.closest('.bootstrap-table');

                if (!$tableWrapper.length) {
                    return $();
                }

                // Try to find the button by data attribute first (most reliable)
                var $button = $tableWrapper.find('button[data-toggle="advanced-search"]').first();

                // Fallback: look in toolbar by the fa-search-plus icon
                if (!$button.length) {
                    $button = $tableWrapper.find('button:has(.fa-search-plus)').first();
                }

                return $button;
            };

            BootstrapTable.prototype.updateAdvancedSearchButtonState = function () {
                var hasFilters = !$.isEmptyObject(this.filterColumnsPartial);
                var $button = this.getAdvancedSearchButton();

                if ($button.length) {
                    $button.toggleClass('active', hasFilters);
                }
            };

            BootstrapTable.prototype.renderAdvancedSearchTags = function () {
                var _this = this;
                var filters = this.filterColumnsPartial;
                var $tagContainer = this.getAdvancedSearchTagsContainer();

                if ($.isEmptyObject(filters)) {
                    $tagContainer.empty();
                    this.updateAdvancedSearchButtonState();
                    return;
                }

                var colMap = {};
                this.columns.forEach(c => colMap[c.field] = c.title);
                var op = this.getAdvancedSearchOperator();
                var html = '<span class="label label-warning" style="font-size: 11px; margin-right:6px;display:inline-block;margin-bottom:6px;">' +
                    advancedSearchOperatorLabel + ': ' + (op === 'or' ? advancedSearchOrText : advancedSearchAndText) + '</span>';

                Object.keys(filters).forEach(f => {
                    // The filter name comes from the URL (deeplink hydration in
                    // the block below), so it is attacker-controllable. Both
                    // insertion points have to encode: the visible label needs
                    // text-context encoding, the data-field attribute needs
                    // attribute-context encoding (which additionally encodes
                    // the double-quote so a crafted name cannot close the
                    // attribute early and inject arbitrary HTML that
                    // jQuery.html() would then parse and execute).
                    var safeLabel = escapeAdvancedSearchValue(colMap[f] || f);
                    var safeField = escapeAdvancedSearchAttr(f);
                    html += '<span class="label label-primary" style="font-size: 11px; margin-right:6px;display:inline-block;margin-bottom:6px;"><b>' +
                        safeLabel + ':</b> ' + escapeAdvancedSearchValue(filters[f]) +
                        ' <a href="javascript:void(0)" class="snipe-advanced-search-tag-remove" data-field="' + safeField +
                        '" style="color:#fff;margin-left:6px;text-decoration:none;">&times;</a></span>';
                });

                // "Clear all" pill: same shortcut as opening the modal and hitting
                // the cancel button, but done from the tags row so the user doesn't
                // have to open the modal just to wipe every filter.
                var safeClearAllLabel = escapeAdvancedSearchValue(advancedSearchClearAllText);
                html += '<a href="javascript:void(0)" class="label label-danger snipe-advanced-search-tags-clear-all"' +
                    ' title="' + safeClearAllLabel + '"' +
                    ' aria-label="' + safeClearAllLabel + '"' +
                    ' style="font-size: 11px; margin-right:6px; display:inline-block; margin-bottom:6px; color:#fff; text-decoration:none; cursor:pointer;">' +
                    '<i class="fas fa-trash" aria-hidden="true" style="margin-right:4px;"></i>' + safeClearAllLabel +
                    '</a>';

                $tagContainer
                    .html(html)
                    .off('click.snipeAdvancedSearchTags')
                    .on('click.snipeAdvancedSearchTags', '.snipe-advanced-search-tag-remove', function (e) {
                        e.preventDefault();
                        var field = $(this).data('field');
                        if (field && _this.filterColumnsPartial) {
                            delete _this.filterColumnsPartial[field];
                            _this.options.pageNumber = 1;
                            _this.initSearch();
                            _this.updatePagination();
                            _this.trigger('column-advanced-search', _this.filterColumnsPartial, _this.getAdvancedSearchOperator());

                            _this.renderAdvancedSearchTags();
                        }
                    })
                    .on('click.snipeAdvancedSearchTags', '.snipe-advanced-search-tags-clear-all', function (e) {
                        e.preventDefault();
                        // Reuse cancelAdvancedSearch: it already wipes filterColumnsPartial,
                        // resets the modal's inputs (in case the user opens it later), fires
                        // the column-advanced-search event (so our patched updateHistoryState
                        // strips filter[...] from the URL), and refetches via initSearch.
                        if (typeof _this.cancelAdvancedSearch === 'function') {
                            _this.cancelAdvancedSearch();
                        }
                    });

                this.updateAdvancedSearchButtonState();
            };

            BootstrapTable.prototype.applyAdvancedSearch = function () {
                var toolbarState = this.collectAdvancedSearchFormData();

                this.filterColumnsPartial = toolbarState.filters;
                this.setAdvancedSearchOperator(toolbarState.operator);

                if (this.options.sidePagination !== 'server') {
                    this.options.pageNumber = 1;
                    this.initSearch();
                    this.updatePagination();
                    this.trigger('column-advanced-search', this.filterColumnsPartial, this.getAdvancedSearchOperator());
                }

                this.renderAdvancedSearchTags();
                this.updateAdvancedSearchButtonState();

                this.hideToolbarModal();
            };

            BootstrapTable.prototype.cancelAdvancedSearch = function () {
                this.filterColumnsPartial = {};
                this.options.pageNumber = 1;
                this.initSearch();
                this.updatePagination();
                this.trigger('column-advanced-search', this.filterColumnsPartial, this.getAdvancedSearchOperator());
                this.renderAdvancedSearchTags();
                this.updateAdvancedSearchButtonState();
                // Reset the form inputs so the fields appear empty when re-opened
                if (this.$toolbarModal) {
                    this.$toolbarModal.find('.toolbar-model-form')[0] && this.$toolbarModal.find('.toolbar-model-form')[0].reset();
                    this.$toolbarModal.find('input[type="text"]').val('');
                }
                this.hideToolbarModal();
            };

            BootstrapTable.prototype.createToolbarForm = function () {
                var filterColumnsPartial = this.filterColumnsPartial || {};
                var html = [`<form class="form-horizontal toolbar-model-form" action="${this.options.actionForm}">`];
                var operator = this.getAdvancedSearchOperator();

                html.push('<div class="form-group row"><div class="col-sm-12"><p class="help-block"><i class="fa fa-solid fa-lightbulb text-info" aria-hidden="true"></i> {!! trans('general.search_tip') !!}</p></div></div>');

                html.push(`
                    <div class="form-group row">
                        <label class="col-sm-4 control-label">${this.options.formatAdvancedSearchOperator()}</label>
                        <div class="col-sm-6">
                            <select class="form-control ${this.constants.classes.input}" name="__advanced_search_operator">
                                <option value="and"${operator === 'and' ? ' selected' : ''}>${advancedSearchAndText}</option>
                                <option value="or"${operator === 'or' ? ' selected' : ''}>${advancedSearchOrText}</option>
                            </select>
                        </div>
                    </div>
                `);

                for (var columnIndex = 0; columnIndex < this.columns.length; columnIndex++) {
                    var column = this.columns[columnIndex];

                    if (!column.checkbox && column.visible && column.searchable) {
                        // column.title is presenter-supplied and can be entity-encoded
                        // user input (e.g. AssetPresenter emits `e($field->name)` for
                        // custom fields). Decode via a textarea so we get the intended
                        // display string without ever instantiating an <img>/<script>
                        // element, then escape when interpolating into the HTML template
                        // — the label, name, and placeholder all get untrusted content.
                        var title = decodeHtmlEntitiesSafely(column.title).trim();
                        var value = filterColumnsPartial[column.field] || '';
                        // Label goes inside <label>...</label> (text context) so
                        // .text().html() suffices. name / placeholder / value
                        // all land inside attribute quotes, and the value comes
                        // from filterColumnsPartial (URL-sourced), so they need
                        // the attribute-safe encoder that also encodes `"`.
                        var safeLabelText = escapeAdvancedSearchValue(title);
                        var safeTitleAttr = escapeAdvancedSearchAttr(title);
                        var safeFieldAttr = escapeAdvancedSearchAttr(column.field);
                        var safeValueAttr = escapeAdvancedSearchAttr(value);

                        html.push(`
                            <div class="form-group row">
                                <label class="col-sm-4 control-label">${safeLabelText}</label>
                                <div class="col-sm-6">
                                    <input
                                        type="text"
                                        class="form-control ${this.constants.classes.input}"
                                        name="${safeFieldAttr}"
                                        placeholder="${safeTitleAttr}"
                                        value="${safeValueAttr}"
                                    >
                                </div>
                            </div>
                        `);
                    }
                }

                html.push('</form>');

                return html.join('');
            };

            BootstrapTable.prototype.initAdvancedSearchFooter = function () {
                var _this = this;
                var $footer = this.$toolbarModal.find('.toolbar-modal-footer');
                var $templateButton = $footer.find('.toolbar-modal-close').first();

                if (!this._advancedSearchFooterButtonTagName) {
                    this._advancedSearchFooterButtonTagName = ($templateButton.prop('tagName') || $footer.find('button,a').first().prop('tagName') || 'button').toLowerCase();
                }

                if (!this._advancedSearchFooterButtonBaseClass) {
                    var buttonClassSource = ($templateButton.attr('class') || $footer.find('button,a').first().attr('class') || '');

                    this._advancedSearchFooterButtonBaseClass = buttonClassSource
                        .replace(/\btoolbar-modal-close\b/g, '')
                        .replace(/\btoolbar-modal-cancel\b/g, '')
                        .replace(/\btoolbar-modal-search\b/g, '')
                        .replace(/\bpull-left\b/g, '')
                        .trim();
                }

                var tagName = this._advancedSearchFooterButtonTagName;
                var baseClass = this._advancedSearchFooterButtonBaseClass;

                var createFooterButton = function (text, extraClass) {
                    var $button = $('<' + tagName + '>').addClass($.trim(baseClass + ' ' + extraClass)).html(text);

                    if (tagName === 'button') {
                        $button.attr('type', 'button');
                    }

                    if (tagName === 'a') {
                        $button.attr('href', 'javascript:void(0)');
                    }

                    return $button;
                };

                var $cancelButton = createFooterButton(this.options.formatAdvancedCancelButton(), 'toolbar-modal-cancel');
                var $searchButton = createFooterButton(this.options.formatAdvancedCloseButton(), 'toolbar-modal-search');

                // Keep cancel on the left for clearer primary/secondary action separation.
                $cancelButton.addClass('pull-left');

                $footer.empty().append($cancelButton, $searchButton);

                $cancelButton.off('click').on('click', function (event) {
                    event.preventDefault();
                    _this.cancelAdvancedSearch();
                });

                $searchButton.off('click').on('click', function (event) {
                    event.preventDefault();
                    _this.applyAdvancedSearch();
                });
            };

            BootstrapTable.prototype.initToolbarModalBody = function () {
                var _this = this;

                this.$toolbarModal.find('.toolbar-modal-title').html(this.options.formatAdvancedSearch());
                this.$toolbarModal.find('.toolbar-modal-body')
                    .html(this.createToolbarForm())
                    .off('submit', '.toolbar-model-form')
                    .on('submit', '.toolbar-model-form', function (event) {
                        event.preventDefault();
                        _this.applyAdvancedSearch();
                    });

                // Let Enter keypresses reuse the same submit path so keyboard users can apply filters quickly.
                this.$toolbarModal.find('.toolbar-model-form')
                    .off('keydown.snipeAdvancedSearch')
                    .on('keydown.snipeAdvancedSearch', ':input', function (event) {
                        if (event.key !== 'Enter' || $(event.target).is('textarea')) {
                            return;
                        }

                        event.preventDefault();
                        $(this).closest('form').trigger('submit');
                    });

                this.initAdvancedSearchFooter();
            };

            BootstrapTable.prototype.initSearch = function () {
                var _this = this;

                baseInitSearch.apply(this, arguments);

                if (!this.options.advancedSearch || this.options.sidePagination === 'server') {
                    return;
                }

                var filters = $.isEmptyObject(this.filterColumnsPartial) ? null : this.filterColumnsPartial;

                if (!filters) {
                    return;
                }

                var operator = this.getAdvancedSearchOperator();

                this.data = this.data.filter(function (item, index) {
                    var matches = [];
                    var matchFound = false;
                    var allMatched = true;

                    $.each(filters, function (key, value) {
                        var searchValue = value.toLowerCase();
                        var formattedValue = item[key];
                        var headerIndex = _this.header.fields.indexOf(key);
                        var isMatch;

                        formattedValue = $.fn.bootstrapTable.utils.calculateObjectValue(_this.header, _this.header.formatters[headerIndex], [formattedValue, item, index], formattedValue);

                        if (_this.header.formatters[headerIndex]) {
                            formattedValue = $('<div>').html(formattedValue).text();
                        }

                        isMatch = headerIndex !== -1 && (typeof formattedValue === 'string' || typeof formattedValue === 'number') && "".concat(formattedValue).toLowerCase().includes(searchValue);

                        matches.push(isMatch);
                        matchFound = matchFound || isMatch;
                        allMatched = allMatched && isMatch;
                    });

                    if (!matches.length) {
                        return true;
                    }

                    return operator === 'or' ? matchFound : allMatched;
                });

                this.unsortedData = this.data.slice();
            };

        // Bootstrap-table's "print" extension races Safari's async print
        // against its own synchronous close(). doPrint calls
        // newWin.focus(); newWin.print(); newWin.close(); right after
        // document.write. When the written HTML contains <img> tags,
        // Safari kicks off fetches for them and treats the popup
        // document as not-yet-ready-to-print. The queued print() is
        // waiting on that ready state, but close() fires anyway, tears
        // down the popup, and the queued print is silently canceled.
        //
        // See https://github.com/grokability/snipe-it/issues/19443
        //
        // Deferring newWin.close() by 500ms gives Safari enough time
        // to either finish loading the images or latch the print
        // dialog against the popup before it disappears.
        if (BootstrapTable && BootstrapTable.prototype.doPrint) {
            var origDoPrint = BootstrapTable.prototype.doPrint;
            BootstrapTable.prototype.doPrint = function (data) {
                var origOpen = window.open;
                window.open = function () {
                    var w = origOpen.apply(window, arguments);
                    if (w) {
                        var realClose = w.close.bind(w);
                        w.close = function () {
                            setTimeout(realClose, 500);
                        };
                    }
                    return w;
                };
                try {
                    return origDoPrint.call(this, data);
                } finally {
                    window.open = origOpen;
                }
            };
        }

        var blockedFields = "searchable,sortable,switchable,title,visible,formatter,class".split(",");

        var keyBlocked = function(key) {
            for(var j in blockedFields) {
                if (key === blockedFields[j]) {
                    return true;
                }
            }
            return false;
        }

        /** This handles the responsive tab UI on v iew detail pages **/
        function resize() {
            if ($(window).width() < 767) {
                $('.nav-tabs-dropdown').addClass('nav-justified');
                $('.uploadtab').removeClass('pull-right');

            }
            else {
                $('.nav-tabs-dropdown').removeClass('nav-justified');
                $('.uploadtab').addClass('pull-right');
            }
        }

        // Run once on initial ready (already inside top-level ready block)
        resize();

        // Watch for window resize events
        $(window).on('resize', function () {
            resize();
        });

        //open and close tab menu
        $('.nav-tabs-dropdown').on("click", "li:not('.active') a", function (event) {
            $(this).closest('ul').removeClass("open");
        }).on("click", "li.active a", function (event) {
            $(this).closest('ul').toggleClass("open");
        });

        /** End handling the responsive tab UI on view detail pages **/

        // Stamp aria-sort on the sorted column's <th> so assistive tech can
        // announce the current sort direction. Bootstrap-table's own render
        // draws a visual arrow but doesn't emit aria state. Called from both
        // onSort (user clicked a header) and onPostHeader (initial paint or
        // layout re-init), since bootstrap-table doesn't re-render the head
        // on sort clicks.
        var updateAriaSort = function ($tableEl, sortName, sortOrder) {
            // scope="col" is emitted by the column presenters (see e.g.
            // AssetPresenter::dataTableLayout). aria-sort will move upstream
            // once we're on a bootstrap-table version that includes
            // https://github.com/wenzhixin/bootstrap-table/pull/8005; until
            // then we stamp it here.
            $tableEl.find('thead th').each(function () {
                var $th = $(this);
                if (sortName && $th.data('field') === sortName) {
                    $th.attr('aria-sort', sortOrder === 'desc' ? 'descending' : 'ascending');
                } else {
                    $th.removeAttr('aria-sort');
                }
            });
        };

        $('.snipe-table').bootstrapTable('destroy').each(function () {

            data_export_options = $(this).attr('data-export-options');
            export_options = data_export_options ? JSON.parse(data_export_options) : {};
            export_options['htmlContent'] = false; // this is already the default; but let's be explicit about it
            // DejaVuSans is registered into jsPDF's VFS via
            // jspdf-dejavu-fonts.js (bundled into public/js/dist/bootstrap-table.js
            // right after jspdf.umd.min.js — see webpack.mix.js). Referencing
            // it here is what actually swaps out the default Helvetica fallback
            // and produces readable output for Cyrillic / Greek / Hebrew / etc.
            // exports. Fixes #19270.
            export_options['jspdf'] = {
                "orientation": "l",
                "autotable": {
                        "styles": {
                            font: 'DejaVuSans',
                            fontStyle: 'normal',
                            overflow: 'linebreak'
                        },
                        tableWidth: 'wrap'
                }
            };
            // tableWidth: 'wrap',
            // ⚠️ SECURITY: DO NOT change the wrapping of `.text()` inside
            //    `htmlEncodeForExport(...)` below without reading this entire
            //    block. The bare `.text()` was the previous shape and it is
            //    an XSS.
            //
            // XSS defense on export cell data. The tableExport plugin's
            // E function (see bundled bootstrap-table.js around line 32110)
            // pipes our return value through jQuery's .html() setter on a
            // scratch <div> before serializing. If we return raw text that
            // happens to look like HTML (e.g. a column titled
            // `<img src=x onerror=alert(1)>`), that .html() call parses it
            // and instantiates a real <img onerror=...> element, firing the
            // payload as soon as the user clicks Export.
            //
            // Encoding the returned string turns any tag-shaped characters
            // into entity refs; the downstream .html() call then treats
            // them as text-content (browser text-decodes back to chars in a
            // text-node, no elements created), and the final PDF/CSV output
            // still shows the visible text the header displayed on-screen.
            //
            // Repro before the fix (kept as a regression pin):
            //   1. Create a custom field named `<img src=x onerror=alert(1)>`
            //   2. Visit the assets index (header shows the string as text)
            //   3. Export → CSV (or PDF): alert(1) fires because tableExport
            //      re-injects our returned text via .html() on a scratch div.
            // If a future edit here reintroduces the bug, that exact repro
            // will fire alert(1) again.
            var htmlEncodeForExport = function (value) {
                if (value == null) return '';
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            };
            export_options['onCellHtmlData'] = function (cell, rowIndex, colIndex, htmlData) {
                if (cell.is('th')) {
                    // ⚠️ MUST stay wrapped in htmlEncodeForExport(). See block above.
                    return htmlEncodeForExport(cell.find('.th-inner').text());
                }
                // Convert <br> tags to newlines so that line breaks in notes and
                // textarea fields survive HTML-stripping during export
                return htmlData.replace(/<br\s*\/?>/gi, '\n');
            }
            // Escape double quotes in hyperlink href and display text so that
            // Excel HYPERLINK formulas are not corrupted when values contain "
            export_options['mso'] = {
                xlsx: {
                    onHyperlink: function (cell, row, col, href, cellText, formula) {
                        var escapedHref = href.replace(/"/g, '""');
                        var escapedText = cellText.replace(/"/g, '""');
                        if (escapedText.length) {
                            return '=HYPERLINK("' + escapedHref + '","' + escapedText + '")';
                        }
                        return '=HYPERLINK("' + escapedHref + '")';
                    }
                }
            };

            // Tell tableExport how to interpret numbers in the HTML cells so the
            // XLSX export writes correct numeric values on non-US locales. Without
            // this the plugin defaults to US format ("," thousands, "." decimal)
            // and misparses cells that numberWithCommas() rendered in
            // "1.234,56" shape: 3.854,60 becomes 3.8546 (issue #19415).
            // Output stays invariant (raw "." decimal, no thousands separator)
            // because OpenXML numeric cells must be locale-neutral; Excel handles
            // display formatting from the cell style on open.
            export_options['numbers'] = {
                html: {
                    decimalMark: "{{ $snipeSettings->digit_separator == '1.234,56' ? ',' : '.' }}",
                    thousandsSeparator: "{{ $snipeSettings->digit_separator == '1.234,56' ? '.' : ',' }}"
                },
                output: {
                    decimalMark: ".",
                    thousandsSeparator: ""
                }
            };

            // This allows us to override the table defaults set below using the data-dash attributes
            var table = this;
            var data_with_default = function (key,default_value) {
                attrib_val = $(table).data(key);
                if(attrib_val !== undefined) {
                    return attrib_val;
                }
                return default_value;
            }



            var initialAdvancedSearchOperator = getStoredAdvancedSearchOperator() || normalizeAdvancedSearchOperator(data_with_default('advanced-search-operator', defaultAdvancedSearchOperator));

            $(this).data('advanced-search-filter-operator', initialAdvancedSearchOperator);

            // Capture the table element for use inside bootstrap-table callbacks.
            // bootstrap-table 1.24 invokes those callbacks with `.apply(options, args)`,
            // so `this` inside them is the options object, not the DOM element —
            // any $(this).find(...) call silently no-ops. The closure lets the
            // callbacks reach the real table.
            var $bootstrapTableEl = $(this);

            $(this).bootstrapTable({

                ajaxOptions: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                // reorderableColumns: true,
                // buttonsPrefix: "btn",
                addrbar: {{ (config('session.bs_table_addrbar') == 'true') ? 'true' : 'false'}}, // deeplink search phrases, sorting, etc
                advancedSearch: data_with_default('advanced-search', true),
                advancedSearchOperator: initialAdvancedSearchOperator,
                buttonsClass: "tableButton tableButton btn-theme hidden-print",
                buttonsOrder: [
                    'columns',
                    'btnAdd',
                    'btnShowDeleted',
                    'btnToggleCompleted',
                    'btnDue',
                    'btnOverdue',
                    'btnShowAdmins',
                    'btnShowExpiring',
                    'btnShowInactive',
                    'refresh',
                    'btnExport',
                    'export',
                    'print',
                    'fullscreen',
                    'advancedSearch',
                ],
                classes: 'table table-responsive table-striped snipe-table table-no-bordered',
                clickToSelect: data_with_default('click-to-select', true),
                cookie: true,
                cookieExpire: '2y',
                cookieStorage: '{{ config('session.bs_table_storage') }}',
                iconsPrefix: 'fa',
                maintainSelected: data_with_default('maintain-selected', true),
                minimumCountColumns: data_with_default('minimum-count-columns', 2),
                mobileResponsive: data_with_default('mobile-responsive', true),
                pagination: data_with_default('pagination', true),
                paginationFirstText: "{{ trans('general.first') }}",
                paginationLastText: "{{ trans('general.last') }}",
                paginationNextText: "{{ trans('general.next') }}",
                paginationPreText: "{{ trans('general.previous') }}",
                search: data_with_default('search', true),
                searchText: "{{ request()->get('assetTag') ?? session()->get('search') }}", // this is needed so that people who incorrectly use the topsearch as an omnibar will not have an additional filter from BS tables
                searchHighlight: data_with_default('search-highlight', true),
                showColumns: data_with_default('show-columns', true),
                showColumnsToggleAll: data_with_default('show-columns-toggle-all', true),
                showExport: data_with_default('show-export', true),
                showFullscreen: data_with_default('show-fullscreen', true),
                showPrint: data_with_default('show-print', true),
                showRefresh: data_with_default('show-refresh', true),
                showSearchClearButton: data_with_default('show-search-clear-button', true),
                sortName: data_with_default('sort-name', 'created_at'),
                sortOrder: data_with_default('sort-order', 'desc'),
                // Opt-out per table via data-sticky-header="false". The
                // dashboard widgets in particular set that flag since
                // they render inside small col-md-4 boxes where a
                // pinned header clone reads as visual noise rather than
                // helping navigate a long list.
                stickyHeader: data_with_default('sticky-header', true),
                // Push the sticky-header clone down by the top-scrollbar
                // mirror's height (14px) plus its padding-bottom (4px)
                // so the .snipe-top-scrollbar (position: sticky top: 0)
                // can sit above the sticky thead without overlapping it,
                // matching the natural unscrolled order where the mirror
                // reads immediately above the thead (#19484 follow-up).
                stickyHeaderOffsetY: 18,
                stickyHeaderOffsetLeft: parseInt($('body').css('padding-left'), 10),
                stickyHeaderOffsetRight: parseInt($('body').css('padding-right'), 10),
                trimOnSearch: false,
                undefinedText: '',
                pageList: ['10', '20', '30', '50', '100', '150', '200'{!! ((config('app.max_results') > 200) ? ",'500'" : '') !!}{!! ((config('app.max_results') > 500) ? ",'".config('app.max_results')."'" : '') !!}],
                pageSize: {{  (($snipeSettings->per_page!='') && ($snipeSettings->per_page > 0)) ? $snipeSettings->per_page : 20 }},
                paginationVAlign: 'both',
                queryParams: function (params) {
                    var newParams = {};
                    for (var i in params) {
                        if (!keyBlocked(i)) { // only send the field if it's not in blockedFields
                            newParams[i] = params[i];
                        }
                    }

                    if (newParams.filter) {
                        newParams.filter_operator = $(table).data('advanced-search-filter-operator') || data_with_default('advanced-search-operator', 'and');
                    }

                    return newParams;
                },
                formatLoadingMessage: function () {
                    return '<h2><x-icon type="spinner" /> {{ trans('general.loading') }} </h2>';
                },
                formatAdvancedCloseButton: function () {
                    return advancedSearchSearchText;
                },
                formatAdvancedCancelButton: function () {
                    return $.fn.bootstrapTable.defaults.formatClearSearch();
                },
                formatAdvancedSearchOperator: function () {
                    return advancedSearchOperatorLabel;
                },
                icons: {
                    advancedSearchIcon: 'fas fa-search-plus',
                    paginationSwitchDown: 'fa-caret-square-o-down',
                    paginationSwitchUp: 'fa-caret-square-o-up',
                    fullscreen: 'fa-expand',
                    columns: 'fa-columns',
                    print: 'fa-print',
                    refresh: 'fas fa-sync-alt',
                    export: 'fa-download',
                    clearSearch: 'fa-times',
                },
                locale: '{{ app()->getLocale() }}',
                exportOptions: export_options,
                exportTypes: ['xlsx', 'csv', 'pdf', 'json', 'xml', 'txt', 'sql', 'doc'],
                onLoadSuccess: function () { // possible 'fixme'? this might be for contents, not for headers?
                    $('[data-tooltip="true"]').tooltip(); // Needed to attach tooltips after ajax call

                    // Clear any lingering "load failed" banner + restore the table body
                    // that onLoadError hid. Bootstrap-table appends the table instance as
                    // the last callback arg and binds `this` to the options bag (not the
                    // DOM), so reach for the table element via arguments rather than
                    // $(this).
                    var instance = arguments[arguments.length - 1];
                    if (instance && instance.$el) {
                        clearBootstrapTableLoadError(instance);
                    }
                },
                onRefresh: function () {
                    // Fires the moment the user clicks the toolbar's refresh button,
                    // before the retry AJAX call is issued. Clear the callout + restore
                    // the body/pagination up-front so that even if the retry also
                    // errors, we're not stacking state on top of the previous error.
                    // If the retry succeeds, onLoadSuccess is a no-op reassertion.
                    // If the retry errors, onLoadError re-hides the body and re-shows
                    // the callout with the new status.
                    var instance = arguments[arguments.length - 1];
                    if (instance && instance.$el) {
                        clearBootstrapTableLoadError(instance);
                    }
                },
                onLoadError: function (status, jqXHR) {
                    // Fires on any real transport-level failure (500, 502, 504, network
                    // error, 419 CSRF expiry, 401 unauthenticated, etc). Snipe's API
                    // convention is to return {status: "error"} at HTTP 200 for
                    // validation/business errors, so anything reaching this callback
                    // is a genuine "the request itself failed" case that would
                    // otherwise render as an empty "No matching records found", a
                    // silent, misleading empty-state.
                    //
                    // Blade's JSON directive below is what makes each translation
                    // string safe to embed in this JS context. It produces a
                    // properly quoted JS string literal that handles any characters
                    // the translation might contain (quotes, backslashes, newlines,
                    // unicode). Every dynamic value from the server response is then
                    // HTML-encoded via jQuery's .text().html() idiom before we
                    // concat it into the callout markup, so a poisoned
                    // responseJSON.message cannot inject script tags or event handlers.
                    //
                    // `this` inside bootstrap-table event callbacks is the options
                    // bag, not the DOM element. The library appends the table
                    // instance as the trailing callback arg, so we reach the
                    // wrapper via arguments[last].$el.
                    var instance = arguments[arguments.length - 1];
                    if (!instance || !instance.$el) {
                        return;
                    }
                    var $wrap = instance.$el.closest('.bootstrap-table');

                    // 401 / 419 mean the user's session died; show the session-
                    // expired message and reload so the login redirect can take over.
                    if (jqXHR && (jqXHR.status === 401 || jqXHR.status === 419)) {
                        renderBootstrapTableLoadErrorCallout({
                            wrap: $wrap,
                            innerHtml:
                                '<i class="fa fa-exclamation-triangle text-warning" aria-hidden="true" style="margin-right: 6px;"></i>' +
                                $('<div/>').text(@json(trans('table.load_error_session_expired'))).html(),
                        });
                        window.setTimeout(function () {
                            window.location.reload();
                        }, 1500);
                        return;
                    }

                    // Deliberately do NOT surface the raw server response body /
                    // statusText / responseJSON.message. On PHP fatals, Laravel's
                    // debug renderer can leak PDO DSN strings (with hostnames), file
                    // paths from the stack trace, and other environment detail that
                    // shouldn't reach a browser. The HTTP status alone is enough
                    // signal for the user to know something's wrong and enough
                    // signal for an admin to correlate in the app log.
                    var httpStatus = (jqXHR && jqXHR.status) || status || '?';

                    renderBootstrapTableLoadErrorCallout({
                        wrap: $wrap,
                        innerHtml:
                            '<div style="font-size: 1.35em;">' +
                            '<i class="fa fa-exclamation-triangle text-warning" aria-hidden="true" style="margin-right: 6px;"></i>' +
                            '<strong>' + $('<div/>').text(@json(trans('table.load_error_title'))).html() + '</strong>' +
                            '</div>' +
                            '<div style="margin-top: 8px;">' +
                            $('<div/>').text(@json(trans('table.load_error_body'))).html() +
                            ' <code>' + $('<div/>').text(@json(trans('table.load_error_http_status'))).html() + ': ' +
                            $('<div/>').text(httpStatus).html() + '</code>' +
                            '</div>',
                    });
                },
                onSort: function (sortName, sortOrder) {
                    // User-triggered sort: bootstrap-table re-renders the body
                    // (client-side sort) or re-queries the server, but does
                    // NOT re-render the <thead>, so onPostHeader won't fire
                    // and aria-sort would go stale. Update the header directly.
                    updateAriaSort($bootstrapTableEl, sortName, sortOrder);
                },
                onPostHeader: function () {
                    // Initial header render (and any layout re-init) — reflect
                    // whatever sort state the table booted with, so a table
                    // configured with data-sort-name/data-sort-order announces
                    // correctly on first paint too.
                    var options = $bootstrapTableEl.bootstrapTable('getOptions');
                    updateAriaSort($bootstrapTableEl, options && options.sortName, options && options.sortOrder);

                    var lookup = {};
                    var lookup_initialized = false;
                    var ths = $('th');
                    var toolbar_buttons = $('.tableButton');

                    ths.each(function (index, element) {
                        th = $(element);
                        //only populate the lookup table once; don't need to keep doing it.
                        if (!lookup_initialized) {
                            // th -> tr -> thead -> table
                            var table = th.parent().parent().parent()
                            var column_data = table.data('columns')

                            for (var column in column_data) {
                                lookup[column_data[column].field] = column_data[column].titleTooltip;
                            }

                            lookup_initialized = true
                        }

                        field = th.data('field'); // find fieldname this column refers to
                        title = lookup[field];

                        if (title) {
                            th.attr('data-toggle', 'tooltip');
                            th.attr('data-tooltip', 'true');
                            th.attr('data-placement', 'top');
                            th.tooltip({container: 'body', title: title});

                        }
                    });

                    // Add tooltips to the toolbar buttons too
                    toolbar_buttons.each(function (index, element) {
                        tableButton = $(element);
                        title = tableButton.attr('title');
                        override_class = tableButton.attr('class');

                        if (title) {
                            // Keep this commented out so that we don't interfere with the dropdown toggle for columns, etc
                            // tableButton.attr('data-toggle', 'tooltip');
                            tableButton.attr('data-tooltip', 'true');
                            tableButton.attr('data-placement', 'auto');

                            // This prevents the slight button jitter on the mouseovees on the dashboard
                            tableButton.tooltip({container: 'body', title: title});

                            // This handles the case where we want a different color button than the default
                            if ((override_class) && ((override_class.indexOf('btn-info') >= 0)) || (override_class.indexOf('btn-danger') >= 0)) {
                                tableButton.removeClass('btn-primary');
                            }
                        }
                    });

                },
                formatNoMatches: function () {
                    // Per-table override via `data-empty-message="..."`
                    // so callers can swap the generic
                    // "no matching records" line for something more
                    // reassuring on widgets where empty is a good state
                    // (e.g. dashboard low-stock: nothing below threshold
                    // is a happy path, not an error).
                    var customEmpty = data_with_default('empty-message', null);
                    if (customEmpty) {
                        return '<span class="snipe-table-empty-state">' + customEmpty + '</span>';
                    }
                    return '{{ trans('table.no_matching_records') }}';
                }

            });

            var bootstrapTableInstance = $(this).data('bootstrap.table');

            if (bootstrapTableInstance && typeof bootstrapTableInstance.renderAdvancedSearchTags === 'function') {
                bootstrapTableInstance.renderAdvancedSearchTags();
            }

            // -----------------------------------------------------------------
            // Advanced-search URL deeplink (prototype: assets/hardware only).
            //
            // - `?filter[field]=value` per-field + `?filter_operator=and|or`,
            //   matching the API's own querystring so what the browser shows
            //   is what the server actually receives.
            // - Distinct from `?search=` (basic search) so both can coexist.
            // - Opt-in via `data-advanced-search-deeplink="true"` on the table.
            //
            // Two interacting quirks make this non-trivial:
            //
            // 1. Snipe's override of applyAdvancedSearch (this file, line ~220)
            //    is a no-op for sidePagination='server' beyond setting state
            //    and rendering pills — it never refetches, and never fires the
            //    `column-advanced-search` event. So a `on('column-advanced-search')`
            //    listener won't hear anything when the user applies via the
            //    modal on a server-side table.
            //
            // 2. addrbar (bootstrap-table's URL extension) owns the query
            //    string. It re-pushes `page`/`size`/`order`/`sort`/`search`
            //    on every onLoadSuccess. If we just replaceState() our own
            //    `filter[...]`, the next refetch clobbers them.
            //
            // Fix: patch two methods on this specific plugin instance.
            //
            //   - applyAdvancedSearch: after Snipe's version runs, force a
            //     refetch + fire the event on server-side. That triggers the
            //     addrbar → updateHistoryState path, which we've also patched.
            //
            //   - updateHistoryState: before addrbar's push, strip any stale
            //     `filter[...]`/`filter_operator` from its cached URLSearchParams
            //     and re-serialize from the current filterColumnsPartial. This
            //     handles adds, edits, and removes (empty filters remove keys).
            // -----------------------------------------------------------------
            if (bootstrapTableInstance && data_with_default('advanced-search-deeplink', false)) {
                var advDeeplinkKeyPrefix = 'filter';
                var advDeeplinkOpParam = 'filter_operator';
                var advDeeplinkKeyRegex = new RegExp('^' + advDeeplinkKeyPrefix + '\\[(.+)\\]$');

                // --- Patch updateHistoryState: emit filter[...] alongside addrbar's keys.
                if (typeof bootstrapTableInstance.updateHistoryState === 'function') {
                    var origUpdateHistoryState = bootstrapTableInstance.updateHistoryState;
                    bootstrapTableInstance.updateHistoryState = function (prefix) {
                        var params = this.searchParams;

                        // Strip stale filter[...] and filter_operator so cleared
                        // filters don't linger from a previous state.
                        if (params) {
                            var toDelete = [];
                            params.forEach(function (value, key) {
                                if (key === advDeeplinkOpParam || advDeeplinkKeyRegex.test(key)) {
                                    toDelete.push(key);
                                }
                            });
                            toDelete.forEach(function (key) { params.delete(key); });

                            // Re-add from current filterColumnsPartial state.
                            var filters = this.filterColumnsPartial || {};
                            var count = 0;
                            Object.keys(filters).forEach(function (field) {
                                var val = filters[field];
                                if (val !== undefined && val !== null && val !== '') {
                                    params.set(advDeeplinkKeyPrefix + '[' + field + ']', val);
                                    count++;
                                }
                            });
                            if (count > 0 && typeof this.getAdvancedSearchOperator === 'function') {
                                params.set(advDeeplinkOpParam, this.getAdvancedSearchOperator());
                            }
                        }

                        return origUpdateHistoryState.apply(this, arguments);
                    };
                }

                // --- Patch applyAdvancedSearch: server-side apply must refetch
                //     + fire the event so addrbar (and any listeners) run. Also
                //     clear the basic-search input so the two modes stay mutually
                //     exclusive (the backend already prefers `filter` over `search`,
                //     but leaving text in the basic input is a confusing UX and can
                //     let a stray search word survive into the next state change).
                if (typeof bootstrapTableInstance.applyAdvancedSearch === 'function') {
                    var origApplyAdvancedSearch = bootstrapTableInstance.applyAdvancedSearch;
                    bootstrapTableInstance.applyAdvancedSearch = function () {
                        origApplyAdvancedSearch.apply(this, arguments);
                        if (this.options.sidePagination === 'server') {
                            // Clear basic-search state without going through
                            // resetSearch()/onSearch() — that path would fire a
                            // separate refetch, and would re-enter our own
                            // onSearch override below.
                            var utils = $.fn.bootstrapTable && $.fn.bootstrapTable.utils;
                            if (utils && typeof utils.getSearchInput === 'function') {
                                var $searchInput = utils.getSearchInput(this);
                                if ($searchInput && $searchInput.length) {
                                    $searchInput.val('');
                                }
                            }
                            this.searchText = '';
                            this.options.searchText = '';

                            this.options.pageNumber = 1;
                            this.refresh();
                            this.trigger('column-advanced-search', this.filterColumnsPartial, this.getAdvancedSearchOperator());
                        }
                    };
                }

                // --- Patch onSearch: basic search clears advanced filters so the
                //     two modes stay mutually exclusive. Runs BEFORE the plugin's
                //     onSearch → initSearch → server refetch, so the AJAX that
                //     ships in the same tick already has an empty filterColumnsPartial.
                if (typeof bootstrapTableInstance.onSearch === 'function') {
                    var origOnSearch = bootstrapTableInstance.onSearch;
                    bootstrapTableInstance.onSearch = function (event, overwriteSearchText) {
                        var newText = '';
                        if (event && event.currentTarget) {
                            newText = String($(event.currentTarget).val() || '').trim();
                        }
                        // Only clear filters when the user is actively typing a
                        // non-empty search. Empty transitions (resetSearch(''), a
                        // stray blur on an already-empty input) leave filters alone.
                        if (
                            newText !== ''
                            && this.filterColumnsPartial
                            && Object.keys(this.filterColumnsPartial).length > 0
                        ) {
                            this.filterColumnsPartial = {};
                            if (typeof this.renderAdvancedSearchTags === 'function') {
                                this.renderAdvancedSearchTags();
                            }
                            if (typeof this.updateAdvancedSearchButtonState === 'function') {
                                this.updateAdvancedSearchButtonState();
                            }
                        }
                        return origOnSearch.apply(this, arguments);
                    };
                }

                // --- Read path: rehydrate from URL if filter[...] is present.
                var initialFilters = {};
                var initialOp = null;
                var initialParams = new URLSearchParams(window.location.search);
                initialParams.forEach(function (value, key) {
                    var m = key.match(advDeeplinkKeyRegex);
                    if (m) {
                        initialFilters[m[1]] = value;
                    } else if (key === advDeeplinkOpParam) {
                        initialOp = value;
                    }
                });

                if (Object.keys(initialFilters).length > 0) {
                    bootstrapTableInstance.filterColumnsPartial = Object.assign({}, initialFilters);
                    if (initialOp && typeof bootstrapTableInstance.setAdvancedSearchOperator === 'function') {
                        bootstrapTableInstance.setAdvancedSearchOperator(initialOp);
                    }

                    // Refetch with the seeded filter. onLoadSuccess → patched
                    // updateHistoryState → URL is re-emitted with filter[...] intact.
                    bootstrapTableInstance.refresh();

                    if (typeof bootstrapTableInstance.renderAdvancedSearchTags === 'function') {
                        bootstrapTableInstance.renderAdvancedSearchTags();
                    }
                    if (typeof bootstrapTableInstance.updateAdvancedSearchButtonState === 'function') {
                        bootstrapTableInstance.updateAdvancedSearchButtonState();
                    }
                }
            }

            // Add btn-advanced-search class to the advanced search button for styling
            if (bootstrapTableInstance) {
                // Use a small delay to ensure toolbar is fully rendered
                setTimeout(function () {
                    var $advancedSearchBtn = bootstrapTableInstance.getAdvancedSearchButton();
                    if ($advancedSearchBtn.length) {
                        $advancedSearchBtn.addClass('btn-advanced-search');

                        // Add data attribute if not present
                        if (!$advancedSearchBtn.attr('data-toggle')) {
                            $advancedSearchBtn.attr('data-toggle', 'advanced-search');
                        }

                        // Initialize button state
                        bootstrapTableInstance.updateAdvancedSearchButtonState();
                    }
                }, 50);
            }

        });

        bindBulkEditSelectionHandler();
        initializeBootstrapTableSearchUi();
    });


    // User table buttons
    window.userButtons = () => ({
        @can('create', \App\Models\User::class)
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('users.create') }}';
            },
            attributes: {
                title: '{{ trans('general.create') }}',
                class: 'btn-warning',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        @endcan

        btnExport: {
            text: '{{ trans('general.export_all_to_csv') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('users.export') }}';
            },
            attributes: {
                title: '{{ trans('general.export_all_to_csv') }}',
            }
        },

        btnShowAdmins: {
            text: '{{ trans('general.show_admins') }}',
            icon: 'fa-solid fa-crown',
            event () {
                window.location.href = '{{ (request()->input('admins') == "true") ? route('users.index') : route('users.index', ['admins' => 'true']) }}';
            },
            attributes: {
                title: '{{ trans('general.show_admins') }}',
                class: '{{ (request()->input('admins') == "true") ? ' btn-selected text-danger' : '' }}'
            }
        },

        btnShowDeleted: {
            text: '{{ (request()->input('status') == "deleted") ? trans('admin/users/table.show_current') : trans('admin/users/table.show_deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "deleted") ? route('users.index') : route('users.index', ['status' => 'deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "deleted") ? ' btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "deleted") ? trans('admin/users/table.show_current') : trans('admin/users/table.show_deleted') }}',

            }
        },

    }); // end user table buttons

    // Oauth table buttons
    window.oauthButtons = () => ({

        btnAdd: {
            text: '{{ (request()->input('status') == "deleted") ? trans('admin/users/table.show_current') : trans('admin/users/table.show_deleted') }}',
            icon: 'fa fa-plus',
            attributes: {
                event() {
                    wire:click = "$dispatch('openModal')"
                    onclick = "$('#modal-create-client').modal('show');"
                },

                title: '{{ trans('general.create') }}',
                class: 'btn-warning',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            },
        },

    }); // end user table buttons


    @can('create', \App\Models\Company::class)
    // Company table buttons
    window.companyButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('companies.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },

    }); // End company table buttons
    @endcan


    @can('create', \App\Models\Groups::class)
    // Groups table buttons
    window.groupButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('groups.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },

    }); // End Groups table buttons
    @endcan


    // Asset table buttons
    window.assetButtons = () => ({
        @can('create', \App\Models\Asset::class)
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('hardware.create') }}';
            },
            attributes: {
                title: '{{ trans('general.create') }}',
                class: 'btn-warning',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        @endcan

        @can('update', \App\Models\Asset::class)
        btnAddMaintenance: {
            text: '{{ trans('button.add_maintenance') }}',
            icon: 'fa-solid fa-screwdriver-wrench',
            event () {
                window.location.href = '{{ route('maintenances.create', ['asset_id' => (isset($asset)) ? $asset->id :'' ]) }}';
            },
            attributes: {
                title: '{{ trans('button.add_maintenance') }}',
            }
        },
        @endcan


        btnExport: {
            text: '{{ trans('admin/hardware/general.custom_export') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('reports/custom') }}';
            },
            attributes: {
                title: '{{ trans('admin/hardware/general.custom_export') }}',
            }
        },

        btnShowDeleted: {
            text: '{{ (request()->input('status_type') == "Deleted") ? trans('general.list_all') : trans('general.deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status_type') == "Deleted") ? route('hardware.index') : route('hardware.index', ['status_type' => 'Deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status_type') == "Deleted") ? 'btn-selected' : '' }}',
                title: '{{ (request()->input('status_type') == "Deleted") ? trans('general.list_all') : trans('general.deleted') }}',

            }
        },
    });

    @can('create', \App\Models\Location::class)
    // Location table buttons
    window.locationButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('locations.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },

        btnShowDeleted: {
            text: '{{ (request()->input('status') == "deleted") ? trans('general.show_current') : trans('general.show_deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "deleted") ? route('locations.index') : route('locations.index', ['status' => 'deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "deleted") ? 'btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "deleted") ? trans('general.show_current') : trans('general.show_deleted') }}',

            }
        },
    });
    @endcan

    @can('create', \App\Models\Accessory::class)
    // Accessory table buttons
    window.accessoryButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('accessories.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Depreciation::class)
    // Accessory table buttons
    window.depreciationButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('depreciations.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\CustomField::class)
    // Accessory table buttons
    window.customFieldButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('fields.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan


    @can('create', \App\Models\CustomFieldset::class)
    // Accessory table buttons
    window.customFieldsetButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('fieldsets.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Component::class)
    // Component table buttons
    window.componentButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('components.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        btnExport: {
            text: '{{ trans('general.custom_component_report') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('reports.custom.component') }}';
            },
            attributes: {
                title: '{{ trans('general.custom_component_report') }}',
            }
        },
    });
    @endcan

    @can('create', \App\Models\Consumable::class)
    // Consumable table buttons
    window.consumableButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('consumables.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Manufacturer::class)
    // Manufacturer table buttons
    window.manufacturerButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('manufacturers.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            },
        },

        btnShowDeleted: {
            text: '{{ (request()->input('status') == "Deleted") ? trans('general.list_all') : trans('general.deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "deleted") ? route('manufacturers.index') : route('manufacturers.index', ['status' => 'deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "deleted") ? 'btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "deleted") ? trans('general.list_all') : trans('general.deleted') }}',

            }
        },
    });
    @endcan

    @can('create', \App\Models\Supplier::class)
    // Consumable table buttons
    window.supplierButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('suppliers.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\MaintenanceType::class)
    // Maintenance Type table buttons
    window.maintenanceTypeButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('maintenance-types.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Department::class)
    // Department table buttons
    window.departmentButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('departments.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Department::class)
    // Custom Field table buttons
    window.departmentButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('departments.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('update', \App\Models\Asset::class)
    // Custom Field table buttons
    window.maintenanceButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('maintenances.create', ['asset_id' => (isset($asset)) ? $asset->id :'' ]) }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('button.add_maintenance') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        btnToggleCompleted: {
            text: '{{ request()->input('completed', 'false') === 'true' ? trans('admin/maintenances/general.show_active') : trans('admin/maintenances/general.show_completed') }}',
            icon: 'fa-regular fa-square-check',
            event() {
                var isShowingCompleted = '{{ request()->input('completed', 'false') }}' === 'true';
                window.location.href = '{{ route('maintenances.index') }}?completed=' + (isShowingCompleted ? 'false' : 'true');
            },
            attributes: {
                class: '{{ request()->input('completed', 'false') === 'true' ? 'btn-selected' : '' }}',
                title: '{{ request()->input('completed', 'false') === 'true' ? trans('admin/maintenances/general.show_active') : trans('admin/maintenances/general.show_completed') }}',
            },
        },

        btnDue: {
            text: '{{ trans('admin/maintenances/general.due') }}',
            icon: 'fa-regular fa-clock',
            event() {
                var isActive = '{{ request()->input('upcoming_status') }}' === 'due';
                window.location.href = '{{ route('maintenances.index') }}' + (isActive ? '' : '?upcoming_status=due');
            },
            attributes: {
                class: '{{ request()->input('upcoming_status') === 'due' ? 'btn-selected' : '' }}',
                title: '{{ trans('admin/maintenances/general.due') }}',
            },
        },

        btnOverdue: {
            text: '{{ trans('admin/maintenances/general.overdue') }}',
            icon: 'fa-solid fa-triangle-exclamation',
            event() {
                var isActive = '{{ request()->input('upcoming_status') }}' === 'overdue';
                window.location.href = '{{ route('maintenances.index') }}' + (isActive ? '' : '?upcoming_status=overdue');
            },
            attributes: {
                class: '{{ request()->input('upcoming_status') === 'overdue' ? 'btn-selected' : '' }}',
                title: '{{ trans('admin/maintenances/general.overdue') }}',
            },
        },
    });
    @endcan

    @can('create', \App\Models\Category::class)
    // Custom Field table buttons
    window.categoryButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('categories.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\PredefinedKit::class)
    // Custom Field table buttons
    window.kitButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('kits.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\AssetModel::class)
    // Custom Field table buttons
    window.modelButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('models.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        btnShowDeleted: {
            text: '{{ (request()->input('status') == "deleted") ? trans('general.list_all') : trans('general.deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "deleted") ? route('models.index') : route('models.index', ['status' => 'deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "deleted") ? ' btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "deleted") ? trans('general.list_all') : trans('general.deleted') }}',

            }
        },
    });
    @endcan
        window.labelButtons = () => ({
        btnAdd: {
            icon: 'fa fa-plus',
            text: 'Create New Label',
            title: 'Create New Label',
            event() {
                $('#new-label-modal').modal('show');
            },
            attributes: {
                class: 'btn-info',
                title: 'Create New Label'
            }
        },
        btnImport: {
            icon: 'fa-solid fa-file-import',
            text: 'Import Label',
            title: 'Import Label',
            event() {
                $('#import-label-modal').modal('show');
            },
            attributes: {
                class: 'btn-info',
                title: 'Import Label'
            }
        }
    });

    @can('create', \App\Models\Statuslabel::class)
    // Status label table buttons
    window.statuslabelButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('statuslabels.create') }}';
            },
            attributes: {
                class: 'btn-info',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan


    // License table buttons
    window.licenseButtons = () => ({
        @can('create', \App\Models\License::class)
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('licenses.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            },
        },
        @endcan

        btnExport: {
            text: '{{ trans('general.export_all_to_csv') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('licenses.export', ['category_id' => (isset($category)) ? $category->id :'' ]) }}';
            },
            attributes: {
                title: '{{ trans('general.export_all_to_csv') }}',
            }
        },

        btnShowExpiring: {
            text: '{{ (request()->input('status') == "expiring") ? trans('general.list_all') : trans('general.show_expiring') }}',
            icon: 'fas fa-clock',
            event () {
                window.location.href = '{{ (request()->input('status') == "expiring") ? route('licenses.index') : route('licenses.index', ['status' => 'expiring']) }}';
            },
            attributes: {
                class: "{{ (request()->input('status') == "expiring") ? ' btn-warning' : '' }}",
                title: '{{ (request()->input('status') == "expiring") ? trans('general.list_all') : trans('general.show_expiring') }}',

            }
        },

        btnShowInactive: {
            text: '{{ (request()->input('status') == "inactive") ? trans('general.list_all') : trans('general.show_inactive') }}',
            icon: 'fas fa-history',
            event () {
                window.location.href = '{{ (request()->input('status') == "inactive") ? route('licenses.index') : route('licenses.index', ['status' => 'inactive']) }}';
            },
            attributes: {
                class: "{{ (request()->input('status') == "inactive") ? ' btn-warning' : '' }}",
                title: '{{ (request()->input('status') == "inactive") ? trans('general.list_all') : trans('general.show_inactive') }}',

            }
        },
    });





    function dateRowCheckStyle(value) {
        if ((value.days_to_next_audit) && (value.days_to_next_audit < {{ $snipeSettings->audit_warning_days ?: 0 }})) {
            return { classes : "danger" }
        }
        return {};
    }


    // Use document.getElementById and DOM/jQuery element constructors throughout this block
    // rather than jQuery selector parsing or HTML string concatenation. The countId value
    // originates from a data attribute that may contain stored user input (e.g. a manufacturer
    // or supplier name), and jQuery decodes HTML entities in data attributes before returning
    // them to JS. Concatenating a decoded attacker-controlled value into an HTML string passed
    // to .after() would allow stored XSS; setting it via DOM APIs treats it as plain text.
    function updateSelectedCount(table) {
        var countId = $(table).data('selected-count-id');
        if (!countId) return;
        var rawCountId = countId.charAt(0) === '#' ? countId.substring(1) : countId;
        if (!rawCountId) return;
        var el = document.getElementById(rawCountId);
        if (!el) return;
        var count = $(table).bootstrapTable('getSelections').length;
        $(el).find('.badge').text(count);
        if (count > 0) {
            $(el).show();
        } else {
            $(el).hide();
        }
    }

    $('.snipe-table').on('post-body.bs.table', function () {
        var countId = $(this).data('selected-count-id');
        if (!countId) return;
        var rawCountId = countId.charAt(0) === '#' ? countId.substring(1) : countId;
        if (!rawCountId) return;
        var $paginationDetail = $(this).closest('.bootstrap-table')
            .find('.fixed-table-pagination').first()
            .find('.pagination-detail');
        if ($paginationDetail.length && !document.getElementById(rawCountId)) {
            var $selectedCount = $('<span/>', {
                id: rawCountId,
                style: 'display:none; float:left; margin-top:10px; margin-bottom:10px; margin-left:10px; line-height:34px;'
            });
            $selectedCount.append(document.createTextNode('— '));
            $selectedCount.append($('<span/>', { 'class': 'badge', text: '0' }));
            $selectedCount.append(document.createTextNode(' {{ trans('general.selected') }}'));
            $paginationDetail.after($selectedCount);
        }
        updateSelectedCount(this);
    });

    // Dynamic bulk actions: when a table's bulk-actions dropdown was rendered with
    // data-dynamic-actions, its options are populated here from the UNION of every
    // selected row's available_actions.bulk_selectable. An action shows if AT LEAST
    // ONE currently-selected row supports it. Rows that don't support the picked
    // action are auto-unchecked when the operator selects the action from the
    // dropdown, so the effective selection lines up with what "Go" will actually
    // submit. Tables that rendered a static option list have no data-dynamic-actions
    // attribute and are unaffected.
    function refreshDynamicBulkActions(table) {
        var $table = $(table);
        var formId = $table.data('bulk-form-id');
        if (!formId) return;

        var $select = $(formId).find('select[data-dynamic-actions]');
        if ($select.length === 0) return;

        var actions;
        try {
            actions = JSON.parse($select.attr('data-dynamic-actions')) || {};
        } catch (e) {
            return;
        }

        var selections = $table.bootstrapTable('getSelections');
        var $button = $($table.data('bulk-button-id'));
        var currentValue = $select.val();
        var placeholder = $select.attr('data-placeholder') || '';

        var eligible = {};
        for (var i = 0; i < selections.length; i++) {
            var supported = (selections[i].available_actions && selections[i].available_actions.bulk_selectable) || {};
            for (var k in supported) {
                if (supported[k] === true) eligible[k] = true;
            }
        }

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        $select.empty();

        if (selections.length === 0) {
            $select.append($('<option/>', { value: '', text: placeholder }));
            $button.attr('disabled', 'disabled');
        }
        else if (Object.keys(eligible).length === 0) {
            $select.append($('<option/>', { value: '', text: '{{ trans('general.bulk_actions_none_available') }}' }));
            $button.attr('disabled', 'disabled');
        } else {
            var appendedAny = false;
            for (var actionKey in actions) {
                if (eligible[actionKey] && actions[actionKey] && actions[actionKey].label) {
                    $select.append($('<option/>', { value: actionKey, text: actions[actionKey].label }));
                    appendedAny = true;
                }
            }
            if (appendedAny) {
                if (currentValue && eligible[currentValue]) {
                    $select.val(currentValue);
                }
                $button.removeAttr('disabled');
            } else {
                $select.append($('<option/>', { value: '', text: '{{ trans('general.bulk_actions_none_available') }}' }));
                $button.attr('disabled', 'disabled');
            }
        }

        $select.select2({ minimumResultsForSearch: Infinity });

        // Auto-uncheck rows that don't support the picked action. Bound
        // here rather than via document delegation because select2's
        // destroy-then-init cycle inside this function was intermittently
        // swallowing delegated change events. Rebinding on every
        // refresh keeps this deterministic. .off('.autoUncheck')
        // isolates just this namespace so we don't clobber select2's
        // own handlers.
        $select.off('change.autoUncheck select2:select.autoUncheck').on('change.autoUncheck select2:select.autoUncheck', function () {
            var action = $(this).val();
            if (action) {
                autoUncheckIncompatibleRowsFor($table, $(this));
            }
            else {
                clearActionDependentRowStyling($table);
            }
        });

        // Re-apply the action-dependent visual state now, in case the
        // refresh was triggered while an action was still selected in
        // the dropdown (e.g. operator checked a new row while "delete"
        // was picked). Keeps styling consistent with the just-adjusted
        // selection.
        applyActionDependentRowStyling($table, $select.val() || null);
    }

    function autoUncheckIncompatibleRowsFor($table, $select) {
        var action = $select.val();
        if (!action) return;

        // Use uncheck(index) instead of uncheckBy(field, values). bs-table's
        // uncheckBy at bootstrap-table.js line 10791 filters on ':enabled',
        // which means once applyActionDependentRowStyling disables the
        // checkbox on an incompatible row (or a prior refresh cycle did),
        // uncheckBy silently skips it. uncheck(index) uses _toggleCheck
        // which sets checked=false via prop() directly, without the
        // :enabled filter, so incompatible rows still uncheck even if
        // their checkbox is already disabled.
        var idsToUncheck = {};
        var selections = $table.bootstrapTable('getSelections');
        for (var i = 0; i < selections.length; i++) {
            var supported = (selections[i].available_actions && selections[i].available_actions.bulk_selectable) || {};
            if (supported[action] !== true) {
                idsToUncheck[selections[i].id] = true;
            }
        }

        var data = $table.bootstrapTable('getData');
        for (var j = 0; j < data.length; j++) {
            if (idsToUncheck[data[j].id]) {
                $table.bootstrapTable('uncheck', j);
            }
        }

        applyActionDependentRowStyling($table, action);
    }

    // Toggle a visual disabled state on rows that don't support the
    // currently-picked action. Composes on top of checkboxEnabledFormatter
    // (which handles the "no actions at all" case at render time). This
    // layer reacts live to dropdown changes and check events so operators
    // can tell at a glance which rows will land in the batch.
    function applyActionDependentRowStyling($table, action) {
        var data = $table.bootstrapTable('getData');

        $table.find('tbody > tr').each(function () {
            var $tr = $(this);
            var index = $tr.data('index');
            var rowData = (typeof index !== 'undefined') ? data[index] : null;
            if (!rowData || !rowData.available_actions || !rowData.available_actions.bulk_selectable) return;

            var supported = rowData.available_actions.bulk_selectable;
            var isIncompatible = !!(action && supported[action] !== true);

            $tr.toggleClass('bulk-action-incompatible', isIncompatible);

            // Only toggle the disabled attribute we set — leave whatever
            // checkboxEnabledFormatter already decided at render time
            // alone by only ADDING disabled when we mark incompatible
            // and REMOVING it only when the row would otherwise be
            // renderable (checkboxEnabledFormatter marks .disabled on
            // the CELL wrapper, so we test that before re-enabling).
            var $checkbox = $tr.find('input[type="checkbox"][name="btSelectItem"]');
            if (!$checkbox.length) return;
            if (isIncompatible) {
                $checkbox.prop('disabled', true);
            }
            else {
                var $cell = $checkbox.closest('.bs-checkbox');
                var cellSaysDisabled = $cell.length && $cell.hasClass('disabled');
                if (!cellSaysDisabled) {
                    $checkbox.prop('disabled', false);
                }
            }
        });
    }

    // Clear the class + attribute back to base state when there's no
    // action picked (dropdown cleared or reset).
    function clearActionDependentRowStyling($table) {
        applyActionDependentRowStyling($table, null);
    }

    // Re-run the auto-uncheck against whatever action is currently in the
    // dropdown for this table's bulk-actions form. Called after any row-
    // check event so a re-check (or check-all) of an incompatible row
    // doesn't sneak past the guard that fired on dropdown pick.
    function reapplyAutoUncheckForTable(table) {
        var $table = $(table);
        var formId = $table.data('bulk-form-id');
        if (!formId) return;

        var $select = $(formId).find('select[data-dynamic-actions]');
        if ($select.length === 0) return;
        if (!$select.val()) return;

        autoUncheckIncompatibleRowsFor($table, $select);
    }

    // bs-table wipes DOM classes when it re-renders rows (pagination,
    // sort, filter, search). Re-apply the action-dependent styling
    // after every body render so the visual state survives those
    // interactions.
    $('.snipe-table').on('post-body.bs.table', function () {
        var $table = $(this);
        var formId = $table.data('bulk-form-id');
        if (!formId) return;

        var $select = $(formId).find('select[data-dynamic-actions]');
        if ($select.length === 0) return;

        applyActionDependentRowStyling($table, $select.val() || null);
    });

    // Safety-net delegated listener for the dropdown change event. The
    // direct binding inside refreshDynamicBulkActions should be doing
    // the work, but if select2's destroy/init cycle ever clobbers it,
    // this catches the event through DOM delegation. Both routes call
    // the same function so double-firing is idempotent.
    $(document).on('change', 'select[data-dynamic-actions]', function () {
        var $select = $(this);
        var formEl = $select.closest('form')[0];
        if (!formEl) return;

        var $table = $('.snipe-table').filter(function () {
            var formId = $(this).data('bulk-form-id');
            return formId && $(formId)[0] === formEl;
        });
        if ($table.length === 0) return;

        var action = $select.val();
        if (action) {
            autoUncheckIncompatibleRowsFor($table, $select);
        }
        else {
            clearActionDependentRowStyling($table);
        }
    });

    // These methods dynamically add/remove hidden input values in the bulk actions form
    $('.snipe-table').on('check.bs.table .btSelectItem', function (row, $element) {
        var buttonName =  $(this).data('bulk-button-id');
        var tableId =  $(this).data('id-table');

        $(buttonName).removeAttr('disabled');
        $(buttonName).after($('<input/>', {
            id: tableId + '_checkbox_' + $element.id,
            type: 'hidden',
            name: 'ids[]',
            value: $element.id
        }));
        updateSelectedCount(this);
        refreshDynamicBulkActions(this);
        reapplyAutoUncheckForTable(this);
    });

    $('.snipe-table').on('check-all.bs.table', function (event, rowsAfter) {

        var buttonName =  $(this).data('bulk-button-id');
        var tableId =  $(this).data('id-table');

        for (var i in rowsAfter) {
            // Do not select things that were already selected
            if (!document.getElementById(tableId + '_checkbox_' + rowsAfter[i].id)) {
                $(buttonName).after($('<input/>', {
                    id: tableId + '_checkbox_' + rowsAfter[i].id,
                    type: 'hidden',
                    name: 'ids[]',
                    value: rowsAfter[i].id
                }));
            }
        }

        if ($(this).bootstrapTable('getSelections').length > 0) {
            $(buttonName).removeAttr('disabled');
        }
        updateSelectedCount(this);
        refreshDynamicBulkActions(this);
        reapplyAutoUncheckForTable(this);
    });


    $('.snipe-table').on('uncheck.bs.table .btSelectItem', function (row, $element) {
        var tableId =  $(this).data('id-table');
        $( "#" + tableId + "_checkbox_" + $element.id).remove();
        updateSelectedCount(this);
    });


    // Handle whether the edit button should be disabled
    $('.snipe-table').on('uncheck.bs.table', function () {
        var buttonName =  $(this).data('bulk-button-id');

        if ($(this).bootstrapTable('getSelections').length == 0) {

            $(buttonName).attr('disabled', 'disabled');
        }
        refreshDynamicBulkActions(this);
    });

    $('.snipe-table').on('uncheck-all.bs.table', function (event, rowsAfter, rowsBefore) {

        var buttonName =  $(this).data('bulk-button-id');
        $(buttonName).attr('disabled', 'disabled');
        var tableId =  $(this).data('id-table');

        for (var i in rowsBefore) {
            $('#' + tableId + "_checkbox_" + rowsBefore[i].id).remove();
        }
        updateSelectedCount(this);
        refreshDynamicBulkActions(this);
    });

    // Initialize sort-order for bulk actions (label-generation) for snipe-tables
    $('.snipe-table').each(function (i, table) {
        table_cookie_segment = $(table).data('cookie-id-table');
        sort = '';
        order = '';
        cookies = document.cookie.split(";");
        for(i in cookies) {
            cookiedef = cookies[i].split("=", 2);
            cookiedef[0] = cookiedef[0].trim();
            if (cookiedef[0] == table_cookie_segment + ".bs.table.sortOrder") {
                order = cookiedef[1];
            }
            if (cookiedef[0] == table_cookie_segment + ".bs.table.sortName") {
                sort = cookiedef[1];
            }
        }
        if (sort && order) {
            domnode = $($(this).data('bulk-form-id')).get(0);
            if ( domnode && domnode.elements && domnode.elements.sort ) {
                domnode.elements.sort.value = sort;
                domnode.elements.order.value = order;
            }
        }
    });

    // If sort order changes, update the sort-order for bulk-actions (for label-generation)
    $('.snipe-table').on('sort.bs.table', function (event, name, order) {
       domnode = $($(this).data('bulk-form-id')).get(0);
       // make safe in case there isn't a bulk-form-id, or it's not found, or has no 'sort' element
       if ( domnode && domnode.elements && domnode.elements.sort ) {
           domnode.elements.sort.value = name;
           domnode.elements.order.value = order;
       }
    });


    // This specifies the footer columns that should have special styles associated
    // (usually numbers)
    window.footerStyle = column => ({
        remaining: {
            classes: 'text-padding-number-footer-cell'
        },
        qty: {
            classes: 'text-padding-number-footer-cell',
        },
        purchase_cost: {
            classes: 'text-padding-number-footer-cell'
        },
        checkouts_count: {
            classes: 'text-padding-number-footer-cell'
        },
        assets_count: {
            classes: 'text-padding-number-footer-cell'
        },
        seats: {
            classes: 'text-padding-number-footer-cell'
        },
        free_seats_count: {
            classes: 'text-padding-number-footer-cell'
        },
    }[column.field]);




    // This only works for model index pages because it uses the row's model ID
    function genericRowLinkFormatter(destination) {
        // camelCase entries in the formatters array whose URL segment
        // is actually hyphenated need a rewrite here; the formatter
        // names stay as valid JS identifiers, the URL uses the real
        // route segment. Matches the groups -> admin/groups precedent
        // in genericActionsFormatter.
        if (destination == 'maintenanceTypes') {
            destination = 'maintenance-types';
        }
        return function (value,row) {

            if ((row) && (row.tag_color) && (row.tag_color!='') && (row.tag_color!=undefined)) {
                var tag_icon = '<i class="fa-solid fa-square" style="color: ' + row.tag_color + ';" aria-hidden="true"></i> ';
            } else {
                var tag_icon = '';
            }

            if (value) {
                return '<span style="white-space:nowrap;">' + tag_icon + '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '">' + value + '</a></span>';
            }
        };
    }



    // This is a special formatter that will indicate whether a user is an admin or superadmin
    function usernameRoleLinkFormatter(value, row) {

            if ((value) && (row)) {

                if (row.role === 'superadmin') {
                    return '<span style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.superuser_tooltip') }}"><x-icon type="superadmin" title="{{ trans('general.superuser') }}"  class="text-danger" /> <a href="{{ config('app.url') }}/users/' + row.id + '">' + value + '</a></span>';
                } else if (row.role === 'admin') {
                    return '<span style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.admin_tooltip') }}"><x-icon type="superadmin" title="{{ trans('general.admin_user') }}" class="text-warning" /> <a href="{{ config('app.url') }}/users/' + row.id + '">' + value + '</a></span>';
                }

                // Regular user
                return '<a href="{{ config('app.url') }}/users/' + row.id + '">' + value + '</a>';
            }

    }

    function progressBarFormatter(value) {
        var bar_color = 'danger';

        if (value <= 25) {
            bar_color = 'danger';
        }
        else if (value <= 75) {
            bar_color = 'warning';
        }
        else if (value <= 100) {
            bar_color = 'success';
        }
        return '<div class="progress progress-sm" data-tooltip="true" title="' + value + '%"><div class="progress-bar progress-bar-' + bar_color + '" role="progressbar" aria-valuenow="' + value + '" aria-valuemin="0" aria-valuemax="100" style="width: ' + value + '%; min-width: 0em;"></div></div>';
    }

    // Use this when we're introspecting into a column object and need to link
    function genericColumnObjLinkFormatter(destination) {
        // camelCase entries in the formatters array whose URL segment
        // is actually hyphenated need a rewrite here. See sibling
        // formatters for the groups -> admin/groups precedent.
        if (destination == 'maintenanceTypes') {
            destination = 'maintenance-types';
        }
        return function (value,row) {
            if ((value) && (value.status_meta)) {

                var text_color;
                var icon_style;
                var text_help;
                var status_meta = {
                  'deployed': '{{ strtolower(trans('general.deployed')) }}',
                  'deployable': '{{ strtolower(trans('admin/hardware/general.deployable')) }}',
                  'archived': '{{ strtolower(trans('general.archived')) }}',
                  'undeployable': '{{ strtolower(trans('general.undeployable')) }}',
                  'pending': '{{ strtolower(trans('general.pending')) }}'
                }

                switch (value.status_meta) {
                    case 'deployed':
                        text_color = 'blue';
                        icon_style = 'fa-circle';
                        text_help = '<label class="label label-default">{{ trans('general.deployed') }}</label>';
                    break;
                    case 'deployable':
                        text_color = 'green';
                        icon_style = 'fa-circle';
                        text_help = '';
                    break;
                    case 'pending':
                        text_color = 'orange';
                        icon_style = 'fa-circle';
                        text_help = '';
                        break;
                    default:
                        text_color = 'red';
                        icon_style = 'fa-times';
                        text_help = '';
                }

                return '<nobr><a href="{{ config('app.url') }}/' + destination + '/' + value.id + '" data-tooltip="true" title="'+ status_meta[value.status_meta] + '"> <i class="fa ' + icon_style + ' text-' + text_color + '"></i> ' + value.name + ' ' + text_help + ' </a> </nobr>';
            } else if ((value) && (value.name)) {

                // Add some overrides for any funny urls we have
                var dest = destination;
                var tag_color;
                var polymorphicItemFormatterDest = '';



                if (destination == 'fieldsets') {
                    var polymorphicItemFormatterDest = 'fields/';
                }

                // Handle the preceding icon if a tag_color is given in the API response
                if ((value.tag_color) && (value.tag_color!='')) {
                    var tag_icon = '<i class="fa-solid fa-square" style="color: ' + value.tag_color + ';" aria-hidden="true"></i>';
                } else {
                    var tag_icon = '';
                }

                return '<nobr>'+ tag_icon + ' <a href="{{ config('app.url') }}/' + polymorphicItemFormatterDest + dest + '/' + value.id + '">' + value.name + '</a></span>';
            }
        };
    }


    function colorTagFormatter(value, row) {
        if (value) {
            return '<i class="fa-solid fa-square" style="color: ' + value + ';" aria-hidden="true"></i> ' + value;
        }
    }




    function licenseKeyFormatter(value, row) {
        if (value) {
            return '<code class="single-line"><span class="js-copy-link" data-clipboard-target=".js-copy-key-' + row.id + '" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}"><span class="js-copy-key-' + row.id + '">' + value + '</span></span></code>';
        }
    }



    function hardwareAuditFormatter(value, row) {
        return '<a href="{{ config('app.url') }}/hardware/' + row.id + '/audit" class="actions btn btn-sm btn-primary hidden-print" data-tooltip="true" title="{{ trans('general.audit') }}"><x-icon type="audit" /><span class="sr-only">{{ trans('general.audit') }}</span></a>&nbsp;';
    }




    // Make the edit/delete buttons
    function genericActionsFormatter(owner_name, element_name) {
        if (!element_name) {
            element_name = '';
        }


        return function (value,row) {
            var actions = '<nobr>';

            // Add some overrides for any funny urls we have
            var dest = owner_name;

            if (dest =='groups') {
                var dest = 'admin/groups';
            }

            if (dest == 'maintenanceTypes') {
                dest = 'maintenance-types';
            }


            if(element_name != '') {
                dest = dest + '/' + row.owner_id + '/' + element_name;
            }

            if ((row.available_actions) && (row.available_actions.create_asset === true)) {
                actions += '<a href="{{ config('app.url') }}/hardware/create?model_id=' + row.id + '" class="actions btn btn-sm btn-info hidden-print" data-tooltip="true" title="{{ trans('general.new_asset') }}"><x-icon type="plus" class="fa-fw" /><span class="sr-only">{{ trans('general.new_asset') }}</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.clone === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/clone" class="actions btn btn-sm btn-info hidden-print" data-tooltip="true" title="{{ trans('general.clone_item') }}"><x-icon type="clone" class="fa-fw" /><span class="sr-only">{{ trans('general.clone_item') }}</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.audit === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/audit" class="actions btn btn-sm btn-primary hidden-print" data-tooltip="true" title="{{ trans('general.audit') }}"><x-icon type="audit" class="fa-fw" /><span class="sr-only">{{ trans('general.audit') }}</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.update === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/edit" class="actions btn btn-sm btn-warning hidden-print" data-tooltip="true" title="{{ trans('general.update') }}"><x-icon type="edit" class="fa-fw" /><span class="sr-only">{{ trans('general.update') }}</span></a>&nbsp;';
            } else {
                if ((row.available_actions) && (row.available_actions.update != true)) {
                    actions += '<span data-tooltip="true" title="{{ trans('general.cannot_be_edited') }}"><a class="btn btn-warning btn-sm disabled" onClick="return false;"><x-icon type="edit" class="fa-fw" /></a></span>&nbsp;';
                }
            }

            // Plus-minus button opens the shared adjust-quantity modal.
            // Only rendered when the row's transformer set
            // available_actions.adjust_quantity (accessories, consumables,
            // components). The click handler in snipeit.js reads the
            // data-* attrs and shows blade/modals/adjust-quantity.
            if ((row.available_actions) && (row.available_actions.adjust_quantity === true)) {
                actions += window.renderAdjustQuantityButton({
                    dest: dest,
                    id: row.id,
                    name: row.name || '',
                    available: row.remaining,
                }) + '&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.delete === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/edit" class="actions btn btn-sm btn-warning hidden-print" data-tooltip="true" title="{{ trans('general.update') }}"><x-icon type="edit" class="fa-fw" /><span class="sr-only">{{ trans('general.update') }}</span></a>&nbsp;';
                // use the asset tag if no name is provided

                if (row.name) {
                    var name_for_box = row.name
                } else if (row.asset_tag) {
                    var name_for_box = row.asset_tag
                }


                
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '" '
                    + ' class="actions btn btn-danger btn-sm delete-asset hidden-print" data-tooltip="true"  '
                    + ' data-toggle="modal" data-icon="fa-trash"'
                    + ' data-content="{{ trans('general.sure_to_delete') }}: ' + name_for_box + '?" '
                    + ' data-title="{{  trans('general.delete') }}" onClick="return false;">'
                    + '<x-icon type="delete" class="fa-fw" /><span class="sr-only">{{ trans('general.delete') }}</span></a>&nbsp;';
            } else {
                // Do not show the delete button on things that are already deleted
                if ((row.available_actions) && (row.available_actions.restore != true)) {
                    actions += '<span data-tooltip="true" title="{{ trans('general.cannot_be_deleted') }}"><a class="btn btn-danger btn-sm delete-asset disabled hidden-print" onClick="return false;"><x-icon type="delete" class="fa-fw" /><span class="sr-only">{{ trans('general.cannot_be_deleted') }}</span></a></span>&nbsp;';
                }

            }


            if ((row.available_actions) && (row.available_actions.restore === true)) {
                actions += '<form style="display: inline;" method="POST" action="{{ config('app.url') }}/' + dest + '/' + row.id + '/restore"> ';
                actions += '@csrf';
                actions += '<button class="btn btn-sm btn-warning" data-tooltip="true" title="{{ trans('general.restore') }}"><x-icon type="restore" class="fa-fw" /><span class="sr-only">{{ trans('general.restore') }}</span></button>&nbsp;';
            }

            actions +='</nobr>';
            return actions;

        };
    }


    // This handles the icons and display of polymorphic entries
    function polymorphicItemFormatter(value) {

        var item_destination = '';
        var item_icon;

        if ((value) && (value.type)) {

            if (value.type === 'asset') {
                item_destination = 'hardware';
                item_icon = 'fas fa-barcode';
            }
            else if (value.type === 'accessory') {
                item_destination = 'accessories';
                item_icon = 'far fa-keyboard';
            }
            else if (value.type === 'component') {
                item_destination = 'components';
                item_icon = 'far fa-hdd';
            }
            else if (value.type === 'consumable') {
                item_destination = 'consumables';
                item_icon = 'fas fa-tint';
            }
            else if (value.type === 'license') {
                item_destination = 'licenses';
                item_icon = 'far fa-save';
            }
            else if (value.type === 'user') {
                item_destination = 'users';
                item_icon = 'fas fa-user';
            }
            else if (value.type === 'location') {
                item_destination = 'locations'
                item_icon = 'fas fa-map-marker-alt';
            }
            else if (value.type === 'maintenance') {
                item_destination = 'maintenances'
                item_icon = 'fa-solid fa-screwdriver-wrench';
            }
            else if (value.type === 'model') {
                item_destination = 'models'
                item_icon = 'fa-solid fa-boxes-stacked';
            }
            else if (value.type === 'supplier') {
                item_destination = 'suppliers';
                item_icon = 'fa-solid fa-store';
            }
            else if (value.type === 'department') {
                item_destination = 'departments';
                item_icon = 'fa-solid fa-building-user';
            }
            else if (value.type === 'company') {
                item_destination = 'companies';
                item_icon = 'fa-regular fa-building';
            }

            // display the username if it's checked out to a user, but don't do it if the username's there already
            if (value.username && !value.name.match('\\(') && !value.name.match('\\)')) {
                value.name = value.name + ' (' + value.username + ')';
            }

            // Show as strikethrough if it's been deleted
            if (value.deleted_at && value.deleted_at != '') {
                return '<nobr><span class="text-muted" data-tooltip="true" title="{{ trans('general.deleted') }} ' + value.type + '"><del><i class="' + item_icon + ' fa-fw"></i> ' + value.name + '</del></span></nobr>';
            }

            return '<nobr><a href="{{ config('app.url') }}/' + item_destination +'/' + value.id + '" data-tooltip="true" title="' + value.type + '"><i class="' + item_icon + ' fa-fw"></i> ' + value.name + '</a></nobr>';

        } else {
            return '';
        }


    }

    // This just prints out the item type in the activity report
    function itemTypeFormatter(value, row) {

        if ((row) && (row.item) && (row.item.type)) {
            return row.item.type;
        }
    }


    // Convert line breaks to <br>
    function notesFormatter(value) {
        if (value) {
            return value.replace(/(?:\r\n|\r|\n)/g, '<br />');
        }
    }

    // Compact display for the `orders` array on Accessory / Consumable /
    // Component rows. These items can be tied to many orders as quantity
    // is re-ordered over time, so a raw list would overwhelm the table
    // on high-volume rows. Renders based on count:
    //   0    empty cell
    //   1    the single order number
    //   2-3  comma-separated list
    //   4+   first order number + "(+N more)" with the full list as tooltip
    // Every rendered order number is passed through jQuery text() to
    // defuse any HTML in the order_number string.
    function ordersSummaryFormatter(value) {
        if (!Array.isArray(value) || value.length === 0) {
            return '';
        }

        var esc = function (v) {
            return $('<div/>').text(v == null ? '' : v).html();
        };

        if (value.length === 1) {
            return esc(value[0]);
        }

        if (value.length <= 3) {
            return value.map(esc).join(', ');
        }

        var tooltip = esc(value.join(', '));
        return esc(value[0]) + ' <span class="text-muted" data-tooltip="true" title="' + tooltip + '">(+' + (value.length - 1) + ' more)</span>';
    }

    // Formatters for the /requests admin page (paired with
    // CheckoutRequestPresenter + CheckoutRequestsTransformer). Each row
    // has a polymorphic requestable (Asset / AssetModel / Accessory /
    // Consumable / Component) with a pre-resolved image, url, and name,
    // so these formatters do not need per-type branching the way the
    // old server-rendered blade did.

    function requestableImageFormatter(value, row) {
        if (!value || !row || !row.requestable) {
            return '';
        }
        // Transformer already e()'d the name; the &quot; entities
        // work fine inside an alt="..." attribute (browsers decode
        // them there). A second $('<div/>').text().html() escape
        // would double-escape.
        var altName = row.requestable.name || '';
        return '<a href="' + value + '" data-toggle="lightbox" data-type="image"><img src="' + value + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt="' + altName + '"></a>';
    }

    function requestableNameFormatter(value, row) {
        if (!row || !row.requestable) {
            return '';
        }
        // Transformer already ran e() on the name, so `value` is
        // already HTML-safe. A second $('<div/>').text().html()
        // escape here would double-escape (e.g. Macbook Pro 13"
        // -> &quot; -> &amp;quot; rendered as &quot;).
        var name = row.requestable.name || '';
        // Type icon prefix (asset / accessory / consumable / etc).
        // Sourced from the transformer via IconHelper so the icon
        // stays consistent with the rest of the app's per-type
        // iconography. Sits INSIDE the anchor to match how the
        // rest of Snipe-IT renders icon+text links.
        var icon = row.requestable.icon
            ? '<i class="' + row.requestable.icon + ' fa-fw" aria-hidden="true" title="' + (row.requestable.type || '') + '"></i> '
            : '';
        if (row.requestable.url) {
            return '<a href="' + row.requestable.url + '">' + icon + name + '</a>';
        }
        return icon + name;
    }

    function requesterFormatter(value, row) {
        // value = row.user (object with id/name/url/deleted) or null
        if (!value) {
            return '{{ trans('admin/reports/general.deleted_user') }}';
        }
        // Same as requestableNameFormatter above - value.name is
        // already e()'d by the transformer.
        var name = value.name || '';
        if (value.deleted || !value.url) {
            return '{{ trans('admin/reports/general.deleted_user') }}';
        }
        return '<a href="' + value.url + '">' + name + '</a>';
    }

    // Renders the per-row action cluster: admin-cancel form + a
    // checkout / checkin button per available_actions. All destination
    // ids come from the transformer output, so no per-type URL
    // branching lives in the JS.
    function checkoutRequestActionsFormatter(value, row) {
        if (!row) {
            return '';
        }
        var actions = '';
        var permissions = row.available_actions || {};

        if (permissions.cancel && row.requestable && row.user) {
            // Route path is /account/request/{itemType} (NOT
            // /account/request-item, which is the route NAME with
            // its unusual slash-separator - the path segment is
            // just "request"). Slug map covers every polymorphic
            // requestable type since the set is fixed.
            var typeSlugs = {
                Asset: 'asset',
                AssetModel: 'asset_model',
                Accessory: 'accessory',
                Consumable: 'consumable',
                Component: 'component',
                License: 'license',
            };
            var typeSlug = typeSlugs[row.requestable.type];
            if (!typeSlug) {
                return actions; // Unknown type, don't render a broken cancel button.
            }
            // Same shape the old blade posted to. Includes the fifth
            // "requestingUser" segment so an admin cancels the
            // specific user's request rather than their own.
            var cancelUrl = '{{ config('app.url') }}/account/request/' +
                typeSlug + '/' + row.requestable.id + '/1/' + row.user.id;
            // Pipe through the shared dataConfirmModal so this
            // behaves like every other destructive action in the
            // app (delete-asset class + data-href + data-content).
            // The modal's #deleteForm posts with a DELETE method
            // spoof; the route accepts both POST and DELETE for
            // this reason.
            var requesterName = (row.user && row.user.name) ? row.user.name : '';
            var itemName = (row.requestable && row.requestable.name) ? row.requestable.name : '';
            var confirmMsg = '{{ trans('admin/hardware/message.requests.confirm_cancel_by_admin') }}'
                .replace(':user', requesterName)
                .replace(':item', itemName);
            actions += ' <a href="' + cancelUrl + '"' +
                ' class="actions btn btn-danger btn-sm delete-asset"' +
                ' data-toggle="modal" data-target="#dataConfirmModal"' +
                ' data-content="' + confirmMsg + '"' +
                ' data-title="{{ trans('general.cancel_request') }}"' +
                ' data-icon="fa-trash"' +
                ' data-tooltip="true" title="{{ trans('general.cancel_request') }}"' +
                ' onClick="return false;">' +
                '<x-icon type="delete" class="fa-fw" /><span class="sr-only">{{ trans('general.cancel_request') }}</span>' +
                '</a>';
        }

        if (permissions.checkout && row.requestable) {
            // Route per requestable type. The old code hardcoded
            // /hardware/{id}/checkout which 404'd (or worse, opened
            // the wrong asset) for non-Asset rows. Component
            // additionally carries a ?requesting_user query so the
            // components-checkout screen can pre-scope its target-
            // asset picker to that user's assigned assets (see
            // ComponentCheckoutController::create).
            // Every single-target checkout URL carries ?request_id so
            // the destination controller can hydrate the full
            // request (requester, reservation window, notes) and
            // pre-populate the form - saves the admin from re-
            // selecting the user, the expected checkin date, etc.
            var checkoutBase;
            var requestQs = row.id ? ('?request_id=' + encodeURIComponent(row.id)) : '';
            switch (row.requestable.type) {
                case 'Component':
                    checkoutBase = '{{ config('app.url') }}/components/' + row.requestable.id + '/checkout' + requestQs;
                    break;
                case 'Accessory':
                    checkoutBase = '{{ config('app.url') }}/accessories/' + row.requestable.id + '/checkout' + requestQs;
                    break;
                case 'Consumable':
                    checkoutBase = '{{ config('app.url') }}/consumables/' + row.requestable.id + '/checkout' + requestQs;
                    break;
                case 'License':
                    checkoutBase = '{{ config('app.url') }}/licenses/' + row.requestable.id + '/checkout' + requestQs;
                    break;
                case 'AssetModel':
                    // AssetModel requests fulfill by picking a
                    // specific available asset OF the model. The
                    // bulk-fulfill screen surfaces the picker even
                    // for a single-row case, so route there instead
                    // of a per-item checkout page (models have no
                    // 1:1 checkout screen).
                    checkoutBase = '{{ config('app.url') }}/models/' + row.requestable.id + '/fulfill-requests';
                    break;
                default:
                    checkoutBase = '{{ config('app.url') }}/hardware/' + row.requestable.id + '/checkout' + requestQs;
            }
            // Disable the checkout button when the requested qty
            // exceeds what's actually on hand. Requesters CAN ask
            // for more than stock (that's intentional - see the
            // discussion around the replenish button), but admin
            // needs a visual "not fulfillable right now" signal
            // instead of a working checkout link that would fail on
            // the next screen. Only meaningful when the requestable
            // exposes a `remaining` count (qty-tracked types); a
            // null remaining means "not qty-tracked" and the link
            // stays enabled.
            var requestedQty = parseInt(row.quantity, 10);
            var availableQty = (row.requestable && row.requestable.remaining != null)
                ? parseInt(row.requestable.remaining, 10)
                : null;
            var shortStock = availableQty !== null && !isNaN(requestedQty) && requestedQty > availableQty;

            if (shortStock) {
                actions += ' <button type="button" class="actions btn btn-sm bg-maroon disabled" disabled data-tooltip="true"' +
                    ' title="{{ trans('admin/hardware/message.requests.insufficient_stock') }}">' +
                    '<x-icon type="checkout" class="fa-fw" /><span class="sr-only">{{ trans('general.checkout') }}</span>' +
                    '</button>';
            }
            else {
                actions += ' <a href="' + checkoutBase +
                    '" class="actions btn btn-sm bg-maroon" data-tooltip="true" title="{{ trans('general.checkout_user_tooltip') }}">' +
                    '<x-icon type="checkout" class="fa-fw" /><span class="sr-only">{{ trans('general.checkout') }}</span>' +
                    '</a>';
            }

            // Bulk-fulfill entry-point on rows where >=2 people are
            // waiting on the same item. pending_requesters is the
            // list of OTHER open requesters for this row's
            // requestable (excludes the current row), so a length
            // >=1 means 2+ total pending on this (type, id) pair.
            // Only rendered for the five bulk-eligible types -
            // Asset is 1:1 (can't be bulk-fulfilled), and the
            // shortStock branch above doesn't gate this since bulk-
            // fulfill supports partial fulfillment anyway. Every
            // row for the same requestable shows the same link
            // (they all resolve to the same bulk-fulfill screen);
            // one-click cost of the extra row is worth the "admin
            // can start bulk from any row" ergonomics.
            var pendingCount = (row.pending_requesters || []).length;
            if (pendingCount >= 1) {
                var bulkBase = null;
                switch (row.requestable.type) {
                    case 'Accessory':
                        bulkBase = '{{ config('app.url') }}/accessories/' + row.requestable.id + '/fulfill-requests';
                        break;
                    case 'Consumable':
                        bulkBase = '{{ config('app.url') }}/consumables/' + row.requestable.id + '/fulfill-requests';
                        break;
                    case 'Component':
                        bulkBase = '{{ config('app.url') }}/components/' + row.requestable.id + '/fulfill-requests';
                        break;
                    case 'License':
                        bulkBase = '{{ config('app.url') }}/licenses/' + row.requestable.id + '/fulfill-requests';
                        break;
                    case 'AssetModel':
                        bulkBase = '{{ config('app.url') }}/models/' + row.requestable.id + '/fulfill-requests';
                        break;
                }
                if (bulkBase) {
                    actions += ' <a href="' + bulkBase +
                        '" class="actions btn btn-sm btn-warning" data-tooltip="true" title="{{ trans('admin/hardware/general.fulfill_multiple') }}">' +
                        '<x-icon type="fulfill_multiple" class="fa-fw" />' +
                        '<span class="sr-only">{{ trans('admin/hardware/general.fulfill_multiple') }}</span>' +
                        '</a>';
                }
            }
        }
        else if (permissions.checkin && row.requestable) {
            actions += ' <a href="{{ config('app.url') }}/hardware/' + row.requestable.id +
                '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">' +
                '<x-icon type="checkin" class="fa-fw" /><span class="sr-only">{{ trans('general.checkin') }}</span></a>';
        }

        // Replenish button opens the shared adjust-quantity modal
        // (via the .adjust-quantity class + snipeit.js handler) so
        // an admin can top up stock straight from the request queue
        // instead of hopping to the item's own page. Only rendered
        // for qty-tracked types with update permission - the
        // transformer gates both. URL map mirrors the per-type web
        // adjust-quantity routes.
        if (permissions.replenish && row.requestable) {
            var adjustBase;
            switch (row.requestable.type) {
                case 'Accessory':
                    adjustBase = '{{ config('app.url') }}/accessories/' + row.requestable.id + '/adjust-quantity';
                    break;
                case 'Consumable':
                    adjustBase = '{{ config('app.url') }}/consumables/' + row.requestable.id + '/adjust-quantity';
                    break;
                case 'Component':
                    adjustBase = '{{ config('app.url') }}/components/' + row.requestable.id + '/adjust-quantity';
                    break;
                default:
                    adjustBase = null;
            }
            if (adjustBase) {
                // Same plus-minus icon + sr-only label pattern the
                // other adjust-quantity buttons use across the app
                // (see the accessory / consumable / component index
                // formatters), so the button looks familiar and
                // stays icon-only next to the neighbouring cancel /
                // checkout affordances. Name is already e()'d by
                // the transformer, so no extra JS-side escape.
                actions += ' <button type="button" class="actions btn btn-sm btn-primary adjust-quantity" data-tooltip="true"' +
                    ' title="{{ trans('general.adjust_quantity') }}"' +
                    ' data-adjust-url="' + adjustBase + '"' +
                    ' data-item-name="' + (row.requestable.name || '') + '"' +
                    ' data-available="' + (row.requestable.remaining != null ? row.requestable.remaining : '') + '">' +
                    '<x-icon type="plus-minus" class="fa-fw" /><span class="sr-only">{{ trans('general.adjust_quantity') }}</span>' +
                    '</button> ';
            }
        }

        return actions;
    }

    // Self-cancel button for /account/requested. Row shape carries a
    // ready-to-POST cancel_url (built server-side so the type/id path
    // segments stay in sync with the route regex), which the user
    // owns because ProfileController::requestedAssets scopes to the
    // authed user in the first place.
    function userRequestCancelFormatter(value, row) {
        if (!value) {
            return '';
        }
        return '<form style="display:inline;" method="POST" action="' + value + '" accept-charset="utf-8">' +
            '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">' +
            '<button class="btn btn-danger btn-sm" data-tooltip="true" title="{{ trans('general.cancel_request') }}">{{ trans('button.cancel') }}</button>' +
            '</form>';
    }

    // Check if checkbox should be selectable
    // Selectability is determined by the API field "selectable" which is set at the Presenter/API Transformer
    // However since different bulk actions have different requirements, we have to walk through the available_actions object
    // to determine whether to disable it
    function checkboxEnabledFormatter (value, row) {
        if (row.available_actions && row.available_actions.bulk_selectable) {
            var values = Object.values(row.available_actions.bulk_selectable);
            if (values.length > 0 && !values.some(function (v) { return v === true; })) {
                return { disabled: true };
            }
        }
    }

    function licenseInOutFormatter(value, row) {
        var user_can_checkout = row.available_actions && row.available_actions.user_can_checkout;

        if (row.disabled === true) {
            return '<span class="btn btn-sm bg-maroon btn-checkout disabled" data-tooltip="true" title="{{ trans('admin/licenses/message.checkout.license_is_inactive') }}">{{ trans('general.checkout') }}</span>';
        } else if ((row.available_actions.checkout === true) && (user_can_checkout === true) && (row.disabled === false)) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkout" class="btn btn-sm bg-maroon btn-checkout" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';
        } else if (row.available_actions.checkout === true) {
            return '<span class="btn btn-sm bg-maroon btn-checkout disabled" data-tooltip="true" title="{{ trans('admin/licenses/message.checkout.unavailable') }}">{{ trans('general.checkout') }}</span>';
        }
    }
    // We need a special formatter for license seats, since they don't work exactly the same
    // Checkouts need the license ID, checkins need the specific seat ID

    function licenseSeatInOutFormatter(value, row) {
        if (row.disabled && (row.assigned_user || row.assigned_asset)) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
        }
        if (row.disabled) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkin" class="btn btn-sm bg-maroon btn-checkout disabled" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkout') }}</a>';
        }
        // The user is allowed to check the license seat out and it's available
        if ((row.available_actions.checkout === true) && (row.user_can_checkout === true) && ((!row.assigned_asset) && (!row.assigned_user))) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.license_id + '/checkout/'+row.id+'" class="btn btn-sm bg-maroon btn-checkout" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';
        }

        // The user is allowed to check the license seat in and it's available
        if ((row.available_actions.checkin === true) && ((row.assigned_asset) || (row.assigned_user))) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkin" class="btn btn-sm bg-purple btn-checkin" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
        }

    }

    function genericCheckinCheckoutFormatter(destination) {
        // camelCase entries in the formatters array whose URL segment
        // is actually hyphenated need a rewrite here even though
        // maintenance-types isn't checkoutable, kept for consistency
        // with the sibling formatters.
        if (destination == 'maintenanceTypes') {
            destination = 'maintenance-types';
        }
        return function (value, row) {

            // The user is allowed to check items out, AND the item is deployable
            if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {

                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkout" class="btn btn-sm bg-maroon btn-checkout" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';

            // The user is allowed to check items out, but the item is not able to be checked out
            } else if (((row.user_can_checkout == false)) && (row.available_actions.checkout == true) && (!row.assigned_to)) {

                // We use slightly different language for assets versus other things, since they are the only
                // item that has a status label
                if (destination =='hardware') {
                    return '<span  data-tooltip="true" title="{{ trans('admin/hardware/general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon btn-checkout disabled">{{ trans('general.checkout') }}</a></span>';
                } else {
                    return '<span  data-tooltip="true" title="{{ trans('general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon btn-checkout disabled">{{ trans('general.checkout') }}</a></span>';
                }

            // The user is allowed to check items in
            } else if (row.available_actions.checkin == true)  {
                if (row.assigned_to) {
                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple btn-checkin" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
                } else if (row.assigned_pivot_id) {
                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.assigned_pivot_id + '/checkin" class="btn btn-sm bg-purple btn-checkin" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
                }

            }

        }


    }

    // Requester-facing name column for accessories on the
    // /account/requestable tabs. Links to the show page only
    // when the caller has the view permission - otherwise plain
    // text, so unprivileged requesters can still see what's
    // requestable without being sent to a 403 on click.
    //
    // The name value is already e()'d by the transformer, so no
    // extra JS-side escape (a second $('<div/>').text().html() would
    // double-escape - Macbook Pro 13" -> &quot; -> &amp;quot;).
    function accessoryRequestableNameFormatter(value, row) {
        var name = value == null ? '' : value;
        @can('view', \App\Models\Accessory::class)
            return '<a href="{{ config('app.url') }}/accessories/' + row.id + '">' + name + '</a>';
        @else
            return name;
        @endcan
    }

    // Action cell for the accessories tab on /account/requestable.
    // Renders either the request-modal opener (row is requestable +
    // not already claimed by this user) or an inline cancel form
    // (row is requestable + this user has an open request). The
    // requestable-scope on the endpoint guarantees requestable=true
    // for every row that reaches this formatter.
    function accessoryRequestableActionsFormatter(value, row) {
        if (!row) {
            return '';
        }
        if (row.assigned_to_self === true) {
            return '<form style="display:inline;" action="{{ config('app.url') }}/account/request/accessory/' + row.id + '" method="POST" accept-charset="utf-8">' +
                '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">' +
                '<input type="hidden" name="active_tab" value="accessories">' +
                '<button class="btn btn-danger btn-sm" type="submit">{{ trans('button.cancel') }}</button>' +
                '</form>';
        }
        return '<button type="button" class="btn btn-primary btn-sm request-item"' +
            ' data-request-url="{{ config('app.url') }}/account/request/accessory/' + row.id + '"' +
            ' data-item-name="' + (row.name || '') + '"' +
            ' data-active-tab="accessories"' +
            ' data-current-qty="1">' +
            '{{ trans('button.request') }}</button>';
    }

    // Requester-facing name column for asset models on the
    // /account/requestable models tab. The link vs. plain-text
    // decision comes from row.available_actions.view (populated by
    // AssetModelsTransformer), NOT a compile-time permission gate
    // inside the formatter - that way the check is one source of
    // truth (the transformer) instead of split across server + JS.
    function assetmodelRequestableNameFormatter(value, row) {
        var name = value == null ? '' : value;
        if (row && row.available_actions && row.available_actions.view) {
            return '<a href="{{ config('app.url') }}/models/' + row.id + '">' + name + '</a>';
        }
        return name;
    }

    // Action cell for the models tab on /account/requestable. Same
    // request/cancel button-swap contract as the accessory version;
    // the POST goes to /account/request/asset_model/{id} (route
    // param is asset_model per the itemType regex in web.php).
    function assetmodelRequestableActionsFormatter(value, row) {
        if (!row) {
            return '';
        }
        if (row.assigned_to_self === true) {
            return '<form style="display:inline;" action="{{ config('app.url') }}/account/request/asset_model/' + row.id + '" method="POST" accept-charset="utf-8">' +
                '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">' +
                '<input type="hidden" name="active_tab" value="models">' +
                '<button class="btn btn-danger btn-sm" type="submit">{{ trans('button.cancel') }}</button>' +
                '</form>';
        }
        return '<button type="button" class="btn btn-primary btn-sm request-item"' +
            ' data-request-url="{{ config('app.url') }}/account/request/asset_model/' + row.id + '"' +
            ' data-item-name="' + (row.name || '') + '"' +
            ' data-active-tab="models"' +
            ' data-current-qty="1">' +
            '{{ trans('button.request') }}</button>';
    }

    // Same shape as accessoryRequestable*Formatter; type-specific
    // so each can carry its own view-permission gate + POST URL
    // segment without any JS-side branching on the row's type.
    // See accessoryRequestableNameFormatter for the no-re-escape
    // rationale.
    function consumableRequestableNameFormatter(value, row) {
        var name = value == null ? '' : value;
        @can('view', \App\Models\Consumable::class)
            return '<a href="{{ config('app.url') }}/consumables/' + row.id + '">' + name + '</a>';
        @else
            return name;
        @endcan
    }

    function consumableRequestableActionsFormatter(value, row) {
        if (!row) {
            return '';
        }
        if (row.assigned_to_self === true) {
            return '<form style="display:inline;" action="{{ config('app.url') }}/account/request/consumable/' + row.id + '" method="POST" accept-charset="utf-8">' +
                '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">' +
                '<input type="hidden" name="active_tab" value="consumables">' +
                '<button class="btn btn-danger btn-sm" type="submit">{{ trans('button.cancel') }}</button>' +
                '</form>';
        }
        return '<button type="button" class="btn btn-primary btn-sm request-item"' +
            ' data-request-url="{{ config('app.url') }}/account/request/consumable/' + row.id + '"' +
            ' data-item-name="' + (row.name || '') + '"' +
            ' data-active-tab="consumables"' +
            ' data-current-qty="1">' +
            '{{ trans('button.request') }}</button>';
    }

    function componentRequestableNameFormatter(value, row) {
        var name = value == null ? '' : value;
        @can('view', \App\Models\Component::class)
            return '<a href="{{ config('app.url') }}/components/' + row.id + '">' + name + '</a>';
        @else
            return name;
        @endcan
    }

    function componentRequestableActionsFormatter(value, row) {
        if (!row) {
            return '';
        }
        if (row.assigned_to_self === true) {
            return '<form style="display:inline;" action="{{ config('app.url') }}/account/request/component/' + row.id + '" method="POST" accept-charset="utf-8">' +
                '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">' +
                '<input type="hidden" name="active_tab" value="components">' +
                '<button class="btn btn-danger btn-sm" type="submit">{{ trans('button.cancel') }}</button>' +
                '</form>';
        }
        return '<button type="button" class="btn btn-primary btn-sm request-item"' +
            ' data-request-url="{{ config('app.url') }}/account/request/component/' + row.id + '"' +
            ' data-item-name="' + (row.name || '') + '"' +
            ' data-active-tab="components"' +
            ' data-current-qty="1">' +
            '{{ trans('button.request') }}</button>';
    }

    function licenseRequestableNameFormatter(value, row) {
        var name = value == null ? '' : value;
        @can('view', \App\Models\License::class)
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '">' + name + '</a>';
        @else
            return name;
        @endcan
    }

    function licenseRequestableActionsFormatter(value, row) {
        if (!row) {
            return '';
        }
        if (row.assigned_to_self === true) {
            return '<form style="display:inline;" action="{{ config('app.url') }}/account/request/license/' + row.id + '" method="POST" accept-charset="utf-8">' +
                '<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">' +
                '<input type="hidden" name="active_tab" value="licenses">' +
                '<button class="btn btn-danger btn-sm" type="submit">{{ trans('button.cancel') }}</button>' +
                '</form>';
        }
        return '<button type="button" class="btn btn-primary btn-sm request-item"' +
            ' data-request-url="{{ config('app.url') }}/account/request/license/' + row.id + '"' +
            ' data-item-name="' + (row.name || '') + '"' +
            ' data-item-type="license"' +
            ' data-active-tab="licenses"' +
            ' data-current-qty="1">' +
            '{{ trans('button.request') }}</button>';
    }

    // This is only used by the requestable assets section
    function assetRequestActionsFormatter (row, value) {
        if (value.assigned_to_self == true){
            return '<button class="btn btn-danger btn-sm btn-block disabled" data-tooltip="true" title="{{ trans('admin/hardware/message.requests.cancel') }}">{{ trans('button.cancel') }}</button>';
        } else if (value.available_actions.cancel == true)  {
            return '<form action="{{ config('app.url') }}/account/request-asset/' + value.id + '/cancel" method="POST">@csrf<input type="hidden" name="active_tab" value="assets"><button class="btn btn-danger btn-block btn-sm" data-tooltip="true" title="{{ trans('admin/hardware/message.requests.cancel') }}">{{ trans('button.cancel') }}</button></form>';
        } else if (value.available_actions.request == true)  {
            // Open the request-item modal instead of submitting a
            // plain form, so the requester can enter a qty (assets
            // always request qty 1, but the modal also collects the
            // optional reservation dates that landed with the
            // start_date / end_date columns on checkout_requests).
            // data-active-tab is a defensive belt with the closest
            // .tab-pane walk in snipeit.js: bs-tables re-renders rows
            // into detached DOM during redraws, and the explicit attr
            // survives that where the DOM-tree walk would fall short.
            // Name / asset_tag are already e()'d by the transformer,
            // so no extra JS-side escape.
            var itemName = value.name || value.asset_tag || '';
            var requestUrl = '{{ config('app.url') }}/account/request/asset/' + value.id;
            return '<button type="button" class="btn btn-block btn-primary btn-sm request-item"' +
                ' data-tooltip="true" title="{{ trans('general.request_item') }}"' +
                ' data-request-url="' + requestUrl + '"' +
                ' data-item-name="' + itemName + '"' +
                ' data-item-type="asset"' +
                ' data-active-tab="assets"' +
                ' data-current-qty="1">' +
                '{{ trans('button.request') }}</button>';
        }

    }



    var formatters = [
        'accessories',
        'categories',
        'companies',
        'components',
        'consumables',
        'departments',
        'depreciations',
        'fieldsets',
        'groups',
        'hardware',
        'kits',
        'licenses',
        'locations',
        'maintenances',
        'maintenanceTypes',
        'manufacturers',
        'models',
        'statuslabels',
        'suppliers',
        'users',
    ];

    for (var i in formatters) {
        window[formatters[i] + 'LinkFormatter'] = genericRowLinkFormatter(formatters[i]);
        window[formatters[i] + 'LinkObjFormatter'] = genericColumnObjLinkFormatter(formatters[i]);
        window[formatters[i] + 'ActionsFormatter'] = genericActionsFormatter(formatters[i]);
        window[formatters[i] + 'InOutFormatter'] = genericCheckinCheckoutFormatter(formatters[i]);
    }

    // Maintenances need a custom actions formatter (adds the green
    // "mark complete" button between update and delete). Defined AFTER the
    // generic-formatter loop above so it overrides the auto-generated
    // maintenancesActionsFormatter.
    window.maintenancesActionsFormatter = function (value, row) {
        var actions = '<nobr>';

        if ((row.available_actions) && (row.available_actions.update === true)) {
            actions += '<a href="{{ config('app.url') }}/maintenances/' + row.id + '/edit" class="actions btn btn-sm btn-warning hidden-print" data-tooltip="true" title="{{ trans('general.update') }}"><x-icon type="edit" class="fa-fw" /><span class="sr-only">{{ trans('general.update') }}</span></a>&nbsp;';
        }

        if ((row.available_actions) && (row.available_actions.complete === true)) {
            actions += '<button type="button" class="actions btn btn-sm btn-success hidden-print complete-maintenance" data-tooltip="true" title="{{ trans('admin/maintenances/form.mark_complete') }}" data-url="{{ config('app.url') }}/maintenances/' + row.id + '/complete"><x-icon type="checkmark" class="fa-fw" /><span class="sr-only">{{ trans('admin/maintenances/form.mark_complete') }}</span></button>&nbsp;';
        } else {
            actions += '<button type="button" class="actions btn btn-sm btn-default hidden-print disabled" disabled data-tooltip="true" title="{{ trans('admin/maintenances/form.already_complete') }}"><x-icon type="checkmark" class="fa-fw" /><span class="sr-only">{{ trans('admin/maintenances/form.already_complete') }}</span></button>&nbsp;';
        }

        if ((row.available_actions) && (row.available_actions.delete === true)) {
            actions += '<a href="{{ config('app.url') }}/maintenances/' + row.id + '" '
                + ' class="actions btn btn-danger btn-sm delete-asset hidden-print" data-tooltip="true" '
                + ' data-toggle="modal" data-icon="fa-trash"'
                + ' data-content="{{ trans('general.sure_to_delete') }}: ' + row.name + '?" '
                + ' data-title="{{ trans('general.delete') }}" onClick="return false;">'
                + '<x-icon type="delete" class="fa-fw" /><span class="sr-only">{{ trans('general.delete') }}</span></a>&nbsp;';
        }

        actions += '</nobr>';
        return actions;
    };

    // Shared markup for the adjust-quantity plus-minus button. Used by
    // genericActionsFormatter's adjust_quantity branch above AND by
    // lowStockActionsFormatter below so both call sites render an
    // identical button and open the same shared adjust-quantity modal.
    // Callers pass the per-row URL segment as `dest` (accessories /
    // consumables / components), the numeric row id, the display name
    // for the modal header, and the available count for the modal help
    // text.
    window.renderAdjustQuantityButton = function (opts) {
        return '<button type="button" class="actions btn btn-sm btn-primary hidden-print adjust-quantity" data-tooltip="true" title="{{ trans('general.adjust_quantity') }}"'
            + ' data-adjust-url="{{ config('app.url') }}/' + opts.dest + '/' + opts.id + '/adjust-quantity"'
            + ' data-item-name="' + (opts.name || '') + '"'
            + ' data-available="' + (opts.available != null ? opts.available : '') + '">'
            + '<x-icon type="plus-minus" class="fa-fw" /><span class="sr-only">{{ trans('general.adjust_quantity') }}</span></button>';
    };

    // Dashboard low-stock widget actions column. Renders only the
    // adjust-quantity plus-minus button (there's no edit/delete on this
    // widget), gated on the row's available_actions.adjust_quantity.
    // Row is polymorphic across consumables / accessories / components /
    // asset_models / licenses; item.type carries the per-row URL segment,
    // pluralized to match the sibling web routes.
    window.lowStockActionsFormatter = function (value, row) {
        if (!row || !row.item || !value || value.adjust_quantity !== true) {
            return '';
        }
        var typeToDest = {
            consumable: 'consumables',
            accessory: 'accessories',
            component: 'components',
        };
        var dest = typeToDest[row.item.type];
        if (!dest) {
            return '';
        }
        return '<nobr>' + window.renderAdjustQuantityButton({
            dest: dest,
            id: row.item.id,
            name: row.item.name || '',
            available: row.remaining,
        }) + '</nobr>';
    };

    var child_formatters = [
        ['kits', 'models'],
        ['kits', 'licenses'],
        ['kits', 'consumables'],
        ['kits', 'accessories'],
    ];

    for (var i in child_formatters) {
        var owner_name = child_formatters[i][0];
        var child_name = child_formatters[i][1];
        window[owner_name + '_' + child_name + 'ActionsFormatter'] = genericActionsFormatter(owner_name, child_name);
    }



    // This is  gross, but necessary so that we can package the API response
    // for custom fields in a more useful way.
    function customFieldsFormatter(value, row) {


            if ((!this) || (!this.title)) {
                return '';
            }

            var field_column = this.title;

            // Pull out any HTMl that might be passed via the presenter
            // (for example, the locked icon for encrypted fields)
            var field_column_plain = field_column.replace(/<(?:.|\n)*?> ?/gm, '');
            if ((row.custom_fields) && (row.custom_fields[field_column_plain])) {

                // If the field type needs special formatting, do that here
                if ((row.custom_fields[field_column_plain].field_format) && (row.custom_fields[field_column_plain].value)) {
                    if (row.custom_fields[field_column_plain].field_format=='URL') {
                        return '<a href="' + row.custom_fields[field_column_plain].value + '" target="_blank" rel="noopener">' + row.custom_fields[field_column_plain].value + '</a>';
                    } else if (row.custom_fields[field_column_plain].field_format=='BOOLEAN') {
                        return (row.custom_fields[field_column_plain].value == 1) ? "<span class='fas fa-check-circle' style='color:green'>" : "<span class='fas fa-times-circle' style='color:red' />";
                    } else if (row.custom_fields[field_column_plain].field_format=='EMAIL') {
                        return '<a href="mailto:' + row.custom_fields[field_column_plain].value + '" style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.send_email') }}"><x-icon type="email" /> ' + row.custom_fields[field_column_plain].value + '</a>';
                    }
                }
                // Convert newlines to <br> for textarea fields so they render in
                // the table; export will convert <br> back to \n via onCellHtmlData
                if (row.custom_fields[field_column_plain].element === 'textarea') {
                    var val = row.custom_fields[field_column_plain].value;
                    return val ? val.replace(/(?:\r\n|\r|\n)/g, '<br>') : '';
                }

                return row.custom_fields[field_column_plain].value;

            }

    }


    function createdAtFormatter(value) {
        if ((value) && (value.formatted)) {
            return value.formatted;
        }
    }

    function externalLinkFormatter(value) {

        if (value) {
            if ((value.indexOf("{") === -1) || (value.indexOf("}") ===-1)) {
                return '<nobr><a href="' + value + '" target="_blank" title="{{ trans('general.external_link_tooltip') }} ' + value + '" data-tooltip="true"><x-icon type="external-link" /> ' + value + '</a></nobr>';
            }
            return value;
        }
    }

    function groupsFormatter(value) {

        if (value) {
            var groups = '';
            for (var index in value.rows) {
                groups += '<a href="{{ config('app.url') }}/admin/groups/' + value.rows[index].id + '" class="label label-light">' + value.rows[index].name + '</a> ';
            }
            return groups;
        }
    }



    function changeLogFormatter(value) {

        var result = '<div style="word-break: break-word;">';
        var pretty_index = '';

            for (var index in value) {


                // Check if it's a custom field
                if (index.startsWith('_snipeit_')) {
                    pretty_index = index.replace("_snipeit_", "Custom:_");
                } else {
                    pretty_index = index;
                }

                extra_pretty_index = prettyLog(pretty_index);

                result += extra_pretty_index + ': <del>' + value[index].old + '</del>  <x-icon type="long-arrow-right" /> ' + value[index].new + '<br>'
            }

        return result+'</div>';

    }

    function prettyLog(str) {
        let frags = str.split('_');
        for (let i = 0; i < frags.length; i++) {
            frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
        }
        return frags.join(' ');
    }

    // Show the warning if below min qty
    function minAmtFormatter(row, value) {

        if ((row) && (row!=undefined)) {

            if (value.remaining < value.min_amt) {
                return  '<span class="text-danger text-bold" data-tooltip="true" title="{{ trans('admin/licenses/general.below_threshold_short') }}"><x-icon type="warning" class="text-yellow" /> ' + value.min_amt + '</span>';
            }
            return value.min_amt
        }
        return '--';
    }

    

    // Create a linked phone number in the table list
    function phoneFormatter(value) {
        if (value) {
            return  '<span style="white-space: nowrap;"><a href="tel:' + value + '" data-tooltip="true" title="{{ trans('general.call') }}"><x-icon type="phone" /> ' + value + '</a></span>';
        }
    }

    // Create a linked phone number in the table list
    function mobileFormatter(value) {
        if (value) {
            return  '<span style="white-space: nowrap;"><a href="tel:' + value + '" data-tooltip="true" title="{{ trans('general.call') }}"><x-icon type="mobile" /> ' + value + '</a></span>';
        }
    }


    function deployedLocationFormatter(row, value) {
        if ((row) && (row!=undefined)) {
            // Handle the preceding icon if a tag_color is given in the API response
            if ((row.tag_color) && (row.tag_color!='')) {
                var tag_icon = '<i class="fa-solid fa-square" style="color: ' + row.tag_color + ';" aria-hidden="true"></i> ';
            } else {
                var tag_icon = '';
            }

            return '<nobr>' + tag_icon +'<a href="{{ config('app.url') }}/locations/' + row.id + '">' + row.name + '</a></nobr>';
        } else if (value.rtd_location) {
            return '<a href="{{ config('app.url') }}/locations/' + value.rtd_location.id + '">' + value.rtd_location.name + '</a>';
        }

    }

    function groupsAdminLinkFormatter(value, row) {
        return '<a href="{{ config('app.url') }}/admin/groups/' + row.id + '">' + value + '</a>';
    }

    function assetTagLinkFormatter(value, row) {
        if ((row.asset) && (row.asset.id)) {
            if (row.asset.deleted_at) {
                return '<span style="white-space: nowrap;"><x-icon type="x" class="text-danger" /><span class="sr-only">{{ trans('admin/hardware/general.deleted') }}</span> <del><a href="{{ config('app.url') }}/hardware/' + row.asset.id + '" data-tooltip="true" title="{{ trans('admin/hardware/general.deleted') }}">' + row.asset.asset_tag + '</a></del></span>';
            }
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.asset_tag + '</a>';
        }
        return '';

    }

    function departmentNameLinkFormatter(value, row) {
        if ((row.assigned_user) && (row.assigned_user.department) && (row.assigned_user.department.name)) {
            return '<a href="{{ config('app.url') }}/departments/' + row.assigned_user.department.id + '">' + row.assigned_user.department.name + '</a>';
        }

    }

    function assetNameLinkFormatter(value, row) {
        if ((row.asset) && (row.asset.name)) {
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.name + '</a>';
        }
    }

    function assetSerialLinkFormatter(value, row) {

        if ((row.asset) && (row.asset.serial)) {
            if (row.asset.deleted_at) {
                return '<span style="white-space: nowrap;"><x-icon type="x" class="text-danger" /><span class="sr-only">deleted</span> <del><a href="{{ config('app.url') }}/hardware/' + row.asset.id + '" data-tooltip="true" title="{{ trans('admin/hardware/general.deleted') }}">' + row.asset.serial + '</a></del></span>';
            }
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.serial + '</a>';
        }
        return '';
    }

    function trueFalseFormatter(value) {
        if ((value) && ((value == 'true') || (value == '1'))) {
            return '<x-icon type="checkmark" class="text-success" /><span class="sr-only">{{ trans('general.true') }}</span>';
        } else {
            return '<x-icon type="x" class="text-danger" /><span class="sr-only">{{ trans('general.false') }}</span>';
        }
    }

    function dateDisplayFormatter(value) {
        if (value) {
            return  value.formatted;
        }
    }

    function iconFormatter(value) {
        if (value) {
            return '<i class="' + value + '  icon-med"></i>';
        }
    }

    function emailFormatter(value) {
        if (value) {
            return '<a href="mailto:' + value + '" style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.send_email') }}"><x-icon type="email" /> ' + value + '</a>';
        }
    }

    function linkFormatter(value) {
        if (value) {
            return '<a href="' + value + '">' + value + '</a>';
        }
    }

    function assetCompanyFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/hardware/?company_id=' + row.id + '">' + value + '</a>';
        }
    }

    function assetCompanyObjFilterFormatter(value, row) {
        if ((row) && (row.company)) {
            return '<a href="{{ config('app.url') }}/hardware/?company_id=' + row.company.id + '">' + row.company.name + '</a>';
        }
    }

    function usersCompanyObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/users/?company_id=' + row.id + '">' + value + '</a>';
        } else {
            return value;
        }
    }

    // Renders a single company tag. `isInherited` is decided by the caller —
    // it's true only when (a) we're on the companies show page (viewing context
    // is set), (b) the row didn't get included via direct membership, and (c)
    // THIS specific tag is the parent or a child of the viewing company. Other
    // unrelated memberships on the same row render unmarked.
    function renderCompanyTag(c, isInherited) {
        var color = (c.tag_color)
            ? '<i class="fa-solid fa-square" style="color: ' + c.tag_color + ';" aria-hidden="true"></i> '
            : '';
        if (isInherited) {
            return '<a href="{{ config('app.url') }}/companies/' + c.id + '"'
                + ' class="label label-light"'
                + ' data-tooltip="true"'
                + ' title="{{ trans('admin/companies/table.inherited_help') }}">'
                + '<i class="fa-solid fa-link" aria-hidden="true"></i> '
                + color + c.name
                + ' <span class="sr-only">({{ trans('admin/companies/table.inherited') }})</span>'
                + '</a>';
        }
        return '<a href="{{ config('app.url') }}/companies/' + c.id + '" class="label label-light">'
            + color + c.name
            + '</a>';
    }

    // A tag is "inherited" when (1) we're on a company show page, (2) the row
    // didn't include the viewing company itself (i.e. the user/asset isn't a
    // direct member), AND (3) this specific tag IS in the viewing company's
    // hierarchy set (parent or one of the children). Tags unrelated to the
    // hierarchy never get marked, even on otherwise-inherited rows.
    function isCompanyTagInherited(tagId, rowCompanyIds) {
        if (typeof window.viewingCompanyId === 'undefined' || window.viewingCompanyId === null) {
            return false;
        }
        if (typeof window.viewingCompanyHierarchyIds === 'undefined' || !window.viewingCompanyHierarchyIds) {
            return false;
        }

        var viewing = parseInt(window.viewingCompanyId, 10);
        var tag = parseInt(tagId, 10);

        // Row is direct (some company on the row matches viewing) — nothing
        // on this row counts as inherited.
        var rowIsDirect = rowCompanyIds.some(function (id) { return parseInt(id, 10) === viewing; });
        if (rowIsDirect) {
            return false;
        }

        // Only tags that are part of the hierarchy set get the badge.
        return window.viewingCompanyHierarchyIds.some(function (id) { return parseInt(id, 10) === tag; })
            && tag !== viewing;
    }

    function companiesLinkObjFormatter(value, row) {
        if (!value) {
            return '';
        }
        return renderCompanyTag(value, isCompanyTagInherited(value.id, [value.id]));
    }

    function companiesArrayLinkFormatter(value, row) {
        if (!value || !value.length) {
            return '';
        }
        var rowCompanyIds = value.map(function (c) { return c.id; });
        return value.map(function (c) {
            return renderCompanyTag(c, isCompanyTagInherited(c.id, rowCompanyIds));
        }).join(' ');
    }

    function locationCompanyObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ url('/') }}/locations/?company_id=' + row.company.id + '">' + row.company.name + '</a>';
        } else {
            return value;
        }
    }

    function employeeNumFormatter(value, row) {

        if ((row) && (row.assigned_to) && ((row.assigned_to.employee_number))) {
            return '<a href="{{ config('app.url') }}/users/' + row.assigned_to.id + '">' + row.assigned_to.employee_number + '</a>';
        }
    }

    function jobtitleFormatter(value, row) {
        if ((row) && (row.assigned_to) && ((row.assigned_to.jobtitle))) {
            return '<a href="{{ config('app.url') }}/users/' + row.assigned_to.id + '">' + row.assigned_to.jobtitle + '</a>';
        }
    }

    function orderNumberObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/hardware/?order_number=' + row.order_number + '">' + row.order_number + '</a>';
        }
    }

    function auditImageFormatter(value, row) {
        if ((row) && (row.file) && (row.file.url)) {
            return '<a href="' + row.file.url + '" data-toggle="lightbox" data-type="image"><img src="' + row.file.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt=""></a>'
        }
    }


   function imageFormatter(value, row) {

        if (value) {

            // This is a clunky override to handle unusual API responses where we're presenting a link instead of an array
            if (row.avatar) {
                var altName = '';
            }
            else if (row.name) {
                var altName = row.name;
            }
            else if ((row) && (row.model)) {
                var altName = row.model.name;
           }
            return '<a href="' + value + '" data-toggle="lightbox" data-type="image"><img src="' + value + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt="' + altName + '"></a>';
        }
    }


    // This is users in the user accounts section for EULAs
    function downloadFormatter(value) {
        if (value) {
            return '<a href="' + value + '" class="btn btn-sm btn-theme"><x-icon type="download" /></a>';
        }
    }

    // This is used by the UploadedFilesPresenter and the HistoryPresenter
    // It handles the download and inline buttons for files that are uploaded to assets, users, etc
    function fileDownloadButtonsFormatter(row, value) {

        if (value)  {
            if (value.url) {
                var inlinable = value.inlineable;
                var exists_on_disk = value.exists_on_disk;
                var download_url = value.url;
            } else if (value.file) {
                var inlinable = value.file.inlineable;
                var exists_on_disk = value.file.exists_on_disk;
                var download_url = value.file.url;
            } else {
                return '';
            }

            var download_button = '<a href="' + download_url + '" class="btn btn-sm btn-theme" data-tooltip="true" title="{{ trans('general.download') }}"><x-icon type="download" /></a>';
            var download_button_disabled = '<span data-tooltip="true" title="{{ trans('general.file_does_not_exist') }}"><a class="btn btn-sm btn-theme disabled"><x-icon type="download" /></a></span>';
            var inline_button = '<a href="'+ download_url +'?inline=true" class="btn btn-sm btn-theme" target="_blank" data-tooltip="true" title="{{ trans('general.open_new_window') }}"><x-icon type="external-link" /></a>';
            var inline_button_disabled = '<span data-tooltip="true" title="{{ trans('general.file_not_inlineable') }}"><a class="btn btn-sm btn-theme disabled" target="_blank" data-tooltip="true" title="{{ trans('general.file_does_not_exist') }}"><x-icon type="external-link" /></a></span>';

            if (exists_on_disk === true) {
                if (inlinable === true) {
                    return '<span style="white-space: nowrap;">' + download_button + ' ' + inline_button + '</span>';
                } else {
                    return '<span style="white-space: nowrap;">' + download_button + ' ' + inline_button_disabled + '</span>';
                }
            } else {
                return '<span style="white-space: nowrap;">' + download_button_disabled + ' ' + inline_button_disabled + '</span>';
            }

        }
    }


    function filePreviewFormatter(row, value) {

        if ((value) && (value.url) && (value.inlineable)) {

            if (value.mediatype == 'image') {
                return '<a href="' + value.url + '?inline=true" data-toggle="lightbox" data-type="image"><img src="' + value.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt=""></a>';
            } else if (value.mediatype == 'video') {
                return '<a href="' + value.url + '?inline=true" data-toggle="lightbox" data-type="video"><video style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive"><source src="' + value.url + '?inline=true"></video></a>';
            } else if (value.mediatype == 'audio') {
                return '<audio controls><source src="' + value.url + '?inline=true" type="audio/mp3">Your browser does not support the audio element.</audio>';
            }
            return '{{ trans('general.preview_not_available') }}';
        }
        return '{{ trans('general.preview_not_available') }}';

    }




    // This is used in the table listings
    function deleteUploadFormatter(value, row) {

        if ((row.available_actions) && (row.available_actions.delete === true)) {
            var destination;

            // This is kinda gross, but for right now we're posting to the GUI delete routes
            // All of these URLS and storage directories need to be updated to be more consistent :(
            if (row.item.type === 'assetmodels') {
                destination = 'models';
            } else if (row.item.type === 'assets') {
                destination = 'hardware';
            } else {
                destination = row.item.type;
            }

            return '<a href="{{ config('app.url') }}/' + destination + '/' + row.item.id + '/files/' + row.id + '/delete" '
                + ' data-target="#dataConfirmModal" class="actions btn btn-danger btn-sm delete-asset" data-tooltip="true"  '
                + ' data-toggle="modal" data-icon="fa-trash"'
                + ' data-content="{{ trans('general.file_upload_status.confirm_delete') }}: ' + row.filename + '?" '
                + ' data-title="{{  trans('general.delete') }}" onClick="return false;" data-icon="fa-trash">'
                + '<x-icon type="delete" /><span class="sr-only">{{ trans('general.delete') }}</span></a>&nbsp;';
        }
    }

    // This handles the custom view for the filestable blade component gallery-card component
    window.customViewFormatter = data => {
        const template = $('#fileGalleryTemplate').html()
        let view = ''

        $.each(data, function (i, row) {

            delete_url = row.url +'/delete';

            if (row.exists_on_disk === true)
            {
                if (row.mediatype === 'image') {
                    embed_code = '<a href="' + row.url + '" data-toggle="lightbox" data-type="image" data-title="' + row.filename + row.filename + '" data-footer="' + row.note + '" class="embed-responsive-item"><img src="' + row.url + '?inline=true" alt="" style="max-width: 100%"></a>';
                } else if (row.mediatype === 'video') {
                    embed_code = '<a href="' + row.url + '" data-toggle="lightbox" data-type="video" data-title="' + row.filename + row.filename + '" data-footer="' + row.note + '" class="embed-responsive-item"><video controls><source src="' + row.url + '?inline=true" type="video/mp4">Your browser does not support the video tag.</video></a>';
                } else if (row.mediatype === 'audio') {
                    embed_code = '<audio style="width: 100%" controls><source src="' + row.url + '?inline=true" type="audio/mpeg">Your browser does not support the audio element.</audio>';
                } else if (row.mediatype === 'pdf') {
                    embed_code = '<object height="200" style="width: 100%" type="application/pdf" data="' + row.url + '?inline=true">File cannot be displayed</object>';
                } else {
                    embed_code = '<div class="text-center"><a href="' + row.url + '?inline=true"><i class="' + row.icon + '" style="font-size: 50px" /></i></a></div>';
                }
            } else {
                embed_code = '<div class="text-center text-danger" style="padding-top: 20px;"><i class="fa-solid fa-heart-crack" style="font-size: 80px" /></i> <br><br>{{ trans('general.file_upload_status.file_not_found') }}</div>';
            }

            view += template.replace('%ID%', row.id)
                .replace('%ICON%', row.icon)
                .replace('%FILETYPE%', row.filetype)
                .replace('%FILE_URL%', row.url)
                .replace('%LINK_URL%', row.url)
                .replace('%FILENAME%', (row.exists_on_disk === true) ? row.filename : '<x-icon type="x" /> <del>' + row.filename + '</del>')
                .replace('%CREATED_AT%', row.created_at.formatted)
                .replace('%CREATED_BY%', (row.created_by) ? row.created_by.name : '')
                .replace('%NOTE%', (row.note) ? row.note : '')
                .replace('%PANEL_CLASS%', (row.exists_on_disk === true) ? 'default' : 'danger')
                .replace('%FILE_EMBED%', embed_code)
                .replace('%DOWNLOAD_BUTTON%', (row.exists_on_disk === true) ? '<a href="'+ row.url +'" class="btn btn-sm btn-theme"><x-icon type="download" /></a> ' : '<span class="btn btn-sm btn-theme disabled" data-tooltip="true" title="{{ trans('general.file_upload_status.file_not_found') }}"><x-icon type="download" /></span>')
                .replace('%NEW_WINDOW_BUTTON%', (row.exists_on_disk === true) ? '<a href="'+ row.url +'?inline=true" class="btn btn-sm btn-theme" target="_blank"><x-icon type="external-link" /></a> ' : '<span class="btn btn-sm btn-theme disabled" data-tooltip="true" title="{{ trans('general.file_upload_status.file_not_found') }}"><x-icon type="external-link"/></span>')
                .replace('%DELETE_BUTTON%', (row.available_actions.delete === true) ?
                    '<a href="'+delete_url+'" class="delete-asset btn btn-danger btn-sm" data-icon="fa-trash" data-toggle="modal" data-content="{{ trans('general.file_upload_status.confirm_delete') }} '+ row.filename +'?" data-title="{{ trans('general.delete') }}" onClick="return false;" data-target="#dataConfirmModal"><x-icon type="delete" /><span class="sr-only">{{ trans('general.delete') }}</span></a>' :
                    '<a class="btn btn-sm btn-danger disabled" data-tooltip="true" title="{{ trans('general.file_upload_status.file_not_found') }}"><x-icon type="delete" /><span class="sr-only">{{ trans('general.delete') }}</span></a>'
                );
        })

        return `<div class="row">${view}</div>`
    }



    function fileNameFormatter(row, value) {

        if (value) {
            if ((value.file) && (value.file.filename) && (value.file.url)) {

                if (value.file.exists_on_disk === true) {
                    return '<a href="' + value.file.url + '">' + value.file.filename + '</a>';
                }

                return '<span class="text-danger" style="text-decoration: line-through;" data-tooltip="true" title="{{ trans('general.file_does_not_exist') }}"><x-icon type="x" /> ' + value.file.filename + '</span>';

            } else if ((value.filename) && (value.url)) {
                if (value.exists_on_disk === true) {
                    return '<a href="' + value.url + '">' + value.filename + '</a>';
                }
                return '<span class="text-danger" style="text-decoration: line-through;" data-tooltip="true" title="{{ trans('general.file_does_not_exist') }}"><x-icon type="x" /> ' + value.filename + '</span>';
            }
        }

    }


    function linkToUserSectionBasedOnCount (count, id, section) {
        if (count) {
            return '<a href="{{ config('app.url') }}/users/' + id + '#' + section +'">' + count + '</a>';
        }

        return count;
    }

    function linkNumberToUserAssetsFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'asset');
    }

    function linkNumberToUserLicensesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'licenses');
    }

    function linkNumberToUserConsumablesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'consumables');
    }

    function linkNumberToUserAccessoriesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'accessories');
    }

    function linkNumberToUserManagedUsersFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'managed-users');
    }

    function linkNumberToUserManagedLocationsFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'managed-locations');
    }

    function linkNumberToUserAssignedMaintenancesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'maintenances');
    }

    function labelActionsFormatter(value, row) {
        if (!row.name) return '';

        const createUrl = '{{ route('settings.labels.create') }}';
        // Clone Button
        let cloneHref;

        if (row.source === 'custom') {
            cloneHref = createUrl + '?custom_label_id=' + row.custom_label_id;
        } else {
            cloneHref = createUrl + '?label=' + encodeURIComponent(row.name);
        }

        let actions = '<a href="' + cloneHref + '" '
            + 'class="actions btn btn-sm btn-primary hidden-print" '
            + 'data-tooltip="true" '
            + 'title="{{ trans('general.clone') }}">'
            + '<i class="fa-regular fa-clone"></i>'
            + '<span class="sr-only">{{ trans('general.clone') }}</span>'
            + '</a>&nbsp;';
        // Only allow delete, share or edit for custom labels
        if (row.source === 'custom') {
            // Update Button
            var editUrl = editLabelUrlTemplate.replace('label_id', row.custom_label_id);

            actions += '<a href="' + editUrl + '" '
                + 'class="actions btn btn-sm btn-warning hidden-print" '
                + 'data-tooltip="true" '
                + 'title="{{ trans('general.update') }}">'
                + '<x-icon type="edit" class="fa-fw" />'
                + '<span class="sr-only">{{ trans('general.update') }}</span>'
                + '</a>&nbsp;';
            // Share button
            var encodedJson = encodeURIComponent(JSON.stringify(row.config_snapshot || {}, null, 2));

            actions += '<button type="button" '
                + 'class="btn btn-primary btn-sm export-label-json hidden-print" '
                + 'data-json="' + encodedJson + '" '
                + 'data-name="' + encodeURIComponent(row.name || "label") + '" '
                + 'title="Export Label" '
                + 'data-tooltip="true">'
                + '<i class="fa-solid fa-file-export"></i>'
                + '<span class="sr-only">Export Label</span>'
                + '</button>&nbsp;';

            // Delete button
            var safeName = $('<div>').text(row.name || 'this label').html().replace(/"/g, '&quot;');
            var deleteUrl = deleteLabelUrlTemplate.replace('label_id', row.custom_label_id);

            actions += '<a href="' + deleteUrl + '" '
                + 'class="actions btn btn-danger btn-sm delete-custom-label hidden-print" '
                + 'data-tooltip="true" '
                + 'data-icon="fa-trash" '
                + 'data-content="{{ trans('general.sure_to_delete') }}: ' + safeName + '?" '
                + 'data-title="{{ trans('general.delete') }}">'
                + '<x-icon type="delete" class="fa-fw" />'
                + '<span class="sr-only">{{ trans('general.delete') }}</span>'
                + '</a>&nbsp;';
        }


        return actions;
    }

    function labelNameFormatter(value) {
        if (!value) return '';
        value = String(value).replace(/\//g, '\\').replace(/^(Sheets|Tapes)\\/, '');
        return $('<div/>').text(value).html();
    }

    function labelPerPageFormatter(value, row, index, field) {
        if (row) {
            if (!row.hasOwnProperty('sheet_info')) { return 1; }
            else { return row.sheet_info.labels_per_page; }
        }
    }

    function labelRadioFormatter(value, row, index, field) {
        if (row) {
            return row.name == '{{ str_replace("\\", "\\\\", $snipeSettings->label2_template) }}';
        }
    }

    function labelSizeFormatter(value, row) {
        if (row) {
            return row.width + ' x ' + row.height + ' ' + row.unit;
        }
    }

    function cleanFloat(number) {
        if(!number) { // in a JavaScript context, meaning, if it's null or zero or unset
            return 0.0;
        }
        if ("{{$snipeSettings->digit_separator}}" == "1.234,56") {
            // yank periods, change commas to periods
            periodless = number.toString().replace(/\./g,"");
            decimalfixed = periodless.replace(/,/g,".");
        } else {
            // yank commas, that's it.
            decimalfixed = number.toString().replace(/\,/g,"");
        }
        return parseFloat(decimalfixed);
    }


    function qtySumFormatter(data) {
        var currentField = this.field;
        var total = 0;
        var fieldname = this.field;

        $.each(data, function() {
            var r = this;
            total += this[currentField];
        });
        return total;
    }

    function sumFormatter(data) {
        if (Array.isArray(data)) {
            var field = this.field;
            var total_sum = data.reduce(function(sum, row) {
                
                return (sum) + (cleanFloat(row[field]) || 0);
            }, 0);
            
            return numberWithCommas(total_sum.toFixed(2));
        }
        return 'not an array';
    }

    function sumFormatterQuantity(data){
        if(Array.isArray(data)) {
            
            // Prevents issues on page load where data is an empty array
            if(data[0] == undefined){
                return 0.00
            }
            // Check that we are actually trying to sum cost from a table
            // that has a quantity column. We must perform this check to
            // support licences which use seats instead of qty
            if('qty' in data[0]) {
                var multiplier = 'qty';
            } else if('seats' in data[0]) {
                var multiplier = 'seats';
            } else {
                return 'no quantity';
            }
            var total_sum = data.reduce(function(sum, row) {
                return (sum) + (cleanFloat(row["purchase_cost"])*row[multiplier] || 0);
            }, 0);
            return numberWithCommas(total_sum.toFixed(2));
        }
        return 'not an array';
    }

    function numberWithCommas(value) {
        
        if ((value) && ("{{$snipeSettings->digit_separator}}" == "1.234,56")){
            var parts = value.toString().split(".");
             parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
             return parts.join(",");
         } else {
             var parts = value.toString().split(",");
             parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
             return parts.join(".");
        }
        return value
    }

    function bindBulkEditSelectionHandler() {
        $('#bulkEdit').click(function () {
            var selectedIds = $('.snipe-table').bootstrapTable('getSelections');
            $.each(selectedIds, function(key,value) {
                $( "#bulkForm" ).append($('<input type="hidden" name="ids[' + value.id + ']" value="' + value.id + '">' ));
            });

        });
    }

    function initializeBootstrapTableSearchUi() {

        // This handles the search box highlighting on both ajax and client-side
        // bootstrap tables
        var searchboxHighlighter = function (event) {

            $('.search-input').each(function (index, element) {

                if ($(element).val() != '') {
                    $(element).addClass('search-highlight');
                    $(element).next().children().addClass('search-highlight');
                } else {
                    $(element).removeClass('search-highlight');
                    $(element).next().children().removeClass('search-highlight');
                }
            });
        };

        $("[name='clearSearch']").click(function () {

            // This hacks around a stupid issue in BS tables where the search text would get remembered for way too long even after it was cleared
            for (storedSearch in localStorage) {
                if (storedSearch.endsWith('.bs.table.searchText')) {
                    localStorage.removeItem(storedSearch);
                }
            }

            $('.search-input').each(function (index, element) {
                $(element).val('');
            });
        });

        $('.search button[name=clearSearch]').click(searchboxHighlighter);
        searchboxHighlighter({ name:'pageload'});
        $('.search-input').keyup(searchboxHighlighter);

        //  This is necessary to make the bootstrap tooltips work inside of the
        // wenzhixin/bootstrap-table formatters. The measurement handlers
        // (post-body + shown.bs.tab) are registered at script parse time
        // below, outside this ready wrapper, so they're active before
        // snipeit.js's URL-hash-driven .tab('show') fires. This tooltip
        // hook can stay in the ready wrapper because it doesn't depend on
        // handler-timing.
        $(document).on('post-body.bs.table', '.snipe-table', function () {
            $('[data-tooltip="true"]').tooltip({
                container: 'body'
            });
        });
    }

    // -----------------------------------------------------------------
    // Sticky-column offsets and top-scrollbar mirror.
    //
    // Both function definitions AND both delegated handlers below are
    // deliberately at script parse time (outside the $(function () { })
    // wrapper). Reason: snipeit.js's URL-hash-to-tab logic
    // (assets/js/snipeit.js) calls .tab('show') from its own
    // document.ready, which fires 'shown.bs.tab' synchronously. If our
    // handler is registered inside a later document.ready callback, we
    // miss that first firing and the top-scrollbar's inner width stays at
    // whatever bootstrap-table measured while the tab was still
    // display:none (usually 0). Same issue for post-body.bs.table if
    // bootstrap-table's own init fires it before our ready runs. Parsing
    // these attachments at top level means they're subscribed before any
    // document.ready callback runs anywhere.
    // -----------------------------------------------------------------

    // Tables that carry a snipe-table--sticky-right-N or -left-N class
    // pin those columns via position:sticky. Each pinned column needs a
    // right/left offset equal to the cumulative outerWidth of the
    // pinned columns outside it, otherwise they all stack at the edge.
    // The offsets are per-column and can change on column-toggle and
    // window resize, so recompute after every render + resize.
    function updateStickyColumnOffsets(root) {
        var $targets = root ? $(root).filter('.snipe-table') : $('.snipe-table');
        $targets.each(function () {
            var el = this;
            var $t = $(this);
            var cls = el.className;
            var $ths = $t.find('> thead > tr').first().children('th');
            var count = $ths.length;

            var mR = /\bsnipe-table--sticky-right-(\d+)\b/.exec(cls);
            if (mR) {
                var nR = Math.min(parseInt(mR[1], 10), count);
                var offR = 0;
                for (var i = 1; i <= nR; i++) {
                    el.style.setProperty('--sticky-right-offset-' + i, offR + 'px');
                    offR += $ths.eq(count - i).outerWidth() || 0;
                }
            }

            var mL = /\bsnipe-table--sticky-left-(\d+)\b/.exec(cls);
            if (mL) {
                var nL = Math.min(parseInt(mL[1], 10), count);
                var offL = 0;
                for (var j = 1; j <= nL; j++) {
                    el.style.setProperty('--sticky-left-offset-' + j, offL + 'px');
                    offL += $ths.eq(j - 1).outerWidth() || 0;
                }
            }
        });
    }

    // Second horizontal scrollbar mirrored above the table so users don't
    // have to scroll down first to find a way to scroll right on wide
    // tables. Bootstrap-table doesn't ship this; we mirror the native
    // scrollbar of .fixed-table-body via a slim spacer div whose width
    // tracks the underlying table's scrollWidth. Only rendered when the
    // table actually overflows horizontally, so tables that fit in their
    // container get no extra chrome.
    function updateTopScrollbar(root) {
        var $targets = root ? $(root).filter('.snipe-table') : $('.snipe-table');
        // Track which outer .bootstrap-table wrappers have already been
        // processed this pass. Bootstrap-table's fixed-columns extension
        // (and some other add-ons) clone the table into extra inner
        // wrappers inside a single .bootstrap-table container. Iterating
        // .snipe-table naively then produced one top scrollbar per clone
        // stacked above the same table, and none of them tracked the
        // primary .fixed-table-body's actual scroll width — visible on
        // /hardware and /locations as two mis-sized top scrollbars.
        var processedWrappers = [];
        $targets.each(function () {
            var tbl = this;
            var $body = $(tbl).closest('.fixed-table-body');
            if (! $body.length) return;
            var $btWrapper = $body.closest('.bootstrap-table');
            if (! $btWrapper.length) return;

            var wrapperEl = $btWrapper[0];
            if (processedWrappers.indexOf(wrapperEl) !== -1) return;
            processedWrappers.push(wrapperEl);

            // Always mirror the PRIMARY .fixed-table-body (the first one
            // inside the outer .bootstrap-table wrapper). Extension clones
            // have their own .fixed-table-body but tracking any of them
            // would produce a scrollbar that only spans the pinned
            // columns' width, not the full table.
            var $primaryContainer = $btWrapper.children('.fixed-table-container').first();
            if (! $primaryContainer.length) return;
            var $primaryBody = $primaryContainer.find('.fixed-table-body').first();
            if (! $primaryBody.length) return;
            var primaryBody = $primaryBody[0];
            var $primaryTable = $primaryBody.find('table.snipe-table').first();
            if (! $primaryTable.length) return;
            var primaryTable = $primaryTable[0];

            // Clean up any scrollbar left inside .fixed-table-container
            // from an older layout. Current placement is as a direct
            // child of .bootstrap-table (see the sticky-context comment
            // below); orphan strays here would be invisible when the
            // page scrolls (sticky broken by the container's
            // overflow: hidden) and would double-render with the new
            // one on wide tables.
            $primaryContainer.children('.snipe-top-scrollbar').remove();

            // Fixed-height tables (data-height, e.g. dashboard widgets)
            // already show their bottom scrollbar within the box they
            // live in, so the top scrollbar adds noise without benefit.
            if ($(tbl).is('[data-height]')) {
                $btWrapper.children('.snipe-top-scrollbar').remove();
                return;
            }

            var overflows = primaryTable.scrollWidth > primaryBody.clientWidth;
            // Look up an existing scrollbar as a direct child of the
            // .bootstrap-table wrapper (NOT nested inside
            // .fixed-table-container). Nesting inside the container
            // used to make sense visually - the scrollbar sat right
            // above .fixed-table-body - but .fixed-table-container has
            // overflow: hidden as a fixed-column overlay clip, which
            // makes it the sticky scroll context. Sticky inside an
            // overflow:hidden ancestor won't engage against the page
            // scroll, so scrolling the page just carries the scrollbar
            // off-screen with the container. Living as a direct child
            // of .bootstrap-table (overflow: visible) lets sticky bind
            // to the outer page scroll and stay pinned at viewport top
            // when the user scrolls past the natural table position
            // (#19484 follow-up: bleed-through above sticky-clone thead).
            var $topScroll = $btWrapper.children('.snipe-top-scrollbar');

            // Sort re-renders can measure momentarily-wrong widths while
            // bootstrap-table is settling the sticky-header clone and
            // column widths — .remove()-ing the scrollbar during that
            // window and then relying on a later post-body event to
            // recreate it was fragile (#19484: horizontal scrollbar
            // silently vanishes after a sort). Hide the element instead
            // when the table currently fits, so the same node survives
            // and can be re-shown as soon as an overflowing state is
            // observed again on the next event tick or scroll.
            if (! overflows) {
                $topScroll.hide();
                return;
            }

            if (! $topScroll.length) {
                $topScroll = $('<div class="snipe-top-scrollbar" aria-hidden="true"><div class="snipe-top-scrollbar-inner"></div></div>');
                $primaryContainer.before($topScroll);
            } else {
                $topScroll.show();
            }

            // Rebind scroll sync every time. The top scrollbar element
            // persists across bootstrap-table renders, but .fixed-table-body
            // is replaced on every post-body, so any handler we attached to
            // the previous body is gone. Namespaced .off() clears whatever
            // we may have attached before; .on() reattaches.
            var top = $topScroll[0];
            var syncing = false;
            $topScroll.off('scroll.snipeScrollSync').on('scroll.snipeScrollSync', function () {
                if (syncing) return;
                syncing = true;
                primaryBody.scrollLeft = top.scrollLeft;
                syncing = false;
            });
            $primaryBody.off('scroll.snipeScrollSync').on('scroll.snipeScrollSync', function () {
                if (syncing) return;
                syncing = true;
                top.scrollLeft = primaryBody.scrollLeft;
                syncing = false;
            });

            $topScroll.children('.snipe-top-scrollbar-inner').css('width', primaryTable.scrollWidth + 'px');

            // Re-evaluate pinning immediately so a table that was
            // scrolled past before this render (e.g. the user sorted
            // while scrolled down) gets the fixed positioning
            // recomputed against the new column widths.
            pinTopScrollbarIfScrolled($topScroll, $primaryContainer);
        });
    }

    // Toggle the top scrollbar between natural flow and position:
    // fixed at viewport top when the user has scrolled past the
    // .fixed-table-container's start. Would ideally be position:
    // sticky (declarative, no JS scroll handler) but sticky did not
    // engage reliably against page scroll from inside the
    // .bootstrap-table's ancestor chain. #19484 follow-up: the
    // scrollbar was invisible when the sticky-clone thead activated,
    // and the offset gap opened by stickyHeaderOffsetY was bleeding
    // through tbody rows behind it.
    function pinTopScrollbarIfScrolled($topScroll, $primaryContainer) {
        if (! $topScroll.length || ! $primaryContainer.length) return;
        if ($topScroll.css('display') === 'none') return;

        var containerRect = $primaryContainer[0].getBoundingClientRect();
        var scrollbarHeight = $topScroll.outerHeight();
        // Pin as soon as the top of .fixed-table-container would go
        // above the space we'd reserve for the pinned scrollbar (so
        // the pin engages just before the natural scrollbar would
        // scroll off-screen). Unpin when the container top comes
        // back into view.
        var shouldPin = containerRect.top < 0;
        // Also unpin when the entire container has scrolled off the
        // bottom of the viewport - no point pinning a scrollbar for
        // a table that's not on screen.
        if (containerRect.bottom < scrollbarHeight) {
            shouldPin = false;
        }

        if (shouldPin) {
            $topScroll.addClass('is-pinned').css({
                position: 'fixed',
                top: 0,
                left: Math.round(containerRect.left) + 'px',
                width: Math.round(containerRect.width) + 'px'
            });
        } else if ($topScroll.hasClass('is-pinned')) {
            $topScroll.removeClass('is-pinned').css({
                position: '',
                top: '',
                left: '',
                width: ''
            });
        }
    }

    // Global scroll listener that repositions every visible top
    // scrollbar mirror. Single listener over all tables rather than
    // one per mirror so the handler count stays constant and cheap
    // even on pages with many tables. rAF-throttled to keep the
    // scroll path smooth.
    var pinRafPending = false;
    function schedulePinRecompute() {
        if (pinRafPending) return;
        pinRafPending = true;
        var raf = window.requestAnimationFrame || function (cb) { return window.setTimeout(cb, 16); };
        raf(function () {
            pinRafPending = false;
            $('.snipe-top-scrollbar').each(function () {
                var $ts = $(this);
                var $btWrap = $ts.parent('.bootstrap-table');
                if (! $btWrap.length) return;
                var $pc = $btWrap.children('.fixed-table-container').first();
                pinTopScrollbarIfScrolled($ts, $pc);
            });
        });
    }
    $(window).on('scroll.snipeTopScrollbarPin resize.snipeTopScrollbarPin', schedulePinRecompute);

    // Helper: run the given callback after enough layout has settled that
    // clientWidth / scrollWidth reads on newly-visible tables are stable.
    //
    // Uses two nested requestAnimationFrame calls (fires two frames later)
    // plus a setTimeout fallback for the same tick, so we're robust
    // against both:
    //   - browsers where RAF fires before the paint that finalizes layout
    //     of a just-un-hidden pane, and
    //   - the resetView path in snipeit.js that runs on the same tick as
    //     shown.bs.tab and adjusts column widths after us.
    function deferAfterLayout(fn) {
        var raf = window.requestAnimationFrame || function (cb) { return window.setTimeout(cb, 0); };
        raf(function () {
            raf(function () {
                fn();
            });
        });
        window.setTimeout(fn, 120);
    }

    // Re-measure after every bootstrap-table render. Delegated on document
    // so it catches tables that init after this handler was attached.
    //
    // Includes a second re-check ~250ms later specifically for the top
    // scrollbar. Sort re-renders (and stickyHeader clone width sync)
    // can leave scrollWidth reading briefly equal to clientWidth on the
    // first deferAfterLayout tick, which would hide the mirror even
    // though the table actually overflows once layout settles. The
    // delayed re-check catches that transient state without needing
    // a full-blown ResizeObserver (#19484).
    $(document).on('post-body.bs.table', '.snipe-table', function () {
        var tbl = this;
        deferAfterLayout(function () {
            updateStickyColumnOffsets(tbl);
            updateTopScrollbar(tbl);
        });
        window.setTimeout(function () {
            updateTopScrollbar(tbl);
        }, 250);
    });

    // Re-measure when a tab becomes visible. Bootstrap 3 renders inactive
    // .tab-pane elements with display:none, so any bootstrap-table that was
    // rendered inside a hidden tab measured its container width as 0 at
    // post-body time. shown.bs.tab fires on the tab trigger after the pane
    // has been made visible; a zero-arg call re-measures all snipe-tables
    // on the page.
    //
    // Also listen for reset-view.bs.table, which bootstrap-table fires
    // when snipeit.js calls `.bootstrapTable('resetView')` from its own
    // shown.bs.tab handler (that path recomputes column widths after us
    // and can leave scrollWidth stale if we measured on the same tick).
    $(document).on('shown.bs.tab', function () {
        deferAfterLayout(function () {
            updateStickyColumnOffsets();
            updateTopScrollbar();
        });
    });

    $(document).on('reset-view.bs.table', '.snipe-table', function () {
        var tbl = this;
        deferAfterLayout(function () {
            updateStickyColumnOffsets(tbl);
            updateTopScrollbar(tbl);
        });
    });

    $(window).on('resize', function () {
        updateStickyColumnOffsets();
        updateTopScrollbar();
    });

</script>
    
@endpush
