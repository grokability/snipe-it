/*!
 * dragtable
 *
 * @Version 2.0.15
 *
 * Copyright (c) 2010-2013, Andres akottr@gmail.com
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * Inspired by the the dragtable from Dan Vanderkam (danvk.org/dragtable/)
 * Thanks to the jquery and jqueryui comitters
 *
 * Any comment, bug report, feature-request is welcome
 * Feel free to contact me.
 */

/* TOKNOW:
 * For IE7 you need this css rule:
 * table {
 *   border-collapse: collapse;
 * }
 * Or take a clean reset.css (see http://meyerweb.com/eric/tools/css/reset/)
 */

/* TODO: investigate
 * Does not work properly with css rule:
 * html {
 *      overflow: -moz-scrollbars-vertical;
 *  }
 * Workaround:
 * Fixing Firefox issues by scrolling down the page
 * http://stackoverflow.com/questions/2451528/jquery-ui-sortable-scroll-helper-element-offset-firefox-issue
 *
 * var start = $.noop;
 * var beforeStop = $.noop;
 * if($.browser.mozilla) {
 * var start = function (event, ui) {
 *               if( ui.helper !== undefined )
 *                 ui.helper.css('position','absolute').css('margin-top', $(window).scrollTop() );
 *               }
 * var beforeStop = function (event, ui) {
 *              if( ui.offset !== undefined )
 *                ui.helper.css('margin-top', 0);
 *              }
 * }
 *
 * and pass this as start and stop function to the sortable initialisation
 * start: start,
 * beforeStop: beforeStop
 */
/*
 * Special thx to all pull requests comitters
 */

(function($) {
    $.widget("akottr.dragtable", {
        options: {
            revert: false,               // smooth revert
            dragHandle: '.table-handle', // handle for moving cols, if not exists the whole 'th' is the handle
            maxMovingRows: 40,           // 1 -> only header. 40 row should be enough, the rest is usually not in the viewport
            excludeFooter: false,        // excludes the footer row(s) while moving other columns. Make sense if there is a footer with a colspan. */
            onlyHeaderThreshold: 100,    // TODO:  not implemented yet, switch automatically between entire col moving / only header moving
            dragaccept: null,            // draggable cols -> default all
            persistState: null,          // url or function -> plug in your custom persistState function right here. function call is persistState(originalTable)
            restoreState: null,          // JSON-Object or function:  some kind of experimental aka Quick-Hack TODO: do it better
            exact: true,                 // removes pixels, so that the overlay table width fits exactly the original table width
            clickDelay: 10,              // ms to wait before rendering sortable list and delegating click event
            containment: null,           // @see http://api.jqueryui.com/sortable/#option-containment, use it if you want to move in 2 dimesnions (together with axis: null)
            cursor: 'move',              // @see http://api.jqueryui.com/sortable/#option-cursor
            cursorAt: false,             // @see http://api.jqueryui.com/sortable/#option-cursorAt
            distance: 0,                 // @see http://api.jqueryui.com/sortable/#option-distance, for immediate feedback use "0"
            tolerance: 'pointer',        // @see http://api.jqueryui.com/sortable/#option-tolerance
            axis: 'x',                   // @see http://api.jqueryui.com/sortable/#option-axis, Only vertical moving is allowed. Use 'x' or null. Use this in conjunction with the 'containment' setting
            beforeStart: $.noop,         // returning FALSE will stop the execution chain.
            beforeMoving: $.noop,
            beforeReorganize: $.noop,
            beforeStop: $.noop
        },
        originalTable: {
            el: null,
            selectedHandle: null,
            sortOrder: null,
            startIndex: 0,
            endIndex: 0
        },
        sortableTable: {
            el: $(),
            selectedHandle: $(),
            movingRow: $()
        },
        persistState: function() {
            var _this = this;
            this.originalTable.el.find('th').each(function(i) {
                if (this.id !== '') {
                    _this.originalTable.sortOrder[this.id] = i;
                }
            });
            $.ajax({
                url: this.options.persistState,
                data: this.originalTable.sortOrder
            });
        },
        /*
         * persistObj looks like
         * {'id1':'2','id3':'3','id2':'1'}
         * table looks like
         * |   id2  |   id1   |   id3   |
         */
        _restoreState: function(persistObj) {
            for (var n in persistObj) {
                this.originalTable.startIndex = $('#' + n).closest('th').prevAll().length + 1;
                this.originalTable.endIndex = parseInt(persistObj[n], 10) + 1;
                this._bubbleCols();
            }
        },
        // bubble the moved col left or right
        _bubbleCols: function() {
            var i, j, col1, col2;
            var from = this.originalTable.startIndex;
            var to = this.originalTable.endIndex;
            /* Find children thead and tbody.
             * Only to process the immediate tr-children. Bugfix for inner tables
             */
            var thtb = this.originalTable.el.children();
            if (this.options.excludeFooter) {
                thtb = thtb.not('tfoot');
            }
            if (from < to) {
                for (i = from; i < to; i++) {
                    col1 = thtb.find('> tr > td:nth-child(' + i + ')')
                        .add(thtb.find('> tr > th:nth-child(' + i + ')'));
                    col2 = thtb.find('> tr > td:nth-child(' + (i + 1) + ')')
                        .add(thtb.find('> tr > th:nth-child(' + (i + 1) + ')'));
                    for (j = 0; j < col1.length; j++) {
                        swapNodes(col1[j], col2[j]);
                    }
                }
            } else {
                for (i = from; i > to; i--) {
                    col1 = thtb.find('> tr > td:nth-child(' + i + ')')
                        .add(thtb.find('> tr > th:nth-child(' + i + ')'));
                    col2 = thtb.find('> tr > td:nth-child(' + (i - 1) + ')')
                        .add(thtb.find('> tr > th:nth-child(' + (i - 1) + ')'));
                    for (j = 0; j < col1.length; j++) {
                        swapNodes(col1[j], col2[j]);
                    }
                }
            }
        },
        _rearrangeTableBackroundProcessing: function() {
            var _this = this;
            return function() {
                _this._bubbleCols();
                _this.options.beforeStop(_this.originalTable);
                _this.sortableTable.el.remove();
                restoreTextSelection();
                // persist state if necessary
                if (_this.options.persistState !== null) {
                    $.isFunction(_this.options.persistState) ? _this.options.persistState(_this.originalTable) : _this.persistState();
                }
            };
        },
        _rearrangeTable: function() {
            var _this = this;
            return function() {
                // remove handler-class -> handler is now finished
                _this.originalTable.selectedHandle.removeClass('dragtable-handle-selected');
                // add disabled class -> reorgorganisation starts soon
                _this.sortableTable.el.sortable("disable");
                _this.sortableTable.el.addClass('dragtable-disabled');
                _this.options.beforeReorganize(_this.originalTable, _this.sortableTable);
                // do reorganisation asynchronous
                // for chrome a little bit more than 1 ms because we want to force a rerender
                _this.originalTable.endIndex = _this.sortableTable.movingRow.prevAll().length + 1;
                setTimeout(_this._rearrangeTableBackroundProcessing(), 50);
            };
        },
        /*
         * Disrupts the table. The original table stays the same.
         * But on a layer above the original table we are constructing a list (ul > li)
         * each li with a separate table representig a single col of the original table.
         */
        _generateSortable: function(e) {
            !e.cancelBubble && (e.cancelBubble = true);
            var _this = this;
            // table attributes
            var attrs = this.originalTable.el[0].attributes;
            var attrsString = '';
            for (var i = 0; i < attrs.length; i++) {
                if (attrs[i].nodeValue && attrs[i].nodeName != 'id' && attrs[i].nodeName != 'width') {
                    attrsString += attrs[i].nodeName + '="' + attrs[i].nodeValue + '" ';
                }
            }

            // row attributes
            var rowAttrsArr = [];
            //compute height, special handling for ie needed :-(
            var heightArr = [];
            this.originalTable.el.find('tr').slice(0, this.options.maxMovingRows).each(function(i, v) {
                // row attributes
                var attrs = this.attributes;
                var attrsString = "";
                for (var j = 0; j < attrs.length; j++) {
                    if (attrs[j].nodeValue && attrs[j].nodeName != 'id') {
                        attrsString += " " + attrs[j].nodeName + '="' + attrs[j].nodeValue + '"';
                    }
                }
                rowAttrsArr.push(attrsString);
                heightArr.push($(this).height());
            });

            // compute width, no special handling for ie needed :-)
            var widthArr = [];
            // compute total width, needed for not wrapping around after the screen ends (floating)
            var totalWidth = 0;
            /* Find children thead and tbody.
             * Only to process the immediate tr-children. Bugfix for inner tables
             */
            var thtb = _this.originalTable.el.children();
            if (this.options.excludeFooter) {
                thtb = thtb.not('tfoot');
            }
            thtb.find('> tr > th').each(function(i, v) {
                var w = $(this).is(':visible') ? $(this).outerWidth() : 0;
                widthArr.push(w);
                totalWidth += w;
            });
            if(_this.options.exact) {
                var difference = totalWidth - _this.originalTable.el.outerWidth();
                widthArr[0] -= difference;
            }
            // one extra px on right and left side
            totalWidth += 2

            var sortableHtml = '<ul class="dragtable-sortable" style="position:absolute; width:' + totalWidth + 'px;">';
            // assemble the needed html
            thtb.find('> tr > th').each(function(i, v) {
                var width_li = $(this).is(':visible') ? $(this).outerWidth() : 0;
                sortableHtml += '<li style="width:' + width_li + 'px;">';
                sortableHtml += '<table ' + attrsString + '>';
                var row = thtb.find('> tr > th:nth-child(' + (i + 1) + ')');
                if (_this.options.maxMovingRows > 1) {
                    row = row.add(thtb.find('> tr > td:nth-child(' + (i + 1) + ')').slice(0, _this.options.maxMovingRows - 1));
                }
                row.each(function(j) {
                    // TODO: May cause duplicate style-Attribute
                    var row_content = $(this).clone().wrap('<div></div>').parent().html();
                    if (row_content.toLowerCase().indexOf('<th') === 0) sortableHtml += "<thead>";
                    sortableHtml += '<tr ' + rowAttrsArr[j] + '" style="height:' + heightArr[j] + 'px;">';
                    sortableHtml += row_content;
                    if (row_content.toLowerCase().indexOf('<th') === 0) sortableHtml += "</thead>";
                    sortableHtml += '</tr>';
                });
                sortableHtml += '</table>';
                sortableHtml += '</li>';
            });
            sortableHtml += '</ul>';
            this.sortableTable.el = this.originalTable.el.before(sortableHtml).prev();
            // set width if necessary
            this.sortableTable.el.find('> li > table').each(function(i, v) {
                $(this).css('width', widthArr[i] + 'px');
            });

            // assign this.sortableTable.selectedHandle
            this.sortableTable.selectedHandle = this.sortableTable.el.find('th .dragtable-handle-selected');

            var items = !this.options.dragaccept ? 'li' : 'li:has(' + this.options.dragaccept + ')';
            this.sortableTable.el.sortable({
                items: items,
                stop: this._rearrangeTable(),
                // pass thru options for sortable widget
                revert: this.options.revert,
                tolerance: this.options.tolerance,
                containment: this.options.containment,
                cursor: this.options.cursor,
                cursorAt: this.options.cursorAt,
                distance: this.options.distance,
                axis: this.options.axis
            });

            // assign start index
            this.originalTable.startIndex = $(e.target).closest('th').prevAll().length + 1;

            this.options.beforeMoving(this.originalTable, this.sortableTable);
            // Start moving by delegating the original event to the new sortable table
            this.sortableTable.movingRow = this.sortableTable.el.find('> li:nth-child(' + this.originalTable.startIndex + ')');

            // prevent the user from drag selecting "highlighting" surrounding page elements
            disableTextSelection();
            // clone the initial event and trigger the sort with it
            this.sortableTable.movingRow.trigger($.extend($.Event(e.type), {
                which: 1,
                clientX: e.clientX,
                clientY: e.clientY,
                pageX: e.pageX,
                pageY: e.pageY,
                screenX: e.screenX,
                screenY: e.screenY
            }));

            // Some inner divs to deliver the posibillity to style the placeholder more sophisticated
            var placeholder = this.sortableTable.el.find('.ui-sortable-placeholder');
            if(!placeholder.height()  <= 0) {
                placeholder.css('height', this.sortableTable.el.find('.ui-sortable-helper').height());
            }

            placeholder.html('<div class="outer" style="height:100%;"><div class="inner" style="height:100%;"></div></div>');
        },
        bindTo: {},
        _create: function() {
            this.originalTable = {
                el: this.element,
                selectedHandle: $(),
                sortOrder: {},
                startIndex: 0,
                endIndex: 0
            };
            // bind draggable to 'th' by default
            this.bindTo = this.originalTable.el.find('th');
            // filter only the cols that are accepted
            if (this.options.dragaccept) {
                this.bindTo = this.bindTo.filter(this.options.dragaccept);
            }
            // bind draggable to handle if exists
            if (this.bindTo.find(this.options.dragHandle).length > 0) {
                this.bindTo = this.bindTo.find(this.options.dragHandle);
            }
            // restore state if necessary
            if (this.options.restoreState !== null) {
                $.isFunction(this.options.restoreState) ? this.options.restoreState(this.originalTable) : this._restoreState(this.options.restoreState);
            }
            var _this = this;
            this.bindTo.mousedown(function(evt) {
                // listen only to left mouse click
                if(evt.which!==1) return;
                if (_this.options.beforeStart(_this.originalTable) === false) {
                    return;
                }
                clearTimeout(this.downTimer);
                this.downTimer = setTimeout(function() {
                    _this.originalTable.selectedHandle = $(this);
                    _this.originalTable.selectedHandle.addClass('dragtable-handle-selected');
                    _this._generateSortable(evt);
                }, _this.options.clickDelay);
            }).mouseup(function(evt) {
                clearTimeout(this.downTimer);
            });
        },
        redraw: function(){
            this.destroy();
            this._create();
        },
        destroy: function() {
            this.bindTo.unbind('mousedown');
            $.Widget.prototype.destroy.apply(this, arguments); // default destroy
            // now do other stuff particular to this widget
        }
    });

    /** closure-scoped "private" functions **/

    var body_onselectstart_save = $(document.body).attr('onselectstart'),
        body_unselectable_save = $(document.body).attr('unselectable');

    // css properties to disable user-select on the body tag by appending a <style> tag to the <head>
    // remove any current document selections

    function disableTextSelection() {
        // jQuery doesn't support the element.text attribute in MSIE 8
        // http://stackoverflow.com/questions/2692770/style-style-textcss-appendtohead-does-not-work-in-ie
        var $style = $('<style id="__dragtable_disable_text_selection__" type="text/css">body { -ms-user-select:none;-moz-user-select:-moz-none;-khtml-user-select:none;-webkit-user-select:none;user-select:none; }</style>');
        $(document.head).append($style);
        $(document.body).attr('onselectstart', 'return false;').attr('unselectable', 'on');
        if (window.getSelection) {
            window.getSelection().removeAllRanges();
        } else {
            document.selection.empty(); // MSIE http://msdn.microsoft.com/en-us/library/ms535869%28v=VS.85%29.aspx
        }
    }

    // remove the <style> tag, and restore the original <body> onselectstart attribute

    function restoreTextSelection() {
        $('#__dragtable_disable_text_selection__').remove();
        if (body_onselectstart_save) {
            $(document.body).attr('onselectstart', body_onselectstart_save);
        } else {
            $(document.body).removeAttr('onselectstart');
        }
        if (body_unselectable_save) {
            $(document.body).attr('unselectable', body_unselectable_save);
        } else {
            $(document.body).removeAttr('unselectable');
        }
    }

    function swapNodes(a, b) {
        var aparent = a.parentNode;
        var asibling = a.nextSibling === b ? a : a.nextSibling;
        b.parentNode.insertBefore(a, b);
        aparent.insertBefore(b, asibling);
    }
})(jQuery);