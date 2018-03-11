<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.

$messages = [
    'title' => 'Domain Connect',
    'description' => 'Connecting domains to your webspace is not always easy - especially for less technical people. The Plesk Domain Connect Extension solves this issue by automatically configuring DNS for your website. Just add your domain name and let Plesk do the rest for you.',
    'message.connect' => 'Your domain <strong>%%domain%%</strong> seems not to be resolving to the IP address of the current Plesk instance. The DNS settings can be configured automatically using Domain Connect. <a href="%%url%%">Click here to initialise the proper configuration</a>.',
    'apply.description' => 'Connecting your domain <strong>%%domain%%</strong> to <strong>%%providerName%%</strong> requires we change some DNS records.',
    'apply.action' => 'Connect to <strong>%%providerName%%</strong> and change your domain\'s DNS records?',
    'exceptions' => [
        'postRequestRequired' => 'POST request is required.',
        'clientHasNotAccessToDomain' => 'No access to domain.',
    ]
];
