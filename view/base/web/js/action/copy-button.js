define([
    'jquery'
], function (
    $
) {
    'use strict';

    $('.copy-btn').click(function() {
        var copyText = jQuery('.payment-code').val();
        copyToClipboard(copyText, function(){
            $('.copied').fadeIn(500).delay(3000).fadeOut(500);
        });
    });

    async function copyToClipboard(textToCopy, successCallback) {
        // Navigator clipboard api needs a secure context (https)
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(textToCopy)
                .then(successCallback);
        } else {
            // Use the 'out of viewport hidden text area' trick
            const textArea = document.createElement("textarea");
            textArea.value = textToCopy;

            // Move textarea out of the viewport so it's not visible
            textArea.style.position = "absolute";
            textArea.style.left = "-999999px";

            document.body.prepend(textArea);
            textArea.select();

            try {
                document.execCommand('copy');
            } catch (error) {
                console.error(error);
            } finally {
                textArea.remove();
                successCallback();
            }
        }
    }
});
