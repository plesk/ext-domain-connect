<?php
// Copyright 1999-2018. Plesk International GmbH.

$messages = [
    'title' => 'Domain Connect',
    'description' => 'Connecting domains to your webspace is not always easy - especially for less technical people. The Plesk Domain Connect Extension solves this issue by automatically configuring DNS for your website. Just add your domain name and let Plesk do the rest for you.',
    'message.connect' => 'Your domain <strong>%%domain%%</strong> seems not to be resolving to the IP address of the current Plesk instance. The DNS settings can be configured automatically using Domain Connect. <a href="%%url%%">Click here to initialise the proper configuration</a>.',
    'apply' => [
        'description' => 'Connecting <strong>%%domain%%</strong> to <strong>%%providerName%%</strong> requires us to change several DNS records.',
        'action' => '<br/>Connect to <strong>%%providerName%%</strong> and change your domain\'s DNS records?',
        'toRemove' => 'The following records will be removed:',
        'toAdd' => 'The following records will be added:',
        'connectButton' => 'Connect',
        'success' => 'The changes were successfully applied to %%domain%%.',
    ],
    'exceptions' => [
        'postRequestRequired' => 'POST request is required.',
        'clientHasNotAccessToDomain' => 'No access to domain.',
    ]
];
