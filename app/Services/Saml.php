<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use OneLogin\Saml2\Auth as OneLogin_Saml2_Auth;
use OneLogin\Saml2\IdPMetadataParser as OneLogin_Saml2_IdPMetadataParser;
use OneLogin\Saml2\Settings as OneLogin_Saml2_Settings;
use OneLogin\Saml2\Utils as OneLogin_Saml2_Utils;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

/**
 * SAML Singleton that builds the settings and loads the onelogin/php-saml library.
 *
 * @author Johnson Yi <jyi.dev@outlook.com>
 *
 * @since 5.0.0
 */
class Saml
{
    public const DATA_SESSION_KEY = '_samlData';

    /**
     * OneLogin_Saml2_Auth instance.
     *
     * @var OneLogin\Saml2\Auth
     */
    private $_auth;

    /**
     * if SAML is enabled and has valid settings.
     *
     * @var bool
     */
    private $_enabled = false;

    /**
     * Settings to be passed to OneLogin_Saml2_Auth.
     *
     * @var array
     */
    private $_settings = [];

    /**
     * User attributes data.
     *
     * @var array
     */
    private $_attributes = [];

    /**
     * User attributes data with FriendlyName index.
     *
     * @var array
     */
    private $_attributesWithFriendlyName = [];

    /**
     * NameID
     *
     * @var string
     */
    private $_nameid;

    /**
     * NameID Format
     *
     * @var string
     */
    private $_nameidFormat;

    /**
     * NameID NameQualifier
     *
     * @var string
     */
    private $_nameidNameQualifier;

    /**
     * NameID SP NameQualifier
     *
     * @var string
     */
    private $_nameidSPNameQualifier;

    /**
     * If user is authenticated.
     *
     * @var bool
     */
    private $_authenticated = false;

    /**
     * SessionIndex. When the user is logged, this stored it
     * from the AuthnStatement of the SAML Response
     *
     * @var string
     */
    private $_sessionIndex;

    /**
     * SessionNotOnOrAfter. When the user is logged, this stored it
     * from the AuthnStatement of the SAML Response
     *
     * @var int|null
     */
    private $_sessionExpiration;

    /**
     * Initializes the SAML service and builds the OneLogin_Saml2_Auth instance.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @throws Exception
     * @throws Error
     */
    public function __construct()
    {
        $this->loadSettings();

        if ($this->isEnabled()) {
            $this->loadDataFromSession();
        } else {
            $this->clearData();
        }

        try {
            $this->_auth = new OneLogin_Saml2_Auth($this->_settings);
        } catch (Exception $e) {
            if ( $this->isEnabled() ) { // $this->loadSettings() initializes this to true if SAML is enabled by settings.
                Log::warning('Trying OneLogin_Saml2_Auth failed. Setting SAML enabled to false. OneLogin_Saml2_Auth error message is: '.  $e->getMessage());
            }
            $this->_enabled = false;
        }
    }

    /**
     * Builds settings from Snipe-IT for OneLogin_Saml2_Auth.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     * @author Michael Pietsch <skywalker-11@mi-pietsch.de>
     *
     * @since 5.0.0
     *
     * @return void
     */
    private function loadSettings()
    {
        $setting = Setting::getSettings();
        $settings = [];

        $this->_enabled = $setting->saml_enabled == '1';

        if ($this->isEnabled()) {
            //Let onelogin/php-saml know to use 'X-Forwarded-*' headers if it is from a trusted proxy
            OneLogin_Saml2_Utils::setProxyVars(request()->isFromTrustedProxy());

            data_set($settings, 'sp.entityId', config('app.url'));
            data_set($settings, 'baseurl', config('app.url') . '/saml');
            data_set($settings, 'sp.assertionConsumerService.url', route('saml.acs'));
            data_set($settings, 'sp.singleLogoutService.url', route('saml.sls'));
            data_set($settings, 'sp.x509cert', $setting->saml_sp_x509cert);
            data_set($settings, 'sp.privateKey', $setting->saml_sp_privatekey);
            if (! empty($setting->saml_sp_x509certNew)) {
                data_set($settings, 'sp.x509certNew', $setting->saml_sp_x509certNew);
            } else {
                data_set($settings, 'sp.x509certNew', '');
            }

            if (! empty(data_get($settings, 'sp.privateKey'))) {
                data_set($settings, 'security.logoutRequestSigned', true);
                data_set($settings, 'security.logoutResponseSigned', true);
            }

            $idpMetadata = $setting->saml_idp_metadata;
            if (! empty($idpMetadata)) {
                $updatedAt = $setting->updated_at->timestamp;
                $metadataCache = Cache::get('saml_idp_metadata_cache');
                try {
                    $url = null;
                    $metadataInfo = null;

                    if (empty($metadataCache) || $metadataCache['updated_at'] != $updatedAt) {
                        if (filter_var($idpMetadata, FILTER_VALIDATE_URL)) {
                            $url = $idpMetadata;
                            $metadataInfo = OneLogin_Saml2_IdPMetadataParser::parseRemoteXML($idpMetadata);
                        } else {
                            $metadataInfo = OneLogin_Saml2_IdPMetadataParser::parseXML($idpMetadata);
                        }

                        Cache::put('saml_idp_metadata_cache', [
                            'updated_at' => $updatedAt,
                            'url' => $url,
                            'metadata_info' => $metadataInfo,
                        ]);
                    } else {
                        $metadataInfo = $metadataCache['metadata_info'];
                    }

                    $settings = OneLogin_Saml2_IdPMetadataParser::injectIntoSettings($settings, $metadataInfo);
                } catch (Exception $e) {
                }
            }

            $custom_settings = preg_split('/\r\n|\r|\n/', $setting->saml_custom_settings ?? '');
            if ($custom_settings) {
                foreach ($custom_settings as $custom_setting) {
                    $split = explode('=', $custom_setting, 2);

                    if (count($split) == 2) {
                        $boolValue = filter_var($split[1], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                        if (! is_null($boolValue)) {
                            $split[1] = $boolValue;
                        }

                        data_set($settings, $split[0], $split[1]);
                    }
                }
            }
            $this->_settings = $settings;
        }
    }

    /**
     * Load SAML data from Session.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return void
     */
    private function loadDataFromSession()
    {
        $samlData = collect(session(self::DATA_SESSION_KEY));
        $this->_authenticated = ! $samlData->isEmpty();
        $this->_nameid = $samlData->get('nameId');
        $this->_nameidFormat = $samlData->get('nameIdFormat');
        $this->_nameidNameQualifier = $samlData->get('nameIdNameQualifier');
        $this->_nameidSPNameQualifier = $samlData->get('nameIdSPNameQualifier');
        $this->_sessionIndex = $samlData->get('sessionIndex');
        $this->_sessionExpiration = $samlData->get('sessionExpiration');
        $this->_attributes = $samlData->get('attributes');
        $this->_attributesWithFriendlyName = $samlData->get('attributesWithFriendlyName');
    }

    /**
     * Save SAML data to Session.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @param string $data
     *
     * @return void
     */
    private function saveDataToSession($data)
    {
        return session([self::DATA_SESSION_KEY => $data]);
    }

    /**
     * Check to see if SAML is enabled and has valid settings.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     * Clear SAML data from session.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return void
     */
    public function clearData()
    {
        Session::forget(self::DATA_SESSION_KEY);
        $this->loadDataFromSession();
    }

    /**
     * Find user from SAML data.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @param string $data
     *
     * @return \App\Models\User
     */
    public function samlLogin($data)
    {
        $this->saveDataToSession($data);
        $this->loadDataFromSession();
        $username = $this->getUsername();
        return User::where('username', '=', $username)->whereNull('deleted_at')->where('activated', '=', '1')->first();
    }

    /**
     * Returns the OneLogin_Saml2_Auth instance.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return OneLogin\Saml2\Auth
     */
    public function getAuth()
    {
        if (! $this->isEnabled()) {
            throw new HttpException(403, 'SAML not enabled.');
        }

        return $this->_auth;
    }

    /**
     * Get a setting.
     *
     * @param string|array|int $key
     * @param mixed $default
     *
     * @return mixed
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     */
    public function getSetting($key, $default = null)
    {
        return data_get($this->_settings, $key, $default);
    }

    /**
     * Gets the SP metadata. The XML representation.
     *
     * @param bool $alwaysPublishEncryptionCert When 'true', the returned
     * metadata will always include an 'encryption' KeyDescriptor. Otherwise,
     * the 'encryption' KeyDescriptor will only be included if
     * $advancedSettings['security']['wantNameIdEncrypted'] or
     * $advancedSettings['security']['wantAssertionsEncrypted'] are enabled.
     * @param int|null      $validUntil    Metadata's valid time
     * @param int|null      $cacheDuration Duration of the cache in seconds
     *
     * @return string  SP metadata (xml)
     */
    public function getSPMetadata($alwaysPublishEncryptionCert = false, $validUntil = null, $cacheDuration = null)
    {
        try {
            $settings = new OneLogin_Saml2_Settings($this->_settings, true);
            $metadata = $settings->getSPMetadata($alwaysPublishEncryptionCert, $validUntil, $cacheDuration);

            return $metadata;
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Extract data from SAML Response.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return array
     */
    public function extractData()
    {
        $auth = $this->getAuth();

        return [
            'attributes' => $auth->getAttributes(),
            'attributesWithFriendlyName' => $auth->getAttributesWithFriendlyName(),
            'nameId' => $auth->getNameId(),
            'nameIdFormat' => $auth->getNameIdFormat(),
            'nameIdNameQualifier' => $auth->getNameIdNameQualifier(),
            'nameIdSPNameQualifier' => $auth->getNameIdSPNameQualifier(),
            'sessionIndex' => $auth->getSessionIndex(),
            'sessionExpiration' => $auth->getSessionExpiration(),
            'nonce' => $auth->getLastAssertionId(),
            'assertionNotOnOrAfter' => $auth->getLastAssertionNotOnOrAfter(),
        ];
    }

    /**
     * Checks if the user is authenticated or not.
     *
     * @return bool  True if the user is authenticated
     */
    public function isAuthenticated()
    {
        return $this->_authenticated;
    }

    /**
     * Returns the set of SAML attributes.
     *
     * @return array  Attributes of the user.
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * Returns the set of SAML attributes indexed by FriendlyName
     *
     * @return array  Attributes of the user.
     */
    public function getAttributesWithFriendlyName()
    {
        return $this->_attributesWithFriendlyName;
    }

    /**
     * Returns the nameID
     *
     * @return string  The nameID of the assertion
     */
    public function getNameId()
    {
        return $this->_nameid;
    }

    /**
     * Returns the nameID Format
     *
     * @return string  The nameID Format of the assertion
     */
    public function getNameIdFormat()
    {
        return $this->_nameidFormat;
    }

    /**
     * Returns the nameID NameQualifier
     *
     * @return string  The nameID NameQualifier of the assertion
     */
    public function getNameIdNameQualifier()
    {
        return $this->_nameidNameQualifier;
    }

    /**
     * Returns the nameID SP NameQualifier
     *
     * @return string  The nameID SP NameQualifier of the assertion
     */
    public function getNameIdSPNameQualifier()
    {
        return $this->_nameidSPNameQualifier;
    }

    /**
     * Returns the SessionIndex
     *
     * @return string|null  The SessionIndex of the assertion
     */
    public function getSessionIndex()
    {
        return $this->_sessionIndex;
    }

    /**
     * Returns the SessionNotOnOrAfter
     *
     * @return int|null  The SessionNotOnOrAfter of the assertion
     */
    public function getSessionExpiration()
    {
        return $this->_sessionExpiration;
    }

    /**
     * Returns the correct username from SAML Response.
     *
     * @author Johnson Yi <jyi.dev@outlook.com>
     *
     * @since 5.0.0
     *
     * @return string
     */
    public function getUsername()
    {
        $setting = Setting::getSettings();
        $username = $this->getNameId();

        if (! empty($setting->saml_attr_mapping_username)) {
            $attributes = $this->getAttributes();

            if (isset($attributes[$setting->saml_attr_mapping_username])) {
                $username = $attributes[$setting->saml_attr_mapping_username][0];
            }
        }

        return $username;
    }
}
