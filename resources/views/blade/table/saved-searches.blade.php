@props([
    'tableId',
    'model' => null,
])

<div id="{{ $tableId }}SavedSearchesModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="{{ $tableId }}SavedSearchesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('general.cancel') }}">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="{{ $tableId }}SavedSearchesModalLabel">{{ trans('general.save_search_as') }}</h4>
            </div>
            <div class="modal-body">
                <form class="js-saved-searches-save-form">
                    <div class="form-group">
                        <label for="{{ $tableId }}SavedSearchesName" class="control-label">{{ trans('general.name') }}</label>
                        <input type="text" id="{{ $tableId }}SavedSearchesName" class="form-control js-saved-searches-name" maxlength="191" required>
                    </div>
                    <div class="js-saved-searches-save-error alert alert-danger" style="display:none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                <button type="button" class="btn btn-primary js-saved-searches-save-submit">{{ trans('general.save') }}</button>
            </div>
        </div>
    </div>
</div>

<script nonce="{{ csrf_token() }}">
    (function () {
        var tableId = @json($tableId);
        var modelClass = @json($model);
        var baseUrl = $('meta[name="baseUrl"]').attr('content') || '/';
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        var apiHeaders = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        };

        var $saveModal = $('#' + tableId + 'SavedSearchesModal');
        var $saveModalTitle = $saveModal.find('.modal-title');
        var saveTitleText = @json(trans('general.save_search_as'));
        var renameTitleText = @json(trans('general.rename_search'));

        // id of the saved search currently being renamed, or null
        // when the modal is open in save-current mode. The submit
        // handler branches on this: null → POST, otherwise PUT.
        // Reset to null at every open so a prior rename does not
        // leak into the next save-current flow.
        var editingId = null;

        // Cache of the user's saved rows for this table, kept in
        // sync with the server by loadList(). Used by the dedup
        // check so the Save flow can tell the user "this is already
        // saved as X" without a server round-trip. Mirrors what the
        // backend uniqueness rule would reject with a 422, but the
        // frontend check is nicer UX (surface before they type a
        // name).
        var cachedRows = [];

        // Sort params so `?filter[a]=1&filter[b]=2` compares equal to
        // `?filter[b]=2&filter[a]=1`. The capture function appends
        // keys in object-iteration order which is NOT stable across
        // browsers / JS engines, so we normalize at compare time
        // rather than relying on capture-time ordering.
        function normalizeQueryString(qs) {
            var entries = [];
            new URLSearchParams(qs || '').forEach(function (v, k) {
                entries.push([k, v]);
            });
            entries.sort(function (a, b) {
                if (a[0] !== b[0]) {
                    return a[0] < b[0] ? -1 : 1;
                }
                return a[1] < b[1] ? -1 : (a[1] > b[1] ? 1 : 0);
            });
            var out = new URLSearchParams();
            entries.forEach(function (e) {
                out.append(e[0], e[1]);
            });
            return out.toString();
        }

        // Returns the matching row if the current filter state is
        // already saved under some name, or null otherwise.
        function findDuplicateRow() {
            var current = normalizeQueryString(captureCurrentFilterState());
            if (current === '') {
                return null;
            }
            for (var i = 0; i < cachedRows.length; i++) {
                if (normalizeQueryString(cachedRows[i].query_string || '') === current) {
                    return cachedRows[i];
                }
            }
            return null;
        }

        // Captures the current filter state as a URL query string so
        // loading the saved search redirects back to the same list
        // with the stored filters re-attached. Pulls directly from the
        // bs-table instance's filterColumnsPartial / operator so it
        // reflects what the user actually has applied right now (not
        // what the modal form shows, which can lag behind). Sort order
        // is captured from URL since bs-table surfaces it there via
        // the patched updateHistoryState. Pagination keys are
        // deliberately dropped so loading does not land on page N.
        function captureCurrentFilterState() {
            var params = new URLSearchParams();
            var bsTable = $('#' + tableId).data('bootstrap.table');
            if (bsTable && bsTable.filterColumnsPartial) {
                Object.keys(bsTable.filterColumnsPartial).forEach(function (k) {
                    var v = bsTable.filterColumnsPartial[k];
                    if (v !== undefined && v !== null && String(v) !== '') {
                        params.append('filter[' + k + ']', String(v));
                    }
                });
            }
            if (bsTable && typeof bsTable.getAdvancedSearchOperator === 'function') {
                var op = bsTable.getAdvancedSearchOperator();
                if (op === 'or') {
                    params.append('filter_operator', 'or');
                }
            }
            // Carry sort + order over so a saved search preserves the
            // view the user built. Pagination is intentionally dropped.
            var current = new URLSearchParams(window.location.search);
            ['sort', 'order', 'status'].forEach(function (k) {
                if (current.has(k)) {
                    params.append(k, current.get(k));
                }
            });

            return params.toString();
        }

        // Build the dropdown DOM once per page load. Appended into
        // the same btn-group as the Advanced Search button so the
        // visual pack-join (no inter-button gap, blue fill) comes
        // from Bootstrap's own btn-group styling. Button class is
        // cloned from the sibling so our bookmark matches whatever
        // theme / state class the toolbar is wearing (primary,
        // warning when filters active, etc.).
        function buildDropdown($advBtn) {
            var saveLabel = @json(trans('general.save_search_as'));
            var manageLabel = @json(trans('general.saved_searches'));
            var siblingClass = ($advBtn.attr('class') || 'btn btn-default').replace(/\bbtn-advanced-search\b/g, '')
            // Strip `active` so our bookmark does not inherit
            // the Advanced Search button's "filters are applied
            // here" pressed-state class. That cue belongs on the
            // search button because filter state IS a property
            // of the advanced search. The bookmark is a static
            // UI control for managing saved presets, with no
            // active / inactive mode of its own.
            .replace(/\bactive\b/g, '').trim();
            // Toggle button starts hidden so the FOUC window between
            // DOM-inject and the first updateVisibility() does not
            // flash a save-button for a user who has neither filters
            // nor saved searches. The MENU is managed by Bootstrap's
            // dropdown JS (display:block = open) so we never
            // force-show it from here. The menu is harmless when
            // its toggle button is display:none because there is no
            // way for the user to open it.
            var $toggle = $('<button type="button" class="' + siblingClass + ' dropdown-toggle js-saved-searches-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="' + manageLabel + '" aria-label="' + manageLabel + '" style="display:none;"><i class="fa fa-bookmark" aria-hidden="true"></i> <span class="caret"></span></button>');
            var $menu = $('<ul class="dropdown-menu dropdown-menu-right js-saved-searches-menu" role="menu"></ul>');
            $menu.append('<li><a href="javascript:void(0)" class="js-saved-searches-save-trigger"><i class="fa fa-plus" aria-hidden="true"></i> ' + saveLabel + '</a></li>');
            return $toggle.add($menu);
        }

        // Each row renders the saved search name as a link that loads
        // the preset, with a delete icon on the right. Trash icon
        // click fires a DELETE and strips the row from the menu
        // without a full redraw.
        function appendRows($menu, rows) {
            var emptyLabel = @json(trans('general.no_saved_searches'));
            var confirmLabel = @json(trans('general.sure_to_delete'));
            var renameLabel = @json(trans('general.rename_search'));
            var $divider = $('<li class="divider js-saved-searches-divider" role="separator"></li>');
            $menu.find('.js-saved-searches-row, .js-saved-searches-divider, .js-saved-searches-empty').remove();
            $menu.append($divider);
            if (!rows || rows.length === 0) {
                $menu.append('<li class="js-saved-searches-empty"><span style="display:block;padding:3px 20px;color:#aaa;font-style:italic;">' + emptyLabel + '</span></li>');
                return;
            }
            rows.forEach(function (row) {
                var escapedName = $('<div>').text(row.name).html();
                var escapedRawName = $('<div>').text(row.name).html();
                var $row = $(
                    '<li class="js-saved-searches-row" data-id="' + row.id + '" data-name="' + escapedRawName + '" data-query="' + $('<div>').text(row.query_string || '').html() + '">' +
                    '<a href="javascript:void(0)" class="js-saved-searches-load" style="padding-right:68px;position:relative;">' + escapedName +
                    '<i class="fa fa-pencil js-saved-searches-rename" title="' + renameLabel + '" aria-label="' + renameLabel + '" style="position:absolute;right:36px;top:50%;transform:translateY(-50%);color:#fff;"></i>' +
                    '<i class="fa fa-trash js-saved-searches-delete" title="' + confirmLabel + '" aria-label="' + confirmLabel + '" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#fff;"></i>' +
                    '</a></li>',
                );
                $menu.append($row);
            });
        }

        function loadList() {
            $.ajax({
                url: baseUrl + 'api/v1/saved-searches',
                method: 'GET',
                dataType: 'json',
                headers: apiHeaders,
                data: {table_name: tableId},
            }).done(function (response) {
                var rows = (response && response.rows) ? response.rows : [];
                cachedRows = rows;
                appendRows($('.js-saved-searches-menu'), rows);
                updateVisibility();
            });
        }

        // Mount the dropdown once the toolbar is in the DOM. Snipe-IT's
        // bs-table partial defers its own button-decoration path by
        // 50ms (line ~1440) because bootstrap-table builds the toolbar
        // asynchronously. We hook the same post-load phase so our
        // button lands next to the Advanced Search button after the
        // toolbar settles. Toggle + menu are inserted directly after
        // the advanced-search button so they share the same
        // btn-group parent and inherit its visual pack-join.
        function mountDropdown() {
            var bsTable = $('#' + tableId).data('bootstrap.table');
            if (!bsTable || typeof bsTable.getAdvancedSearchButton !== 'function') {
                return;
            }
            var $advBtn = bsTable.getAdvancedSearchButton();
            if (!$advBtn.length) {
                return;
            }
            if ($advBtn.siblings('.js-saved-searches-toggle').length) {
                return;
            }
            var $parts = buildDropdown($advBtn);
            $advBtn.after($parts);
            // Deeplinked / restored state can arrive with
            // filterColumnsPartial already populated before loadList()
            // returns. Run visibility now so the button appears
            // immediately for those cases rather than waiting on the
            // API round-trip.
            updateVisibility();
            loadList();
        }

        // Count non-empty entries in filterColumnsPartial so the
        // "Save current" affordance can hide when there are no
        // filters to save. The dropdown ITSELF hides when both
        // filters AND saved-searches are empty (nothing to do here).
        function currentFilterCount() {
            var bsTable = $('#' + tableId).data('bootstrap.table');
            if (!bsTable || !bsTable.filterColumnsPartial) {
                return 0;
            }
            var n = 0;
            Object.keys(bsTable.filterColumnsPartial).forEach(function (k) {
                var v = bsTable.filterColumnsPartial[k];
                if (v !== undefined && v !== null && String(v) !== '') {
                    n++;
                }
            });
            return n;
        }

        function updateVisibility() {
            var hasFilters = currentFilterCount() > 0;
            var hasSaved = $('.js-saved-searches-menu .js-saved-searches-row').length > 0;
            var showToggle = hasFilters || hasSaved;
            // Toggle the BUTTON only. The dropdown menu's open/close
            // state is Bootstrap's business via the data-toggle=
            // dropdown wiring, not ours. Forcing display:block on a
            // .dropdown-menu is literally what Bootstrap does to
            // open the dropdown, so .toggle(true) here would make
            // the menu auto-open on page load, which is exactly the
            // bug this comment is here to prevent.
            $('.js-saved-searches-toggle').toggle(showToggle);
            // Items inside the menu are safe to show/hide. The
            // Save-Current entry (and its divider) only make sense
            // when there is actually a filter state to capture.
            $('.js-saved-searches-menu .js-saved-searches-save-trigger').closest('li').toggle(hasFilters);
            $('.js-saved-searches-menu .js-saved-searches-divider').toggle(hasFilters && hasSaved);
        }

        $(function () {
            // Chase the same deferred-mount window Snipe-IT's bs-table
            // partial uses for other post-toolbar decorations.
            setTimeout(mountDropdown, 100);

            // Re-evaluate visibility after every filter apply /
            // clear / cancel so the toggle appears the moment a user
            // picks a filter and disappears when they wipe them.
            $('#' + tableId).on('column-advanced-search.bs.table load-success.bs.table', function () {
                updateVisibility();
            });
        });

        // Delegated click handlers survive the dropdown being torn
        // down and rebuilt on refresh.
        $(document).on('click', '.js-saved-searches-save-trigger', function () {
            editingId = null;
            $saveModalTitle.text(saveTitleText);
            $saveModal.find('.js-saved-searches-name').val('');
            var $err = $saveModal.find('.js-saved-searches-save-error');
            $err.hide().text('');
            $saveModal.find('.js-saved-searches-save-submit').prop('disabled', false);

            // Surface the dedup result on open so a user who clicked
            // Save for a filter state they already have as a preset
            // sees it immediately without needing to type a name
            // first. Submit button is disabled in this state because
            // the backend rule would reject the POST anyway.
            var dupe = findDuplicateRow();
            if (dupe) {
                $err.text(@json(trans('general.saved_search_duplicate')).replace(':name', dupe.name)
            ).
                show();
                $saveModal.find('.js-saved-searches-save-submit').prop('disabled', true);
            }

            $saveModal.one('shown.bs.modal', function () {
                $saveModal.find('.js-saved-searches-name').trigger('focus');
            });
            $saveModal.modal('show');
        });

        // Rename flow: pre-populate the modal with the row's current
        // name and flip editingId so the submit handler issues PUT
        // instead of POST. Dedup check skipped because the backend
        // uniqueness rule injects the current record's id as the
        // except-id slot, so re-saving under the same name is a
        // no-op rather than a 422.
        $(document).on('click', '.js-saved-searches-rename', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $row = $(this).closest('.js-saved-searches-row');
            editingId = $row.attr('data-id');
            $saveModalTitle.text(renameTitleText);
            $saveModal.find('.js-saved-searches-name').val($row.attr('data-name') || '');
            $saveModal.find('.js-saved-searches-save-error').hide().text('');
            $saveModal.find('.js-saved-searches-save-submit').prop('disabled', false);
            $saveModal.one('shown.bs.modal', function () {
                $saveModal.find('.js-saved-searches-name').trigger('focus').trigger('select');
            });
            $saveModal.modal('show');
        });

        $saveModal.on('click', '.js-saved-searches-save-submit', function () {
            var name = ($saveModal.find('.js-saved-searches-name').val() || '').trim();
            var $err = $saveModal.find('.js-saved-searches-save-error');
            $err.hide().text('');
            if (name === '') {
                $err.text(@json(trans('validation.required', ['attribute' => 'name']))).show();
                return;
            }

            var ajaxOk = function () {
                $saveModal.modal('hide');
                loadList();
            };
            var ajaxFail = function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.messages)
                    ? Object.values(xhr.responseJSON.messages).flat().join(' ')
                    : (xhr.responseText || xhr.statusText);
                $err.text(msg).show();
            };

            if (editingId) {
                // Rename path. Only name is updateable. query_string
                // and table_name are frozen on the server side.
                $.ajax({
                    url: baseUrl + 'api/v1/saved-searches/' + editingId,
                    method: 'PUT',
                    dataType: 'json',
                    headers: apiHeaders,
                    data: {name: name},
                }).done(ajaxOk).fail(ajaxFail);
                return;
            }

            // Save-current path.
            // Defense-in-depth check so a race between modal-open and
            // another tab creating the same preset still gets
            // intercepted here before hitting the backend.
            var dupe = findDuplicateRow();
            if (dupe) {
                $err.text(@json(trans('general.saved_search_duplicate')).replace(':name', dupe.name)
            ).
                show();
                return;
            }
            // Normalize before send so the stored string is in the
            // same canonical form findDuplicateRow() compares against.
            // Backend uniqueness rule reads the stored strings
            // literally.
            var queryString = normalizeQueryString(captureCurrentFilterState());
            $.ajax({
                url: baseUrl + 'api/v1/saved-searches',
                method: 'POST',
                dataType: 'json',
                headers: apiHeaders,
                data: {
                    table_name: tableId,
                    model: modelClass,
                    name: name,
                    query_string: queryString,
                },
            }).done(ajaxOk).fail(ajaxFail);
        });

        $saveModal.on('keydown', '.js-saved-searches-name', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $saveModal.find('.js-saved-searches-save-submit').click();
            }
        });

        $(document).on('click', '.js-saved-searches-load', function (e) {
            // Delete and rename icons bubble up to the link, so
            // intercept both before treating this as a load.
            if ($(e.target).closest('.js-saved-searches-delete, .js-saved-searches-rename').length) {
                e.preventDefault();
                return;
            }
            var $row = $(this).closest('.js-saved-searches-row');
            var query = $row.attr('data-query') || '';
            var sep = query === '' ? '' : '?';
            window.location.href = window.location.pathname + sep + query;
        });

        $(document).on('click', '.js-saved-searches-delete', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $row = $(this).closest('.js-saved-searches-row');
            var id = $row.attr('data-id');
            if (!id) {
                return;
            }
            $.ajax({
                url: baseUrl + 'api/v1/saved-searches/' + id,
                method: 'DELETE',
                dataType: 'json',
                headers: apiHeaders,
            }).done(function () {
                $row.remove();
                loadList();
            });
        });
    })();
</script>
