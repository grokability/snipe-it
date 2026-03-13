<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use OneLogin\Saml2\IdPMetadataParser as OneLogin_Saml2_IdPMetadataParser;
use OneLogin\Saml2\Utils as OneLogin_Saml2_Utils;

/**
 * This handles validating and cleaning SAML settings provided by the user.
 *
 * @author Johnson Yi <jyi.dev@outlook.com>
 * @author Michael Pietsch <skywalker-11@mi-pietsch.de>
 *
 * @since 5.0.0
 */
class SettingsSamlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $setting = Setting::getSettings();
            if ($this->input('saml_enabled') == '1') {
                $idpMetadata = $this->input('saml_idp_metadata');
                if (! empty($idpMetadata)) {
                    try {
                        if (filter_var($idpMetadata, FILTER_VALIDATE_URL)) {
                            $metadataInfo = OneLogin_Saml2_IdPMetadataParser::parseRemoteXML($idpMetadata);
                        } else {
                            $metadataInfo = OneLogin_Saml2_IdPMetadataParser::parseXML($idpMetadata);
                        }
                    } catch (\Exception $e) {
                        $validator->errors()->add('saml_idp_metadata', trans('validation.url', ['attribute' => 'Metadata']));
                    }
                }
            }

            $was_custom_x509cert = strpos($setting->saml_custom_settings, 'sp_x509cert') !== false;

            $custom_x509cert = '';
            $custom_privateKey = '';
            $custom_x509certNew = '';
            if (! empty($this->input('saml_custom_settings'))) {
                $req_custom_settings = preg_split('/\r\n|\r|\n/', $this->input('saml_custom_settings', ''));
                $custom_settings = [];

                foreach ($req_custom_settings as $custom_setting) {
                    $split = explode('=', $custom_setting, 2);

                    if (count($split) == 2) {
                        $split[0] = trim($split[0]);
                        $split[1] = trim($split[1]);

                        if (! empty($split[0])) {
                            $custom_settings[] = implode('=', $split);
                        }
                        if ($split[0] == 'sp_x509cert') {
                            $custom_x509cert = $split[1];
                        } elseif ($split[0] == 'sp_privateKey') {
                            $custom_privateKey = $split[1];
                        } elseif ($split[0] == 'sp_x509certNew') {
                            //to prepare for Key rollover
                            $custom_x509certNew = $split[1];
                        }
                    }
                }

                $this->merge(['saml_custom_settings' => implode(PHP_EOL, $custom_settings).PHP_EOL]);
            }

            $cert_updated = false;
            if (! empty($custom_x509cert) && ! empty($custom_privateKey)) {
                // custom certificate and private key are defined
                $cert_updated = true;
                $x509 = openssl_x509_read($custom_x509cert);
                $pkey = openssl_pkey_get_private($custom_privateKey);
            } elseif ($this->input('saml_sp_regenerate_keypair') == '1' || ! $this->has('saml_sp_x509cert') || $was_custom_x509cert) {
                // key regeneration requested, no certificate defined yet or previous custom certicate was removed
                error_log('regen');
                $cert_updated = true;
                $dn = [
                    'countryName' => 'US',
                    'stateOrProvinceName' => 'N/A',
                    'localityName' => 'N/A',
                    'organizationName' => 'Snipe-IT',
                    'commonName' => 'Snipe-IT',
                ];

                $pkey = openssl_pkey_new([
                    'private_key_bits' => (int) config('app.saml_key_size'),
                    'private_key_type' => OPENSSL_KEYTYPE_RSA,
                ]);

                $csr = openssl_csr_new($dn, $pkey, ['digest_alg' => 'sha256']);

                if ($csr) {
                    $x509 = openssl_csr_sign($csr, null, $pkey, 3650, ['digest_alg' => 'sha256']);

                    openssl_x509_export($x509, $x509cert);
                    openssl_pkey_export($pkey, $privateKey);

                    $errors = [];
                    while (($error = openssl_error_string() !== false)) {
                        $errors[] = $error;
                    }

                    if (! (empty($x509cert) && empty($privateKey))) {
//                        $this->merge([
//                            'saml_sp_x509cert' => $x509cert,
//                            'saml_sp_privatekey' => $privateKey,
//                        ]);
                        $setting->saml_sp_x509cert = $x509cert;
                        $setting->saml_sp_privatekey = $privateKey;
                        $setting->save();

                    }
                } else {
                    $validator->errors()->add('saml_integration', 'openssl.cnf is missing/invalid');
                }
            }

            if ($custom_x509certNew) {
                $x509New = openssl_x509_read($custom_x509certNew);
                openssl_x509_export($x509New, $x509certNew);

                while (($error = openssl_error_string() !== false)) {
                    $errors[] = $error;
                }

                if (! empty($x509certNew)) {
//                    $this->merge([
//                        'saml_sp_x509certNew' => $x509certNew,
//                    ]);
                    $setting->saml_sp_x509certNew = $x509certNew;
                    $setting->save();
                }
            } else {
//                $this->merge([
//                    'saml_sp_x509certNew' => '',
//                ]);
                $setting->saml_sp_x509certNew = '';
                $setting->save();
            }


        });
    }
}
