;
(function ($) {
    $(document).ready(function () {

        $('.address_finder').each(function (i, elem) {
            var widget,
                key = $(elem).data('api-key'),
                address = $(elem).find('.address_finder_address'),
                input = $(elem).find('input').first(),
                manual = $(elem).find('.manual_address'),
                toggle = $(elem).find('.toggle_manual_address')

            var useManual = manual.find('input[name*=ManualAddress]'),
                field = address.find('input').get(0)

            /* update ui with javascript */
            toggle.show()
            address.show()

            if (!useManual.val()) {
                manual.hide()
            }

            if (!$(elem).find('.addressfinder__holder input').length) {
                return;
            }

            // Define your own API key below
            var addyApiKey = key;

            input.removeAttr("autocomplete");

            // Instantiate the Address Autocomplete for the 'address1' field
            var addy = new AddyComplete(addyApiKey, input);

            addy.onPopulate = function (address) {
                manual.find('input[name*=Address1]').val(address.address1 || '')
                manual.find('input[name*=FullAddress]').val(address.full || '')
                manual.find('input[name*=Region]').val(address.region || '')
                manual.find('input[name*=District]').val(address.territory || '')
                manual.find('input[name*=Suburb]').val(address.suburb || '')
                manual.find('input[name*=City]').val(address.city || '')
                manual.find('input[name*=Postcode]').val(item.postcode || '')
                manual.find('input[name*=Longitude]').val(address.x || '')
                manual.find('input[name*=Latitude]').val(address.y || '')

                input.val(address.full);
                $('body').trigger(jQuery.Event('addressselected'))
            };

            /* click handler to toggle manual div */
            toggle.on('click', function (e) {
                e.preventDefault()

                manual.toggle('slow')

                // if the manual address is visible then add a hidden flag so
                if (manual.is(':visible')) {
                    useManual.val('1')
                } else {
                    useManual.val('0')
                }

                return false;
            })

            /* focusing back on the address dropdown should hide the manual */
            input.on('focus', function (e) {
                manual.slideUp()
            })
        })
    })
})(jQuery)
