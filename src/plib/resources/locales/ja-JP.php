<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => '<strong>%%providerName%%</strong> に接続し、ドメインの DNS 構成を変更しますか？',
    'cancelButton' => 'キャンセル',
    'connectButton' => '接続',
    'description' => '<strong>%%domain%%</strong> を <strong>%%providerName%%</strong> に接続すると、現在の DNS 構成が変更されます。',
    'hideDetails' => '詳細を隠す',
    'nothingToAdd' => '追加する DNS レコードはありません。サービスは既に接続されています。',
    'showDetails' => '詳細を表示',
    'success' => '変更が %%domain%% に適用されました。',
    'toAdd' => '以下の DNS レコードが追加されます。',
    'toRemove' => '以下の DNS レコードが削除されます。',
  ],
  'description' => 'ドメイン名の購入は最初の一歩に過ぎません。ウェブサイトをオンラインで公開するには、ウェブサイトにドメインを紐付ける必要があります。レジストラから既にドメイン名を購入しており、別のホスティング事業者でウェブサイトをホストする場合、ドメイン名を Plesk サーバにポイントさせる必要がある可能性があります。また、ドメイン名が既に Plesk をポイントしており、ウェブサイトに追加のサードパーティサービス（メールや e-コマースなどのプラットフォーム）を追加したい場合もあります。いずれの場合も DNS 設定の構成が必要になります。これは、特に初心者にとって難しい作業です。Domain Connect 拡張を使用すると、この作業を簡単に実行できます。ドメイン名を提供するだけで、この拡張によって DNS 設定が自動的に構成されます。',
  'exceptions' => [
    'clientHasNotAccessToDomain' => 'ドメインへのアクセスがありません。',
    'dnsProviderDisabled' => 'DNS プロバイダがサーバ構成で無効化されています。',
    'postRequestRequired' => 'POST リクエストが必要です。',
  ],
  'message.connect' => '<strong>%%domain%%</strong> を Plesk に自動的に接続しますか？%%link%%。',
  'message.link' => 'Domain Connect を使用して接続',
  'title' => 'Domain Connect',
];