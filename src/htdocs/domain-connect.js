// Copyright 1999-2018. Plesk International GmbH.

Jsw.namespace('PleskExt.DomainConnect');

PleskExt.DomainConnect.addConnectionMessage = function(domainId, message, closable) {
    Jsw.addStatusMessage('info', message, {
        closable: typeof closable === 'undefined' || closable,
        onClose: function() {
            $(this).up('.msg-box').remove();
            new Ajax.Request(Jsw.prepareUrl('/modules/domain-connect/index.php/index/hide-warning'), {
                method: 'post',
                parameters: { 'domainId': domainId }
            });
        }
    });
};
