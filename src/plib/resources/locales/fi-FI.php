<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => 'Haluatko luoda yhteyden kohteeseen <strong>%%providerName%%</strong> ja muuttaa toimialueesi DNS-määrityksiä?',
    'cancelButton' => 'Peruuta',
    'connectButton' => 'Yhdistä',
    'description' => 'Kun toimialue <strong>%%domain%%</strong> yhdistetään palveluun <strong>%%providerName%%</strong>, nykyiset DNS-määritykset muuttuvat.',
    'hideDetails' => 'Piilota tiedot',
    'nothingToAdd' => 'Ei lisättäviä DNS-tietueita. Palvelu on jo yhdistetty.',
    'showDetails' => 'Näytä tiedot',
    'success' => 'Muutokset toteutettiin toimialueessa %%domain%%.',
    'toAdd' => 'Seuraavat DNS-tietueet lisätään:',
    'toRemove' => 'Seuraavat DNS-tietueet poistetaan:',
  ],
  'description' => 'Toimialueen nimen hankkiminen on ainoastaan kaiken alku. Verkkosivuston julkaiseminen edellyttää, että se yhdistetään toimialueeseen. Jos olet hankkinut toimialueen rekisterinpitäjältä ja verkkosivustoa isännöi toinen isännöintipalvelujen tarjoaja, toimialueen nimi voi olla tarpeen ohjata Plesk-palvelimeen.Jos toimialueesi nimi on jo ohjattu Pleskiin, voit haluta lisätä verkkosivustoosi ylimääräisiä kolmannen osapuolen palveluja (esim. sähköposti tai verkkokauppa-alusta). Molemmat edellyttävät DNS-asetuksien määrittämisen. Tämä voi olla haastava tehtävä, varsinkin aloittelijalle.Se voidaan suorittaa helposti Domain Connect -laajennuksella. Sinun tarvitsee vain antaa toimialueen nimi ja laajennus määrittää DNS-asetukset automaattisesti puolestasi.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Ei pääsyä toimialueeseen.',
    'dnsProviderDisabled' => 'Palvelimen määritykset poistivat DNS-palveluntarjoajan käytöstä.',
    'postRequestRequired' => 'LÄHETYS-pyyntö vaaditaan.',
  ],
  'message.connect' => 'Haluatko yhdistää toimialueen <strong>%%domain%%</strong> Pleskiin automaattisesti? %%link%%.',
  'message.link' => 'Luo yhteys Domain Connectilla',
  'title' => 'Domain Connect',
];