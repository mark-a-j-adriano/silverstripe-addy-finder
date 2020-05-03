(function ($) {
    $.entwine("ss", function ($) {

        $(".address_finder p a").entwine({
            onmatch: function () {

            },
            onclick: function (e) {
                e.preventDefault();
                $(this).parents('.address_finder[data-api-key]').find('.manual_address').slideToggle(function () {
                    $(this).parents('.address_finder[data-api-key]').find('input[name*=ManualAddress]').val(
                        $(this).parents('.address_finder[data-api-key]').find('.manual_address').is(":visible")
                    )
                });
            }
        })

        $(".address_finder").entwine({
            onmatch: function (deferred) {
                var widget,
                    key = $(this).data('api-key'),
                    address = $(this).find('.address_finder_address'),
                    input = $(this).find('input').first(),
                    manual = $(this).find('.manual_address'),
                    toggle = $(this).find('.toggle_manual_address');

                var useManual = manual.find('input[name*=ManualAddress]'),
                    field = address.find('input').get(0)

                /* update ui with javascript */
                toggle.show()
                address.show()

                if (!useManual.val()) {
                    manual.hide()
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
                    manual.find('input[name*=Postcode]').val(address.postcode || '')
                    manual.find('input[name*=Longitude]').val(address.x || '')
                    manual.find('input[name*=Latitude]').val(address.y || '')

                    input.val(address.full);

                    $('body').trigger(jQuery.Event('addressselected'))
                };

                /* on manually changing of the fields then we have to clear x/y */
                manual.on('keydown', 'input', function (e) {
                    manual.find('input[name*=Longitude]').val('')
                    manual.find('input[name*=Latitude]').val('')
                })

                /* focusing back on the address dropdown should hide the manual */
                input.on('focus', function (e) {
                    manual.slideUp()
                })
            }
        })
    })
})(jQuery)
