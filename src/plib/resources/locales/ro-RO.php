<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => 'Vă conectați la <strong>%%providerName%%</strong> și modificați configurația DNS a domeniului?',
    'cancelButton' => 'Revocare',
    'connectButton' => 'Conectare',
    'description' => 'Conectarea <strong>%%domain%%</strong> la <strong>%%providerName%%</strong> va duce la modificarea configurației DNS actuale.',
    'hideDetails' => 'Ascundere detalii',
    'nothingToAdd' => 'Nu trebuie adăugată nicio înregistrare DNS. Serviciul este conectat deja.',
    'showDetails' => 'Afișare detalii',
    'success' => 'Modificările au fost aplicate în %%domain%%.',
    'toAdd' => 'Se vor adăuga următoarele înregistrări DNS:',
    'toRemove' => 'Se vor elimina următoarele înregistrări DNS:',
  ],
  'description' => 'Cumpărarea unui nume de domeniu este doar primul pas. Pentru ca site-ul să fie accesibil online, trebuie să-l asociați cu domeniul. Dacă ați cumpărat un nume de domeniu de la o instituție de înregistrare și găzduiți site-ul la un alt furnizor de servicii, poate fi nevoie de direcționarea numelui de domeniu spre serverul Plesk. Dacă numele de domeniu este direcționat deja spre Plesk, puteți adăuga alte servicii terță parte la site (de exemplu, e-mail sau o platformă de comerț electronic). În ambele cazuri trebuie să configurați setările DNS. Această operație poate fi dificilă, în special pentru începători. Pentru a rezolva ușor problema, utilizați extensia Domain Connect. Este suficient să introduceți numele de domeniu: extensia va configura automat setările DNS.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Domeniul este inaccesibil.',
    'dnsProviderDisabled' => 'Furnizorul DNS este dezactivat de configurația serverului.',
    'postRequestRequired' => 'Este necesară o solicitare POST.',
  ],
  'message.connect' => 'Conectați automat <strong>%%domain%%</strong> la Plesk? %%link%%.',
  'message.link' => 'Conectare prin Domain Connect',
  'title' => 'Domain Connect',
];