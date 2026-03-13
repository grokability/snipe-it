<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixCustomFieldsRegexStuff extends Migration
{
    /**
     * Run the migrations.
     * "ANY" => "",
     * "ALPHA" => "alpha",
     * "EMAIL" => "email",
     * "DATE" => "date",
     *  "URL" => "url",
     * "NUMERIC" => "numeric",
     * "MAC" => "regex:/^[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}$/",
     * "IP" => "ip"
     *
     * @return void
     */
    public function up()
    {
        foreach (\App\Models\CustomField::all() as $custom_field) {

            // Handle alphanumeric
            if (stripos($custom_field->format, 'ALPHA') !== false) {
                $custom_field->format = 'alpha';

            // Numeric
            } elseif (stripos($custom_field->format, 'NUMERIC') !== false) {
                $custom_field->format = 'numeric';

            // IP
            } elseif (stripos($custom_field->format, 'IP') !== false) {
                $custom_field->format = 'ip';

            // Email
            } elseif (stripos($custom_field->format, 'EMAIL') !== false) {
                $custom_field->format = 'email';

            // MAC
            } elseif (stripos($custom_field->format, 'regex:/^[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}$/') !== false) {
                $custom_field->format = 'regex:/^[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}:[a-fA-F0-9]{2}$/';

            // Date
            } elseif (stripos($custom_field->format, 'DATE') !== false) {
                $custom_field->format = 'date';

            // URL
            } elseif (stripos($custom_field->format, 'URL') !== false) {
                $custom_field->format = 'url';

            // ANY
            } elseif (stripos($custom_field->format, 'ANY') !== false) {
                $custom_field->format = '';

            // Fix any custom regexes
            } else {
                $tmp_custom = str_replace('regex:/^', '', $custom_field->format);
                $tmp_custom = str_replace('$/', '', $tmp_custom);
                $custom_field->format = 'regex:/^'.$tmp_custom.'$/';
            }

            $custom_field->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
