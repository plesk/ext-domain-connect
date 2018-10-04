<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => 'Anslut till <strong>%%providerName%%</strong> och ändra domänens DNS-konfiguration?',
    'cancelButton' => 'Avbryt',
    'connectButton' => 'Anslut',
    'description' => 'Anslutningen till <strong>%%domain%%</strong> till <strong>%%providerName%%</strong> gör att domänens aktuella DNS-konfiguration ändras?',
    'hideDetails' => 'Dölj information',
    'nothingToAdd' => 'Inga DNS-uppgifter att lägga till. Tjänsten är redan ansluten.',
    'showDetails' => 'Visa detaljer',
    'success' => 'Ändringarna har applicerats på %%domain%%.',
    'toAdd' => 'Följande DNS-uppgifter kommer att läggas till:',
    'toRemove' => 'Följande DNS-uppgifter kommer att tas bort:',
  ],
  'description' => 'Att köpa ett domännamn är bara början på en resa. För att placera webbplatsen online, behöver du associera den med din domän. Om du har köpt ett domännamn från en registeransvarig och har din webbplats hos en annan webbleverantör, kan du behöva rikta in domännamnet på din Plesk-server.Om ditt domännamn redan är riktat på Plesk kanske du vill lägga till ytterligare tjänster från tredje part (till exempel e-post eller e-handelsplattform) på din webbplats. I båda dessa fall måste du konfigurera DNS-inställningarna. Detta kan vara en utmanande uppgift, särskilt för nybörjare. För att klara av uppgiften enkelt, använd Domain Connect-ändelsen. Ange bara domännamnet så kommer DNS-inställningarna att konfigurerats automatiskt.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Ingen åtkomst till domänen.',
    'dnsProviderDisabled' => 'DNS-leverantören har inaktiverats av serverkonfigurationen.',
    'postRequestRequired' => 'En POST-begäran krävs.',
  ],
  'message.connect' => 'Vill du ansluta <strong>%%domain%%</strong> automatiskt till Plesk? %%link%%.',
  'message.link' => 'Anslut med Domain Connect',
  'title' => 'Domain Connect',
];