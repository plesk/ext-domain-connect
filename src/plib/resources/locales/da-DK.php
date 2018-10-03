<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => 'Vil du forbinde til <strong>%%providerName%%</strong> og rette dit domænes DNS konfiguration?',
    'cancelButton' => 'Afbryd',
    'connectButton' => 'Tilslut',
    'description' => 'Tilslutning af <strong>%%domain%%</strong> til <strong>%%providerName%%</strong> vil ændre dit domænes aktuelle DNS konfiguration.',
    'hideDetails' => 'Skjul detaljer',
    'nothingToAdd' => 'Ingen DNS-poster at tilføje. Servicen er allerede tilsluttet.',
    'showDetails' => 'Vis detaljerede oplysninger',
    'success' => 'Ændringerne blev anvendt på %%domain%%.',
    'toAdd' => 'Følgende DNS-poster vil blive tilføjet:',
    'toRemove' => 'Følgende DNS-poster vil blive fjernet.',
  ],
  'description' => 'Købet af et domæne er blot starten på rejsen. For at lægge dit websted online, er det nødvendigt at knytte det til dit domæne. Hvis du har købt et domænenavn hos en registrator og host\'e webstedet hos en anden hosting-udbyder, kan det være nødvendigt henvise domænenavnet til din Plesk-server.Hvis domænenavnet allerede peger til Plesk, vil du måske knytte yderligere tredjepartsservices (f.eks. mail eller en e-handelsplatform) til webstedet. I begge tilfælde skal du konfigurere DNS-indstillingerne. Dette kan være lidt af en udfordring, specielt for en begynder. Du kan nemmere komme omkring denne opgave ved at bruge udvidelsen Domain Connect. Angiv blot domænenavnet, hvorefter udvidelsen automatisk vil konfigurere DNS-indstillingerne for dig.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Ingen adgang til domæne.',
    'dnsProviderDisabled' => 'DNS-udbyderen er deaktiveret af serverkonfigurationen.',
    'postRequestRequired' => 'En POST-forespørgsel kræves.',
  ],
  'message.connect' => 'Vil du automatisk forbinde <strong>%%domain%%</strong> med Plesk? %%link%%.',
  'message.link' => 'Tilslut vha.Domain Connect',
  'title' => 'Domain Connect',
];