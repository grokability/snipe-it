<?php

namespace Database\Factories;

use App\Models\CustomField;
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
                'element'   => 'checkbox',
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
                'element'   => 'radio',
            ];
        });
    }

}
