<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => 'Kapcsolódik <strong>%%providerName%%</strong> szolgáltatóhoz és megváltoztatja a domain DNS-konfigurációját?',
    'cancelButton' => 'Mégse',
    'connectButton' => 'Kapcsolódás',
    'description' => '<strong>%%domain%%</strong> <strong>%%providerName%%</strong> szolgáltatóhoz kapcsolódása megváltoztatja a jelenlegi DNS-konfigurációt.',
    'hideDetails' => 'Részletek elrejtése',
    'nothingToAdd' => 'Nincsenek hozzáadandó DNS-rekordok. Már kapcsolódott a szolgáltatás.',
    'showDetails' => 'Részletek megmutatása',
    'success' => '%%domain%% esetében megtörtént a változtatások alkalmazása.',
    'toAdd' => 'A következő DNS-rekordok hozzáadására kerül sor:',
    'toRemove' => 'A következő DNS-rekordok eltávolítására kerül sor:',
  ],
  'description' => 'Egy domain név megvásárlása csak a munka kezdete. A webhelyének interneten való megjelenítéséhez társítania kell azt a domainjéhez. Ha egy nyilvántartásból vásárolt egy domain nevet és egy mások üzemeltető üzemelteti a webhelyét, akkor esetleg gondoskodnia kell arról, hogy a Plesk kiszolgálójára mutatasson a domain név. Ha már a Plesk kiszolgálóra mutat a domain neve, akkor előfordulhat, hogy további harmadik fél szolgáltatásait (például levelezési vagy e-kereskedelmi platformot) akarja a webhelyéhez adni. Mindkét esetben el kell végeznie a DNS-beállítások konfigurálását. Ez kihívást jelentő feladat, különösen kezdők számára. A Domainhez kapcsolódás bővítményt használja e feladat könnyű elvégzéséhez. Csak adja meg a domain nevét és a bővítmény automatikusan elvégzi a DNS-beállítások konfigurálását a számára.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Nincs hozzáférés a domainhez.',
    'dnsProviderDisabled' => 'A kiszolgáló konfigurációja letiltotta a DNS-szolgáltatót.',
    'postRequestRequired' => 'POST kérelem szükséges.',
  ],
  'message.connect' => 'Akarja, hogy automatikusan történjen <strong>%%domain%%</strong> Plesk kezelőpanelhez kapcsolódása? %%link%%.',
  'message.link' => 'Domainhez kapcsolódás bővítmény használatával történő kapcsolódás',
  'title' => 'Domainhez kapcsolódás',
];