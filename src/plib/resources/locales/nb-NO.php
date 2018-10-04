<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => 'Koble til <strong>%%providerName%%</strong> og endre domenets DNS-konfigurasjon?',
    'cancelButton' => 'Avbryt',
    'connectButton' => 'Koble til',
    'description' => 'Tilkobling av <strong>%%domain%%</strong> til <strong>%%providerName%%</strong> vil endre den aktive DNS-konfigurasjonen.',
    'hideDetails' => 'Skjul detaljer',
    'nothingToAdd' => 'Ingen DNS-oppføringer å legge til.Tjenesten er allerede koblet til.',
    'showDetails' => 'Vis detaljer',
    'success' => 'Endringene ble lagt til %%domain%%.',
    'toAdd' => 'Følgende DNS-oppføringer vil bli lagt til:',
    'toRemove' => 'Følgende DNS-oppføringer vil bli fjernet:',
  ],
  'description' => 'Å kjøpe et domenenavn er bare starten på reisen. For å få nettstedet ditt opp på nettet, må du koble det til domenet ditt. Hvis du har kjøpt et domenenavn fra en registreringsmyndighet og drifter nettstedet hos en annen driftsleverandør, må du kanskje få domenenavnet til å peke mot Plesk-serveren.Hvis domenenavnet ditt allerede peker mot Plesk, ønsker du kanskje å legge flere tredjepartstjenester (for eksempel e-post eller e-handelplattform) til nettstedet. I begge tilfellene må du konfigurere DNS-innstillingene.Dette kan være en utfordrende oppgave, spesielt for en nybegynner.For enkel fullføring av denne oppgaven, kan du bruke utvidelsen Domain Connect. Bare angi domenenavnet, så vil utvidelsen automatisk konfigurere DNS-innstillingene for deg.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Ingen tilgang til domenet.',
    'dnsProviderDisabled' => 'DNS-leverandøren er deaktivert av serverkonfigurasjonen.',
    'postRequestRequired' => 'Det kreves en POST-forespørsel.',
  ],
  'message.connect' => 'Vil du koble <strong>%%domain%%</strong> til Plesk automatisk? %%link%%.',
  'message.link' => 'Koble til ved hjelp av Domain Connect',
  'title' => 'Domain Connect',
];