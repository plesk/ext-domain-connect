// Copyright 1999-2018. Plesk International GmbH.

Jsw.namespace('PleskExt.DomainConnect');

PleskExt.DomainConnect.addConnectionMessage = function(domainId, message, closable, options) {
    closable = typeof closable === 'undefined' || closable;
    options = options || {};

    var messageId = 'ext-domain-connect--message-' + domainId;
    Jsw.addStatusMessage('info', message, {
        id: messageId,
        closable: closable,
        onClose: function() {
            $(this).up('.msg-box').remove();
            new Ajax.Request(Jsw.prepareUrl('/modules/domain-connect/index.php/index/hide-warning'), {
                method: 'post',
                parameters: { 'domainId': domainId }
            });
        }
    });

    var messageEl = document.getElementById(messageId);
    var linkEl = messageEl && messageEl.querySelector('a');
    linkEl && linkEl.addEventListener('click', function (event) {
        event.preventDefault();
        var features = options.width && options.height
            ? 'width=' + options.width + ',' + 'height=' + options.height
            : '';
        window.open(linkEl.href, '', features);
    });
};
