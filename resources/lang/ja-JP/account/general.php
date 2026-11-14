<?php

return [
    'personal_api_keys' => '個人用APIキー',
    'personal_access_token' => '個人アクセストークン',
    'personal_api_keys_success' => '個人APIキー「:key」を作成しました。',
    'here_is_api_key' => '新しい個人アクセストークンです。この画面を閉じると再表示できないため、今すぐ安全な場所に保存してください。このトークンを使ってAPIリクエストを送信できます。',
    'api_key_warning' => 'APIトークンは作成時に一度しか表示されません。必ずすぐにコピーし、安全な場所に保存してください。',
    'api_base_url' => 'APIベースURLは以下の場所です:',
    'api_base_url_endpoint' => '/<エンドポイント>',
    'api_token_expiration_time' => 'APIトークンの有効期限:',
    'api_reference' => '利用できるエンドポイントや詳しい使用方法は、<a href="https://snipe-it.readme.io/reference" target="_blank">APIリファレンス</a>をご覧ください。',
    'profile_updated' => 'アカウントを更新しました。',
    'no_tokens' => '個人アクセストークンはまだ作成されていません。',
    'enable_sounds' => '効果音を有効にする',
    'enable_confetti' => '紙吹雪の演出を有効にする',
    'pat_scopes_column' => 'MCP Scopes',
    'pat_scopes_help' => 'Scopes control which MCP tools this token can call. Leave every box unchecked for a REST-API-only token. For an MCP client (ChatGPT, Claude Desktop, etc.), check the capabilities you want that client to have.',
    'pat_no_scopes' => 'REST API only',
    'pat_no_scopes_tooltip' => 'No MCP scopes are granted on this token. It works for every REST API endpoint but cannot call MCP tools. To use this token with an MCP client, delete it and create a new one with the capability scopes you need.',
    'mcp_url' => 'MCP URL: ',
];
