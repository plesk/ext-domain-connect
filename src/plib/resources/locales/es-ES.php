<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => '¿Desea conectarse a <strong>%%providerName%%</strong> y modificar la configuración DNS de su dominio?',
    'cancelButton' => 'Cancelar',
    'connectButton' => 'Conectar',
    'description' => 'La conexión de <strong>%%domain%%</strong> a <strong>%%providerName%%</strong> modificará la configuración actual del DNS.',
    'hideDetails' => 'Ocultar detalles',
    'nothingToAdd' => 'Ningún registro DNS a añadir. El servicio ya está conectado.',
    'showDetails' => 'Ver detalles',
    'success' => 'Los cambios han sido aplicados a %%domain%%.',
    'toAdd' => 'Se añadirán los siguientes registros DNS:',
    'toRemove' => 'Se eliminarán los siguientes registros DNS:',
  ],
  'description' => 'La compra de un nombre de dominio sólo es el principio. Si desea que su sitio esté operativo en la red debe asociarlo con su dominio. Si ha comprado un nombre de dominio a través de un registrador y aloja su sitio en otro proveedor de hosting, puede que deba apuntar su nombre de dominio a su servidor Plesk. Si su nombre de dominio ya apunta a Plesk, puede que desee añadir servicios de terceros adicionales (como por ejemplo correo o plataforma e-commerce) a su sitio web. En ambos casos deberá configurar el DNS. Puede que esto le resulte todo un reto, más aún si no dispone de demasiados conocimientos al respecto. Para ello, use la extensión Domain Connect. Simplemente deberá indicar el nombre de dominio y la extensión configurará el DNS de forma automática.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Sin acceso al dominio.',
    'dnsProviderDisabled' => 'El proveedor de DNS ha sido desactivado por la configuración del servidor.',
    'postRequestRequired' => 'Se requiere una petición POST.',
  ],
  'message.connect' => '¿Desea conectar <strong>%%domain%%</strong> con Plesk automáticamente? %%link%%.',
  'message.link' => 'Efectuar conexión mediante Domain Connect',
  'title' => 'Domain Connect',
];