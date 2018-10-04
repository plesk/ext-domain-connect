<?php
// Copyright 1999-2018. Plesk International GmbH.
$messages = [
  'apply' => [
    'action' => '<strong>%%providerName%%</strong>에 연결하여 DNS 구성을 변경하고 싶습니까?',
    'cancelButton' => '취소',
    'connectButton' => '연결',
    'description' => '<strong>%%domain%%</strong>을(를) <strong>%%providerName%%</strong>에 연결하면 기존의 DNS 구성이 변경됩니다.',
    'hideDetails' => '세부 정보 숨기기',
    'nothingToAdd' => '추가할 DNS 레코드가 없습니다. 서비스가 이미 연결되었습니다.',
    'showDetails' => '세부 정보 표시',
    'success' => '변경 내용이 %%domain%%에 적용되었습니다.',
    'toAdd' => '다음 DNS 레코드가 추가됩니다.',
    'toRemove' => '다음 DNS 레코드가 제거됩니다.',
  ],
  'description' => '도메인 이름을 구매하는 것은 단지 시작에 불과합니다. 웹 사이트를 온라인에 올리려면 도메인과 연결해야 합니다. 도메인 이름을 등록 기관에서 구매했고 웹 사이트를 다른 호스팅 제공업체에 호스트했다면 도메인 이름이 Plesk 서버를 가리키도록 해야 합니다. 도메인 이름이 이미 Plesk를 가리킬 경우 별도의 제3자 서비스(예: 메일 또는 전자상거래 플랫폼)를 웹 사이트에 추가할 수 있습니다. 두 경우 모두 DNS 설정을 구성해야 합니다. 이 작업은 특히 초보자에게 까다로운 작업이 될 수 있습니다. 이 작업을 손쉽게 하려면 Domain Connect(도메인 연결) 확장 프로그램을 사용하십시오. 도메인 이름만 제공하면 확장 프로그램이 자동으로 DNS 설정을 구성합니다.',
  'exceptions' => [
    'clientHasNotAccessToDomain' => '도메인에 액세스할 수 없음.',
    'dnsProviderDisabled' => 'DNS 제공업체가 서버 구성에 의해 비활성화되었습니다.',
    'postRequestRequired' => 'POST 요청이 필요합니다.',
  ],
  'message.connect' => '자동으로 <strong>%%domain%%</strong>을(를) Plesk에 연결하시겠습니까? %%link%%.',
  'message.link' => 'Domain Connect를 사용하여 연결',
  'title' => 'Domain Connect',
];