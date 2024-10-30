var current_currency;

jQuery(document).ready(
    function ($) {

        $('.js_currency_converter_select').change(function (event) {
            current_currency = $(event.currentTarget).val();
            setCurCookie('current_currency', current_currency);

            event.preventDefault();

            update_currency(current_currency);
        });

        /**
         * Update the currency based on the selection
         */
        update_currency = function (current_currency) {
            if (typeof null === typeof currency || undefined === js_currency_converter) {
                current_currency = js_currency_converter.from;
            }

            let rate = js_currency_converter.exchange_rates[current_currency];
            if (typeof rate === typeof undefined) {
                rate = 1;
            }

            let exchange_data = js_currency_converter.exchange_values[current_currency];
            if (typeof exchange_data === typeof undefined) {
                exchange_data = {
                    "flag"     : "United States of America(USA).png",
                    "title"    : "USD",
                    "symbol"   : "$",
                    "decimal"  : ".",
                    "separator": ","
                };
            }

            $('.' + js_currency_converter.target).each(function () {

                let org_value = $(this).attr('data-org_value');
                if (typeof org_value === typeof undefined || org_value === false) {
                    $(this).attr('data-org_value', currency($(this).text()));
                }
                org_value = currency($(this).attr('data-org_value'), {precision: 2});

                let convertedValue = currency(
                    org_value,
                    {
                        formatWithSymbol: true,
                        symbol          : exchange_data.symbol,
                        decimal         : exchange_data.decimal,
                        separator       : exchange_data.separator,
                        precision       : 2
                    }
                ).multiply(rate).format();

                $(this).html(convertedValue);
            });
        };

        /**
         * Store a cookie
         *
         * @param cname
         * @param cvalue
         */
        setCurCookie = function (cname, cvalue) {
            let d = new Date(),
                exdays = 31;
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            let expires = 'expires=' + d.toUTCString();
            document.cookie = cname + '=' + cvalue + ';' + expires + 'path=/';
        };

        /**
         * Get the cookie value
         * @param cname
         * @returns {*}
         */
        getCookie = function (cname) {
            let name = cname + '=',
                ca = document.cookie.split(';');
            for(var i = 0; i < ca.length; i ++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return '';
        };

        /*
         * Set the stored currency, from the php coockie
         */
        current_currency = getCookie('current_currency');

        update_currency(js_currency_converter.from);
        if (undefined !== current_currency && '' !== current_currency) {
            $('.js_currency_converter_select').val(current_currency);
            update_currency(current_currency);
        }

        /**
         * Adding a flag to the option
         *
         * @param state
         * @returns {*|jQuery|HTMLElement}
         */
        formatState = function (state) {
            if (!state.id) {
                return state.text;
            }

            return $('<span><img src="' + $(state.element).attr('data-image') + '" class="js_currency_converter_img-flag" /> ' + state.text + '</span>');
        };
        /**
         * Show a flag in the selected item
         *
         * @param event
         */
        formatSelect = function (event) {
            var current_value = event.currentTarget.value,
                current_item = $('option[name="' + event.currentTarget.value + '"]');

            if (undefined === current_item.html()) {
                current_value = js_currency_converter.from
                current_item = $('option[name="' + current_value + '"]');
            }

            $('.select2-selection__rendered').html('<span><img src="' + current_item.attr('data-image') + '" class="js_currency_converter_img-flag" /> ' + current_value + '</span>');
        };

        $('.js_currency_converter_select')
            .select2({
                templateResult         : formatState,
                minimumResultsForSearch: Infinity
            })
            .on('select2:select', formatSelect)
            .trigger('select2:select');

        /*
         * Add a disabled class when there is no target found
         */
        if (1 > $('.' + js_currency_converter.target).length) {
            $('.js_currency_converter').addClass('no_target');
        }

    });
