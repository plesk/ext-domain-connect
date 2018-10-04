<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => '<strong>%%providerName%%</strong> ile irtibata geçip, alan adınızın DNS yapılandırmasını değiştirmek istiyor musunuz?',
    'cancelButton' => 'İptal',
    'connectButton' => 'Bağla',
    'description' => '<strong>%%domain%%</strong> alan adını <strong>%%providerName%%</strong> ile bağlamak, geçerli DNS yapılandırmasını değiştirir.',
    'hideDetails' => 'Ayrıntıları gizle',
    'nothingToAdd' => 'Eklenecek DNS kaydı yok. Servis zaten bağlı.',
    'showDetails' => 'Ayrıntıları göster',
    'success' => 'Değişiklikler, %%domain%% üzerinde uygulandı.',
    'toAdd' => 'Aşağıdaki DNS kayıtları eklenecek:',
    'toRemove' => 'Aşağıdaki DNS kayıtları kaldırılacak:',
  ],
  'description' => 'Bir alan adı satın almak, bir yolculuğun sadece başlangıcıdır. Web sitenizi çevrimiçi yapmak için, onu alan adınızla ilişkilendirmeniz gerekir. Alan adınızı bir kayıt şirketinden alıp, web siteniziyse başka bir barındırma sağlayıcısında barındırıyorsanız, alan adını Plesk sunucunuza yönlendirmeniz gerekebilir. Alan adınız zaten Plesk\'inize yönlendiriyorsa, web sitenize ek üçüncü taraf servisler (örneğin posta veya e-ticaret platformu) eklemek isteyebilirsiniz. Her iki durumda da DNS ayarlarını yapılandırmanız gerekecektir. Bu, özellikle yeni başlayanlar için zorlu bir görev olabilir. Bu görevi kolayca tamamlamak için, Domain Connect uzantısını kullanın. Alan adını verdiğinizde uzantı, DNS ayarlarını sizin için otomatik olarak yapılandırır.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'Alan adına erişim yok.',
    'dnsProviderDisabled' => 'DNS sağlayıcısı, sunucu yapılandırması tarafından devre dışı bırakılmış.',
    'postRequestRequired' => 'Bir POST talebi gerekli.',
  ],
  'message.connect' => '<strong>%%domain%%</strong> alan adını otomatik olarak Plesk\'e bağlamak istiyor musunuz? %%link%%.',
  'message.link' => 'Domain Connect kullanarak bağla',
  'title' => 'Domain Connect',
];