/**
 * orders_inline.js - Modifica inline quantità e prezzi con AJAX
 * Compatibile jQuery 3.x  |  PHP 5.5  |  Fallback: href standard
 */
$(document).ready(function () {

    /* ------------------------------------------------------------------ */
    /* Helpers                                                              */
    /* ------------------------------------------------------------------ */

    function getTabId() {
        try { return sessionStorage.getItem('mhr_tab_id') || ''; } catch (e) { return ''; }
    }

    function ajaxUpdate(params, onSuccess, onFail) {
        params.mhr_tab_id = getTabId();
        $.ajax({
            type:     'POST',
            url:      'orders_ajax.php',
            data:     params,
            dataType: 'json',
            success:  function (resp) {
                if (resp && resp.success) {
                    onSuccess(resp);
                } else if (onFail) {
                    onFail();
                }
            },
            error: function () { if (onFail) onFail(); }
        });
    }

    function updateTotal(total) {
        $('#mhr-table-total b').text(total);
    }

    // Valuta un'espressione matematica elementare (es. "4*12", "5-2", "7/12").
    // Accetta solo cifre, operatori + - * /, punto, virgola, parentesi e spazi.
    // Restituisce il risultato arrotondato a 2 decimali, oppure NaN se non valido.
    function evalPrice(raw) {
        var sanitized = raw.replace(',', '.').replace(/\s/g, '');
        if (!/^[\d\+\-\*\/\.\(\)]+$/.test(sanitized)) return NaN;
        try {
            var result = Function('"use strict"; return (' + sanitized + ')')();
            if (typeof result !== 'number' || !isFinite(result) || result < 0) return NaN;
            return Math.round(result * 100) / 100;
        } catch (e) { return NaN; }
    }

    /**
     * Aggiorna TUTTE le celle minus/cestino con lo stesso data-id
     * (es. "Ultima operazione" + tabella principale mostrano lo stesso ordine).
     */
    function syncAllById(id, newQty) {
        var $plusBtns  = $('a.mhr-btn-plus[data-id="'  + id + '"]');
        var $minusBtns = $('a.mhr-btn-minus[data-id="' + id + '"]');
        var $qtyCells  = $('td.mhr-qty-cell[data-id="' + id + '"]');

        /* Aggiorna data-qty su tutti i pulsanti + e – */
        $plusBtns.attr('data-qty',  newQty + 1);
        $minusBtns.attr('data-qty', newQty - 1);

        /* Aggiorna testo nelle celle quantità */
        $qtyCells.attr('data-qty', newQty).text(newQty);

        /* Leggi suspend/extra dal primo pulsante + (uguale ovunque) */
        var suspend = $plusBtns.first().attr('data-suspend') || '0';
        var extra   = $plusBtns.first().attr('data-extra')   || '0';

        /* Ricostruisci ogni cella minus/cestino */
        $('td.mhr-minus-cell[data-id="' + id + '"]').each(function () {
            var $cell     = $(this);
            var imgMinus  = $cell.attr('data-img-minus');
            var imgTrash  = $cell.attr('data-img-trash');
            var isCashier = $cell.attr('data-cashier') === '1';
            var name      = $cell.attr('data-name') || 'questo piatto';

            if (newQty > 1) {
                var minusQty  = newQty - 1;
                var minusLink = 'orders.php?command=update&data[quantity]=' + minusQty + '&data[id]=' + id;
                if (parseInt(suspend, 10)) minusLink += '&data[suspend]=1';
                if (parseInt(extra,   10)) minusLink += '&data[extra_care]=1';
                $cell.html(
                    '<a href="' + minusLink + '" class="mhr-btn-minus"' +
                    ' data-id="' + id + '" data-qty="' + minusQty + '"' +
                    ' data-suspend="' + suspend + '" data-extra="' + extra + '">' +
                    '<img src="' + imgMinus + '" border="0"></a>'
                );
            } else if (newQty === 1) {
                var deleteLink = 'orders.php?command=delete&data[id]=' + id;
                if (parseInt(suspend, 10)) deleteLink += '&data[suspend]=1';
                if (parseInt(extra,   10)) deleteLink += '&data[extra_care]=1';
                var confirmAttr = isCashier ? '' : ' onclick="return confirm(\'Vuoi eliminare ' + name + ' ?\')"';
                $cell.html(
                    '<a href="' + deleteLink + '"' + confirmAttr + '>' +
                    '<img src="' + imgTrash + '" border="0"></a>'
                );
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* Pulsanti + / -  (AJAX, fallback su href)                            */
    /* ------------------------------------------------------------------ */

    $(document).on('click', 'a.mhr-btn-plus, a.mhr-btn-minus', function (e) {
        e.preventDefault();
        var $btn    = $(this);
        var id      = $btn.attr('data-id');
        var newQty  = parseInt($btn.attr('data-qty'), 10);
        var suspend = $btn.attr('data-suspend') || '0';
        var extra   = $btn.attr('data-extra')   || '0';

        /* Quantità 0 = cancellazione: usa il fallback (page reload) */
        if (newQty <= 0) {
            window.location.href = $btn.attr('href').replace(/&amp;/g, '&');
            return;
        }

        ajaxUpdate(
            { command: 'update_qty', id: id, qty: newQty, suspend: suspend, extra_care: extra },
            function (resp) {
                syncAllById(id, resp.new_qty);
                updateTotal(resp.new_total);
            },
            function () {
                /* Fallback su errore AJAX */
                window.location.href = $btn.attr('href').replace(/&amp;/g, '&');
            }
        );
    });

    /* ------------------------------------------------------------------ */
    /* Modifica inline QUANTITÀ (doppio click sulla cella)                 */
    /* ------------------------------------------------------------------ */

    $(document).on('dblclick', 'td.mhr-qty-cell', function () {
        var $cell = $(this);
        if ($cell.find('input').length) return;

        var id         = $cell.attr('data-id');
        var currentQty = parseInt($cell.attr('data-qty'), 10);
        var $row       = $cell.closest('tr');
        var suspend    = $row.find('a.mhr-btn-plus').attr('data-suspend') || '0';
        var extra      = $row.find('a.mhr-btn-plus').attr('data-extra')   || '0';

        var $input = $('<input type="number" min="0" max="99">')
            .css({ width: '38px', textAlign: 'center', fontSize: 'inherit' })
            .val(currentQty);

        $cell.empty().append($input);
        $input.focus().select();

        function restore() { $cell.attr('data-qty', currentQty).text(currentQty); }

        function save() {
            var newQty = parseInt($input.val(), 10);
            if (isNaN(newQty) || newQty === currentQty) { restore(); return; }
            if (newQty <= 0) {
                window.location.href = 'orders.php?command=delete&data[id]=' + id;
                return;
            }
            ajaxUpdate(
                { command: 'update_qty', id: id, qty: newQty, suspend: suspend, extra_care: extra },
                function (resp) {
                    syncAllById(id, resp.new_qty);
                    updateTotal(resp.new_total);
                },
                restore
            );
        }

        $input.bind('keydown', function (e) {
            if (e.keyCode === 13) { $(this).unbind('blur'); save(); }
            if (e.keyCode === 27) { $(this).unbind('blur'); restore(); }
        }).bind('blur', save);
    });

    /* ------------------------------------------------------------------ */
    /* Modifica inline PREZZO (click sulla cella prezzo)                   */
    /* ------------------------------------------------------------------ */

    $(document).on('click', 'td.mhr-price-edit a', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $cell         = $(this).closest('td');
        if ($cell.find('input').length) return;

        var id            = $cell.attr('data-id');
        var currentPrice  = $cell.attr('data-price');
        var fallbackHref  = $(this).attr('href').replace(/&amp;/g, '&');

        var $input = $('<input type="text" inputmode="decimal">')
            .css({ width: '58px', textAlign: 'right', fontSize: 'inherit' })
            .val(currentPrice);

        $cell.empty().append($input);
        $input.focus().select();

        function restore() {
            $cell.html('<a href="' + fallbackHref + '">' + currentPrice + '</a>');
        }

        function save() {
            var raw      = $input.val().trim();
            var newPrice = evalPrice(raw);
            if (isNaN(newPrice)) { restore(); return; }
            raw = String(newPrice);
            if (raw === currentPrice) { restore(); return; }

            ajaxUpdate(
                { command: 'update_price', id: id, price: raw },
                function (resp) {
                    $cell.attr('data-price', resp.new_price)
                         .html('<a href="' + fallbackHref + '">' + resp.new_price + '</a>');
                    updateTotal(resp.new_total);
                },
                restore
            );
        }

        $input.bind('keydown', function (e) {
            if (e.keyCode === 13) { $(this).unbind('blur'); save(); }
            if (e.keyCode === 27) { $(this).unbind('blur'); restore(); }
        }).bind('blur', save);
    });

});
