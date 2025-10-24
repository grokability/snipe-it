<?php

namespace Tests\Unit;

use App\Models\Setting;
use PHPUnit\Framework\Attributes\Group;
use App\Models\Ldap;
use Tests\TestCase;

#[Group('ldap')]
class LdapTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function testConnect()
    {
        $this->settings->enableLdap();

        $ldap_connect = $this->getFunctionMock("App\\Models", "ldap_connect");
        $ldap_connect->expects($this->once())->willReturn('hello');

        $ldap_set_option = $this->getFunctionMock("App\\Models", "ldap_set_option");
        $ldap_set_option->expects($this->exactly(4));


        $blah = Ldap::connectToLdap();
        $this->assertEquals('hello',$blah,"LDAP_connect should return 'hello'");
    }

    // other test cases - with/without client-side certs?
    // with/without LDAP version 3?
    // with/without ignore cert validation?
    // test (and mock) ldap_start_tls() ?

    public function testBindAdmin()
    {
        $this->settings->enableLdap();
        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->once())->willReturn(true);
        $this->assertNull(Ldap::bindAdminToLdap("dummy"));
    }

    public function testBindBad()
    {
        $this->settings->enableLdap();
        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->once())->willReturn(false);
        $this->getFunctionMock("App\\Models","ldap_error")->expects($this->once())->willReturn("exception");
        $this->expectExceptionMessage("Could not bind to LDAP:");

        $this->assertNull(Ldap::bindAdminToLdap("dummy"));
    }
    // other test cases - test donked password?

    public function testAnonymousBind()
    {
        //todo - would be nice to introspect somehow to make sure the right parameters were passed?
        $this->settings->enableAnonymousLdap();
        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->once())->willReturn(true);
        $this->assertNull(Ldap::bindAdminToLdap("dummy"));
    }

    public function testBadAnonymousBind()
    {
        $this->settings->enableAnonymousLdap();
        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->once())->willReturn(false);
        $this->getFunctionMock("App\\Models","ldap_error")->expects($this->once())->willReturn("exception");
        $this->expectExceptionMessage("Could not bind to LDAP:");

        $this->assertNull(Ldap::bindAdminToLdap("dummy"));
    }

    public function testBadEncryptedPassword()
    {
        $this->settings->enableBadPasswordLdap();

        $this->expectExceptionMessage("Your app key has changed");
        $this->assertNull(Ldap::bindAdminToLdap("dummy"));
    }

    public function testFindAndBind()
    {
        $this->settings->enableLdap();

        $ldap_connect = $this->getFunctionMock("App\\Models", "ldap_connect");
        $ldap_connect->expects($this->exactly(3))->willReturn('hello');

        $ldap_set_option = $this->getFunctionMock("App\\Models", "ldap_set_option");
        $ldap_set_option->expects($this->exactly(12));

        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->exactly(2))->willReturn(true);

        $this->getFunctionMock("App\\Models", "ldap_search")->expects($this->exactly(1))->willReturn(true);

        $this->getFunctionMock("App\\Models", "ldap_first_entry")->expects($this->exactly(1))->willReturn(true);
        $this->getFunctionMock("App\\Models", "ldap_unbind")->expects($this->exactly(2));
        $this->getFunctionMock("App\\Models", "ldap_count_entries")->expects($this->once())->willReturn(1);
        $this->getFunctionMock("App\\Models", "ldap_get_dn")->expects($this->once())->willReturn('dn=FirstName Surname,ou=Org,dc=example,dc=com');


        $this->getFunctionMock("App\\Models", "ldap_get_attributes")->expects($this->exactly(1))->willReturn(
            [
                "count" => 1,
                0 => [
                    'sn' => 'Surname',
                    'firstname' => 'FirstName'
                ]
            ]
        );

        $results = Ldap::findAndBindUserLdap("username","password");
        $this->assertEqualsCanonicalizing(["count" =>1,0 =>['sn' => 'Surname','firstname' => 'FirstName']],$results);
    }

    public function testFindAndBindBadPassword()
    {
        $this->settings->enableLdap();

        $ldap_connect = $this->getFunctionMock("App\\Models", "ldap_connect");
        $ldap_connect->expects($this->exactly(3))->willReturn('hello');

        $ldap_set_option = $this->getFunctionMock("App\\Models", "ldap_set_option");
        $ldap_set_option->expects($this->exactly(12));

        //
        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->exactly(4))->willReturn(
            true, /* initial admin connection for 'fast path' */
            false, /* the actual login for the user */
            false, /* the direct login for the user binding-as-themselves in the legacy path */
            true /* the admin login afterwards (which is weird and doesn't make sense)  */
        );
        $this->getFunctionMock("App\\Models", "ldap_unbind")->expects($this->exactly(2));
        $this->getFunctionMock("App\\Models", "ldap_error")->expects($this->never())->willReturn("exception");
        $this->getFunctionMock("App\\Models", "ldap_search")->expects($this->exactly(1))->willReturn(false); //uhm?
        $this->getFunctionMock("App\\Models", "ldap_count_entries")->expects($this->exactly(1))->willReturn(1);
        $this->getFunctionMock("App\\Models", "ldap_first_entry")->expects($this->exactly(1))->willReturn(true);
        $this->getFunctionMock("App\\Models", "ldap_get_attributes")->expects($this->exactly(1))->willReturn(1);
        $this->getFunctionMock("App\\Models", "ldap_get_dn")->expects($this->exactly(1))->willReturn('dn=FirstName Surname,ou=Org,dc=example,dc=com');

//        $this->getFunctionMock("App\\Models","ldap_error")->expects($this->once())->willReturn("exception");


//        $this->expectExceptionMessage("exception");
        $results = Ldap::findAndBindUserLdap("username","password");
        $this->assertFalse($results);
    }

    public function testFindAndBindCannotFindSelf()
    {
        $this->settings->enableLdap();

        $ldap_connect = $this->getFunctionMock("App\\Models", "ldap_connect");
        $ldap_connect->expects($this->exactly(2))->willReturn('hello');

        $ldap_set_option = $this->getFunctionMock("App\\Models", "ldap_set_option");
        $ldap_set_option->expects($this->exactly(8));

        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->exactly(2))->willReturn(true); //I think this is OK

        $this->getFunctionMock("App\\Models", "ldap_search")->expects($this->exactly(2))->willReturn(false); //uhm?
        $this->getFunctionMock("App\\Models", "ldap_unbind")->expects($this->once());
        $this->getFunctionMock("App\\Models", "ldap_count_entries")->expects($this->once())->willReturn(0);


        $this->expectExceptionMessage("Could not search LDAP:");
        $results = Ldap::findAndBindUserLdap("username","password");
        $this->assertFalse($results);
    }

    //maybe should do an AD test as well?

    public function testFindLdapUsers()
    {
        $this->settings->enableLdap();

        $ldap_connect = $this->getFunctionMock("App\\Models", "ldap_connect");
        $ldap_connect->expects($this->once())->willReturn('hello');

        $ldap_set_option = $this->getFunctionMock("App\\Models", "ldap_set_option");
        $ldap_set_option->expects($this->exactly(4));

        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->once())->willReturn(true);

        $this->getFunctionMock("App\\Models", "ldap_search")->expects($this->once())->willReturn(["stuff"]);

        $this->getFunctionMock("App\\Models", "ldap_parse_result")->expects($this->once())->willReturn(true);

        $this->getFunctionMock("App\\Models", "ldap_get_entries")->expects($this->once())->willReturn(["count" => 1]);

        $results = Ldap::findLdapUsers();

        $this->assertEqualsCanonicalizing(["count" => 1], $results);
    }

    public function testFindLdapUsersPaginated()
    {
        $this->settings->enableLdap();

        $ldap_connect = $this->getFunctionMock("App\\Models", "ldap_connect");
        $ldap_connect->expects($this->once())->willReturn('hello');

        $ldap_set_option = $this->getFunctionMock("App\\Models", "ldap_set_option");
        $ldap_set_option->expects($this->exactly(4));

        $this->getFunctionMock("App\\Models", "ldap_bind")->expects($this->once())->willReturn(true);

        $this->getFunctionMock("App\\Models", "ldap_search")->expects($this->exactly(2))->willReturn(["stuff"]);

        $this->getFunctionMock("App\\Models", "ldap_parse_result")->expects($this->exactly(2))->willReturnCallback(
            function ($ldapconn, $search_results, $errcode , $matcheddn , $errmsg , $referrals, &$controls) {
                static $count = 0;
                if($count == 0) {
                    $count++;
                    $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'] = "cookie";
                    return ["count" => 1];
                } else {
                    $controls = [];
                    return ["count" => 1];
                }

            }
        );

        $this->getFunctionMock("App\\Models", "ldap_get_entries")->expects($this->exactly(2))->willReturn(["count" => 1]);

        $results = Ldap::findLdapUsers();

        $this->assertEqualsCanonicalizing(["count" => 2], $results);
    }

    public function testNonexistentTLSFile()
    {
        $this->settings->enableLdap()->set(['ldap_client_tls_cert' => 'SAMPLE CERT TEXT']);
        $certfile = Setting::get_client_side_cert_path();
        $this->assertStringEqualsFile($certfile, 'SAMPLE CERT TEXT');
    }

    public function testStaleTLSFile()
    {
        file_put_contents(Setting::get_client_side_cert_path(), 'STALE CERT FILE');
        sleep(1); // FIXME - this is going to slow down tests
        $this->settings->enableLdap()->set(['ldap_client_tls_cert' => 'SAMPLE CERT TEXT']);
        $certfile = Setting::get_client_side_cert_path();
        $this->assertStringEqualsFile($certfile, 'SAMPLE CERT TEXT');
    }

    public function testFreshTLSFile()
    {
        $this->settings->enableLdap()->set(['ldap_client_tls_cert' => 'SAMPLE CERT TEXT']);
        $client_side_cert_path = Setting::get_client_side_cert_path();
        file_put_contents($client_side_cert_path, 'WEIRDLY UPDATED CERT FILE');
        clearstatcache();
        //the system should respect our cache-file, since the settings haven't been updated
        $possibly_recached_cert_file = Setting::get_client_side_cert_path(); //this should *NOT* re-cache from the Settings
        $this->assertStringEqualsFile($possibly_recached_cert_file, 'WEIRDLY UPDATED CERT FILE');
    }

}
