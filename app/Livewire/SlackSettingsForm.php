<?php

namespace App\Livewire;

use App\Helpers\Helper;
use App\Models\Setting;
use App\Rules\ExternalUrl;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;
use Osama\LaravelTeamsNotification\TeamsNotification;

class SlackSettingsForm extends Component
{
    public $webhook_endpoint;

    public $webhook_channel;

    public $webhook_botname;

    public $isDisabled = 'disabled';

    public $webhook_name;

    public $webhook_link;

    public $webhook_placeholder;

    public $webhook_icon;

    public $webhook_selected;

    public $teams_webhook_deprecated;

    public array $webhook_text;

    public Setting $setting;

    public $save_button;

    public $webhook_test;

    public $webhook_endpoint_rules;

    protected function rules(): array
    {
        return [
            'webhook_endpoint' => [
                'nullable',
                'required_with:webhook_channel',
                'url',
                new ExternalUrl,
            ],
            'webhook_channel' => 'required_with:webhook_endpoint|starts_with:#|nullable',
            'webhook_botname' => 'string|nullable',
        ];
    }

    public function mount()
    {
        $this->webhook_text = [
            'slack' => [
                'name' => trans('admin/settings/general.slack'),
                'icon' => 'fab fa-slack',
                'placeholder' => 'https://hooks.slack.com/services/XXXXXXXXXXXXXXXXXXXXX',
                'link' => 'https://api.slack.com/messaging/webhooks',
                'test' => 'testWebhook',
            ],
            'general' => [
                'name' => trans('admin/settings/general.general_webhook'),
                'icon' => 'fab fa-hashtag',
                'placeholder' => trans('general.url'),
                'link' => '',
                'test' => 'testWebhook',
            ],
            'google' => [
                'name' => trans('admin/settings/general.google_workspaces'),
                'icon' => 'fa-brands fa-google',
                'placeholder' => 'https://chat.googleapis.com/v1/spaces/xxxxxxxx/messages?key=xxxxxx',
                'link' => 'https://developers.google.com/chat/how-tos/webhooks#register_the_incoming_webhook',
                'test' => 'googleWebhookTest',
            ],
            'microsoft' => [
                'name' => trans('admin/settings/general.ms_teams'),
                'icon' => 'fa-brands fa-microsoft',
                'placeholder' => 'https://abcd.webhook.office.com/webhookb2/XXXXXXX',
                'link' => 'https://support.microsoft.com/en-us/office/create-incoming-webhooks-with-workflows-for-microsoft-teams-8ae491c7-0394-4861-ba59-055e33f75498',
                'test' => 'msTeamTestWebhook',
            ],
        ];

        $this->setting = Setting::getSettings();
        $this->save_button = trans('general.save');
        $this->webhook_selected = ($this->setting->webhook_selected !== '') ? $this->setting->webhook_selected : 'slack';
        $this->webhook_name = $this->webhook_text[$this->setting->webhook_selected]['name'] ?? $this->webhook_text['slack']['name'];
        $this->webhook_icon = $this->webhook_text[$this->setting->webhook_selected]['icon'] ?? $this->webhook_text['slack']['icon'];
        $this->webhook_placeholder = $this->webhook_text[$this->setting->webhook_selected]['placeholder'] ?? $this->webhook_text['slack']['placeholder'];
        $this->webhook_link = $this->webhook_text[$this->setting->webhook_selected]['link'] ?? $this->webhook_text['slack']['link'];
        $this->webhook_test = $this->webhook_text[$this->setting->webhook_selected]['test'] ?? $this->webhook_text['slack']['test'];
        $this->webhook_endpoint = $this->setting->webhook_endpoint;
        $this->webhook_channel = $this->setting->webhook_channel;
        $this->webhook_botname = $this->setting->webhook_botname;
        $this->webhook_options = $this->setting->webhook_selected;
        $this->teams_webhook_deprecated = ! Str::contains($this->webhook_endpoint, 'workflows');
        if ($this->webhook_selected === 'microsoft' || $this->webhook_selected === 'google') {
            $this->webhook_channel = '#NA';
        }

        if ($this->setting->webhook_endpoint != null && $this->setting->webhook_channel != null) {
            $this->isDisabled = '';
        }
        if ($this->webhook_selected === 'microsoft' && $this->teams_webhook_deprecated) {
            session()->flash('warning', trans('admin/settings/message.webhook.ms_teams_deprecation'));
        }
    }

    public function updated($field)
    {

        $this->validateOnly($field);

    }

    public function updatedWebhookSelected()
    {
        $this->webhook_name = $this->webhook_text[$this->webhook_selected]['name'];
        $this->webhook_icon = $this->webhook_text[$this->webhook_selected]['icon'];
        $this->webhook_placeholder = $this->webhook_text[$this->webhook_selected]['placeholder'];
        $this->webhook_endpoint = null;
        $this->webhook_link = $this->webhook_text[$this->webhook_selected]['link'];
        $this->webhook_test = $this->webhook_text[$this->webhook_selected]['test'];
        if ($this->webhook_selected != 'slack') {
            $this->isDisabled = '';
            $this->save_button = trans('general.save');
        }
        if ($this->webhook_selected == 'microsoft' || $this->webhook_selected == 'google') {
            $this->webhook_channel = '#NA';
        }
    }

    public function updatedwebhookEndpoint()
    {
        $this->teams_webhook_deprecated = ! Str::contains($this->webhook_endpoint, 'workflows');
    }

    private function isButtonDisabled()
    {
        if (empty($this->webhook_endpoint)) {
            $this->isDisabled = 'disabled';
            $this->save_button = trans('admin/settings/general.webhook_presave');
        }
        if (empty($this->webhook_channel)) {
            $this->isDisabled = 'disabled';
            $this->save_button = trans('admin/settings/general.webhook_presave');
        }
    }

    public function render()
    {
        $this->isButtonDisabled();

        return view('livewire.slack-settings-form');

    }

    public function testWebhook()
    {
        if ($fail = $this->guardWebhookEndpoint()) {
            return $fail;
        }

        $webhook = new Client([
            'base_url' => e($this->webhook_endpoint),
            'defaults' => [
                'exceptions' => false,
            ],
            'allow_redirects' => false,
        ]);

        $payload = json_encode(
            [
                'channel' => e($this->webhook_channel),
                'text' => trans('general.webhook_test_msg', ['app' => $this->webhook_name]),
                'username' => e($this->webhook_botname),
                'icon_emoji' => ':heart:',

            ]);

        try {
            $test = $webhook->post($this->webhook_endpoint, ['body' => $payload, 'headers' => ['Content-Type' => 'application/json']]);

            if (($test->getStatusCode() == 302) || ($test->getStatusCode() == 301)) {
                return session()->flash('error', trans('admin/settings/message.webhook.error_redirect', ['endpoint' => $this->webhook_endpoint]));
            }
            $this->isDisabled = '';
            $this->save_button = trans('general.save');

            return session()->flash('success', trans('admin/settings/message.webhook.success', ['webhook_name' => $this->webhook_name]));

        } catch (\Exception $e) {
            return $this->handleWebhookFailure($e);
        }

        return session()->flash('error', trans('admin/settings/message.webhook.error_misc'));

    }

    public function clearSettings()
    {

        if (Helper::isDemoMode()) {
            session()->flash('error', trans('general.feature_disabled'));
        } else {
            $this->webhook_endpoint = '';
            $this->webhook_channel = '';
            $this->webhook_botname = '';
            $this->setting->webhook_endpoint = '';
            $this->setting->webhook_channel = '';
            $this->setting->webhook_botname = '';

            $this->setting->save();

            session()->flash('success', trans('admin/settings/message.update.success'));
        }
    }

    public function submit()
    {
        if (Helper::isDemoMode()) {
            session()->flash('error', trans('general.feature_disabled'));
        } else {
            $this->validate();

            $this->setting->webhook_selected = $this->webhook_selected;
            $this->setting->webhook_endpoint = $this->webhook_endpoint;
            $this->setting->webhook_channel = $this->webhook_channel;
            $this->setting->webhook_botname = $this->webhook_botname;

            $this->setting->save();

            session()->flash('success', trans('admin/settings/message.update.success'));
        }

    }

    public function googleWebhookTest()
    {
        if ($fail = $this->guardWebhookEndpoint()) {
            return $fail;
        }

        $payload = [
            'text' => trans('general.webhook_test_msg', ['app' => $this->webhook_name]),
        ];

        try {
            $response = Http::withHeaders([
                'content-type' => 'application/json',
            ])->withOptions(['allow_redirects' => false])
                ->post($this->webhook_endpoint, $payload)
                ->throw();

            if (($response->getStatusCode() == 302) || ($response->getStatusCode() == 301)) {
                return session()->flash('error', trans('admin/settings/message.webhook.error_redirect', ['endpoint' => $this->webhook_endpoint]));
            }

            $this->isDisabled = '';
            $this->save_button = trans('general.save');

            return session()->flash('success', trans('admin/settings/message.webhook.success', ['webhook_name' => $this->webhook_name]));

        } catch (\Exception $e) {
            return $this->handleWebhookFailure($e);
        }
    }

    public function msTeamTestWebhook()
    {
        if ($fail = $this->guardWebhookEndpoint()) {
            return $fail;
        }

        try {

            if ($this->teams_webhook_deprecated) {
                // will use the deprecated webhook format
                $payload =
                    [
                        '@type' => 'MessageCard',
                        '@context' => 'http://schema.org/extensions',
                        'summary' => trans('mail.snipe_webhook_summary'),
                        'title' => trans('mail.snipe_webhook_test'),
                        'text' => trans('general.webhook_test_msg', ['app' => $this->webhook_name]),
                    ];
                $response = Http::withHeaders([
                    'content-type' => 'application/json',
                ])->withOptions(['allow_redirects' => false])
                    ->post($this->webhook_endpoint, $payload)
                    ->throw();
            } else {
                $notification = new TeamsNotification($this->webhook_endpoint);
                $message = trans('general.webhook_test_msg', ['app' => $this->webhook_name]);
                $notification->success()->sendMessage($message);

                $response = Http::withHeaders([
                    'content-type' => 'application/json',
                ])->withOptions(['allow_redirects' => false])
                    ->post($this->webhook_endpoint);
            }

            if (($response->getStatusCode() == 302) || ($response->getStatusCode() == 301)) {
                return session()->flash('error', trans('admin/settings/message.webhook.error_redirect', ['endpoint' => $this->webhook_endpoint]));
            }
            $this->isDisabled = '';
            $this->save_button = trans('general.save');

            return session()->flash('success', trans('admin/settings/message.webhook.success', ['webhook_name' => $this->webhook_name]));

        } catch (\Exception $e) {
            return $this->handleWebhookFailure($e);
        }

        return session()->flash('error', trans('admin/settings/message.webhook.error_misc'));
    }

    /**
     * Re-validate the currently-entered endpoint immediately before an
     * outbound request. This is defense-in-depth on top of the save-time
     * ExternalUrl rule: it stops a super-admin from typing an internal
     * URL, clicking Test, and having the server dial it before the value
     * is ever persisted. Returns a flash-response on failure, null on pass.
     */
    private function guardWebhookEndpoint()
    {
        $validator = Validator::make(
            ['webhook_endpoint' => $this->webhook_endpoint],
            ['webhook_endpoint' => ['required', 'url', new ExternalUrl]],
        );

        if ($validator->fails()) {
            $this->isDisabled = 'disabled';
            $this->save_button = trans('admin/settings/general.webhook_presave');

            return session()->flash('error', $validator->errors()->first('webhook_endpoint'));
        }

        return null;
    }

    /**
     * Uniform failure path for outbound webhook tests. The exception message
     * used to be a port-scanning oracle back when the endpoint field accepted
     * internal URLs; now that the ExternalUrl rule rejects those before we
     * ever dial anything, the connect-level error can only describe an
     * external host the admin explicitly typed, so we surface it back into
     * the flash. Full exception context still lands in the log for audit
     * and for cases where the flash message is too short to be useful (SSL
     * chain problems, proxy failures, etc.).
     */
    private function handleWebhookFailure(\Throwable $e)
    {
        Log::warning('Webhook test failed', [
            'endpoint' => $this->webhook_endpoint,
            'app' => $this->webhook_name,
            'exception' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        $this->isDisabled = 'disabled';
        $this->save_button = trans('admin/settings/general.webhook_presave');

        return session()->flash('error', trans('admin/settings/message.webhook.error', [
            'error_message' => $e->getMessage(),
            'app' => $this->webhook_name,
        ]));
    }
}
