// Copyright 1999-2018. Plesk International GmbH.

Jsw.namespace('PleskExt.DomainConnect');

PleskExt.DomainConnect.warnAboutDomainResolvingIssue = function(domainId, message) {
    Jsw.addStatusMessage('info', message, {
        closable: true,
        onClose: function() {
            $(this).up('.msg-box').remove();
            new Ajax.Request(Jsw.prepareUrl('/modules/domain-connect/index.php/index/hide-warning'), {
                method: 'post',
                parameters: { 'domainId': domainId }
            });
        }
    });
};
