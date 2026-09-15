<?php

namespace Database\Factories;

use App\Models\CustomField;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomField::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->catchPhrase(),
            'format' => '',
            'element' => 'text',
            'auto_add_to_fieldsets' => '0',
            'show_in_requestable_list' => '0',
            'created_by' => User::factory()->superuser(),
        ];
    }

    public function imei()
    {
        return $this->state(function () {
            return [
                'name' => 'IMEI',
                'help_text' => 'The IMEI number for this device.',
                'format' => 'regex:/^[0-9]{15}$/',
            ];
        });
    }

    public function phone()
    {
        return $this->state(function () {
            return [
                'name' => 'Phone Number',
                'help_text' => 'Enter the phone number for this device.',
            ];
        });
    }

    public function ram()
    {
        return $this->state(function () {
            return [
                'name' => 'RAM',
                'help_text' => 'The amount of RAM this device has.',
            ];
        });
    }

    public function cpu()
    {
        return $this->state(function () {
            return [
                'name' => 'CPU',
                'help_text' => 'The speed of the processor on this device.',
                'show_in_requestable_list' => '1',
            ];
        });
    }

    public function macAddress()
    {
        return $this->state(function () {
            return [
                'name' => 'MAC Address',
                'format' => 'regex:/^([0-9a-fA-F]{2}[:-]){5}[0-9a-fA-F]{2}$/',
            ];
        });
    }

    public function ipAddress()
    {
        return $this->state(function () {
            return [
                'name' => 'IP Address',
                'help_text' => 'The last-known IP address for this device.',
            ];
        });
    }

    public function operatingSystem()
    {
        return $this->state(function () {
            return [
                'name' => 'Operating System',
                'help_text' => 'The operating system this device is running.',
            ];
        });
    }

    public function osVersion()
    {
        return $this->state(function () {
            return [
                'name' => 'OS Version',
                'help_text' => 'The OS version this device is running.',
            ];
        });
    }

    public function lastCheckIn()
    {
        return $this->state(function () {
            return [
                'name' => 'Last Check-in',
                'help_text' => 'The last time this device was seen by an inventory sync adapter.',
                'element' => 'datetime_picker',
                'format' => 'DATETIME',
            ];
        });
    }

    public function testEncrypted()
    {
        return $this->state(function () {
            return [
                'name' => 'Test Encrypted',
                'field_encrypted' => '1',
                'help_text' => 'This is a sample encrypted field.',
            ];
        });
    }

    public function encrypt()
    {
        return $this->state(function () {
            return [
                'field_encrypted' => '1',
            ];
        });
    }

    public function alpha()
    {
        return $this->state(function () {
            return [
                'format' => 'alpha',
            ];
        });
    }

    public function numeric()
    {
        return $this->state(function () {
            return [
                'format' => 'numeric',
            ];
        });
    }

    public function email()
    {
        return $this->state(function () {
            return [
                'format' => 'email',
            ];
        });
    }

    public function testCheckbox()
    {
        return $this->state(function () {
            return [
                'name' => 'Test Checkbox',
                'help_text' => 'This is a sample checkbox.',
                'field_values' => "One\r\nTwo\r\nThree",
                'element' => 'checkbox',
            ];
        });
    }

    public function testRadio()
    {
        return $this->state(function () {
            return [
                'name' => 'Test Radio',
                'help_text' => 'This is a sample radio.',
                'field_values' => "One\r\nTwo\r\nThree",
                'element' => 'radio',
            ];
        });
    }

    public function testDate()
    {
        return $this->state(function () {
            return [
                'name' => 'Sample Date',
                'help_text' => 'This shows a datepicker',
                'element' => 'date_picker',
                'format' => 'DATE',
            ];
        });
    }

    public function testDatetime()
    {
        return $this->state(function () {
            return [
                'name' => 'Sample Datetime',
                'help_text' => 'This shows a datetimepicker',
                'element' => 'datetime_picker',
                'format' => 'DATETIME',
            ];
        });
    }

    public function testMarkdownTextarea()
    {
        return $this->state(function () {
            return [
                'name' => 'Notes',
                'help_text' => 'Additional notes about this asset. Markdown is supported.',
                'element' => 'markdown-textarea',
            ];
        });
    }

    public function xss()
    {
        return $this->state(function () {
            return [
                'name' => '<img src=x onerror=alert(1)>',
                'help_text' => 'This is an intentional XSS seeded field so we can easily check for BS tables slips in escaping.',
                'show_in_requestable_list' => '0',
            ];
        });
    }
}
