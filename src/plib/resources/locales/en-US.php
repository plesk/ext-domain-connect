<?php
// Copyright 1999-2018. Plesk International GmbH.

$messages = [
    'title' => 'Domain Connect',
    'description' => 'Buying a domain name is just the start of a journey. To put your website online, you need to associate it with your domain. If you have bought a domain name from a registrar and host your website at another hosting provider, you may need to point the domain name to your Plesk server. If your domain name is already pointing to Plesk, you may want to add additional third-party services (for example, mail or e-commerce platform) to your website. In both cases, you will have to configure DNS settings. This may be a challenging task, especially for a beginner. To accomplish this task easily, use the Domain Connect extension. Just provide the domain name, and the extension will automatically configure DNS settings for you.',
    'message.connect' => 'Do you want to connect <strong>%%domain%%</strong> to Plesk automatically? %%link%%.',
    'message.mailconnect' => 'Do you want to connect mailservice of <strong>%%domain%%</strong> to Plesk automatically? %%link%%.',
    'message.link' => 'Connect using Domain Connect',
    'apply' => [
        'description' => 'Connecting <strong>%%domain%%</strong> to <strong>%%providerName%%</strong> will change the current DNS configuration.',
        'action' => 'Connect to <strong>%%providerName%%</strong> and change your domain\'s DNS configuration?',
        'showDetails' => 'Show details',
        'hideDetails' => 'Hide details',
        'toRemove' => 'The following DNS records will be removed:',
        'toAdd' => 'The following DNS records will be added:',
        'nothingToAdd' => 'No DNS records to add. The service is already connected.',
        'connectButton' => 'Connect',
        'cancelButton' => 'Cancel',
        'success' => 'The changes were applied to %%domain%%.',
    ],
    'exceptions' => [
        'postRequestRequired' => 'A POST request is required.',
        'clientHasNotAccessToDomain' => 'No access to domain.',
        'dnsProviderDisabled' => 'The DNS provider is disabled by server configuration.',
    ]
];
