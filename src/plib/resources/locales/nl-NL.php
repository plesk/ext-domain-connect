<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => 'Koppeling maken met <strong>%%providerName%%</strong> en de DNS-configuratie van uw domein aanpassen?',
    'cancelButton' => 'Annuleren',
    'connectButton' => 'Koppelen',
    'description' => 'Het koppelen van <strong>%%domain%%</strong> net <strong>%%providerName%%</strong> zal de huidige DNS-configuratie wijzigen.',
    'hideDetails' => 'Details verbergen',
    'nothingToAdd' => 'Er zijn geen DNS-records toe te voegen. De dienst is al gekoppeld.',
    'showDetails' => 'Details weergeven',
    'success' => 'De wijzigingen zijn toegepast op %%domain%%.',
    'toAdd' => 'De volgende DNS-records zullen worden toegevoegd:',
    'toRemove' => 'De volgende DNS-records zullen worden verwijderd:',
  ],
  'description' => 'Het aanschaffen van een domeinnaam is alleen nog maar het begin. Voor uw website online is, moet u deze koppelen aan uw domein. Als u een domeinnaam hebt aangeschaft bij een bedrijf dat domeinregistratie verzorgd (een registrar) en uw website bij een ander bedrijf (een hostingprovider), dan moet u waarschijnlijk eerst de domeinnaam naar uw Plesk-server verwijzen. Als uw domeinnaam al naar Plesk verwijst, dan wilt u wellicht extra diensten van derden toevoegen aan uw website, zoals e-mail of een e-commerceplatform. In beide gevallen moet u de DNS-instellingen configureren. Dit kan best nog een uitdaging zijn, zeker als u het voor het eerst doet. U kunt de Domain Connect-extensie gebruiken om het allemaal wat makkelijker te maken. Geef simpelweg de domeinnaam op, en de extensie zal de DNS-instellingen automatisch voor u configureren.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Geen toegang tot domein.',
    'dnsProviderDisabled' => 'De DNS-provider is uitgeschakeld in de instellingen van de server.',
    'postRequestRequired' => 'Er is een POST-aanvraag vereist.',
  ],
  'message.connect' => 'Wilt u <strong>%%domain%%</strong> automatisch aan Plesk koppelen? %%link%%.',
  'message.link' => 'Koppelen met Domain Connect',
  'title' => 'Domain Connect',
];