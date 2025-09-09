<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Crypt;
use App\Models\Ldap;

/**
 * Check if a given ip is in a network
 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return boolean true if the ip is in this range / false if not.
 */
function ip_in_range( $ip, $range ) {
	if ( strpos( $range, '/' ) == false ) {
		$range .= '/32';
	}
	// $range is in IP/CIDR format eg 127.0.0.1/24
	list( $range, $netmask ) = explode( '/', $range, 2 );
	$range_decimal = ip2long( $range );
	$ip_decimal = ip2long( $ip );
	$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
	$netmask_decimal = ~ $wildcard_decimal;
	return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
}
// NOTE - this function was shamelessly stolen from this gist: https://gist.github.com/tott/7684443

/**
 * Ensure LDAP filters are parentheses-wrapped
 */
function parenthesized_filter($filter)
{
    if(substr($filter,0,1) == "(" ) {
        return $filter;
    } else {
        return "(".$filter.")";
    }

}

class LdapTroubleshooter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:troubleshoot
                            {--ldap-search : Output an ldapsearch command-line for testing your LDAP config}
                            {--force : Skip the interactive yes/no prompt for confirmation}
                            {--debug : Include debugging output (verbose)}
                            {--trace : Include extremely verbose LDAP trace output}
                            {--timeout=15 : Timeout for LDAP Bind operations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs a series of non-destructive LDAP commands to help try and determine correct LDAP settings for your environment.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Output something *only* if debug is enabled
     * 
     * @return void
     */
    public function debugout($string)
    {
        if($this->option('debug')) {
            $this->line($string);
        }
    }

    /**
     * Clean the results from ldap_get_entries into something useful
     * @param array $array
     * @return array
     */
    public function ldap_results_cleaner ($array) {
        $cleaned = [];
        for($i = 0; $i < $array['count']; $i++) {
            $row = $array[$i];
            $clean_row = [];
            foreach($row AS $key => $val ) {
                $this->debugout("Key is: ".$key);
                if($key == "count" || is_int($key) || $key == "dn") {
                    $this->debugout(" and we're gonna skip it\n");
                    continue;
                }
                $this->debugout(" And that seems fine.\n");
                if(array_key_exists('count',$val)) {
                    if($val['count'] == 1) {
                        $clean_row[$key] = $val[0];
                    } else {
                        unset($val['count']); //these counts are annoying
                        $elements = [];
                        foreach($val as $entry) {
                            if(isset($ldap_constants[$entry])) {
                                $elements[] = $ldap_constants[$entry];
                            } else {
                                $elements[] = $entry;
                            }
                        }
                        $clean_row[$key] = $elements;
                    }
                } else {
                    $clean_row[$key] = $val;
                }
            }
            $cleaned[$i] = $clean_row;
        }
        return $cleaned;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if($this->option('trace')) {
    	    ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
        }

        $settings = Setting::getSettings();
        $this->settings = $settings;
        if($this->option('ldap-search')) {
            if(!$this->option('force')) {
                $confirmation = $this->confirm('WARNING: This command will display your LDAP password on your terminal. Are you sure this is ok?');
                if(!$confirmation) {
                    $this->error('ABORTING');
                    exit(-1);
                }
            }
            $output = [];
            if($settings->ldap_server_cert_ignore) {
                $this->line("# Ignoring server certificate validity");
                $output[] = "LDAPTLS_REQCERT=never";
            }
            if($settings->ldap_client_tls_cert && $settings->ldap_client_tls_key) {
                $this->line("# Adding LDAP Client Certificate and Key");
                $output[] = "LDAPTLS_CERT=storage/ldap_client_tls.cert";
                $output[] = "LDAPTLS_KEY=storage/ldap_client_tls.key";
            }
            $output[] = "ldapsearch";
            $output[] = "-H ".$settings->ldap_server;
            $output[] = "-x";
            $output[] = "-b ".escapeshellarg($settings->ldap_basedn);
            $output[] = "-D ".escapeshellarg($settings->ldap_uname);

            try {
                $w = Crypt::Decrypt($settings->ldap_pword);
            } catch (\Exception $e) {
                $this->warn("Could not decrypt password. This usually means an LDAP password was not set or the APP_KEY was changed since the LDAP pasword was last saved.  Aborting.");
                exit(0);
            }

            $output[] = "-w ". escapeshellarg($w);
            $output[] = escapeshellarg(parenthesized_filter($settings->ldap_filter));
            if($settings->ldap_tls) {
                $this->line("# adding STARTTLS option");
                $output[] = "-Z";
            }
            $output[] = "-v";
            $this->line("\n");
            $this->line(implode(" \\\n",$output));
            exit(0);
        }

        //PHP Version check for warning
        $php_version = phpversion();
        list($major, $minor, $patch) = explode('.', $php_version);
        if (
            $major < 8 ||
            ($major == 8 && $minor < 3) ||
            ($major == 8 && $minor == 3 && $patch < 21) ||
            ($major == 8 && $minor == 4 && $patch < 7)
        ) {
            $this->warn("PHP Version: $php_version WARNING - Versions before 8.3.21 or 8.4.7 will return INCONSISTENT results!");
            if (!$this->confirm("Are you sure you wish to continue?")) {
                $this->warn("ABORTING");
                exit(-1);
            }
        }

        if(!$this->option('force')) {
            $confirmation = $this->confirm('WARNING: This command will make several attempts to connect to your LDAP server. Are you sure this is ok?');
            if(!$confirmation) {
                $this->error('ABORTING');
                exit(-1);
            }
        }
        //$this->line(print_r($settings,true));
        $this->line("STAGE 1: Checking settings");
        if(!$settings->ldap_enabled) {
            $this->error("WARNING: Snipe-IT's LDAP setting is not turned on. (That may be OK if you're still trying to figure out settings)");
        }

        $ldap_conn = false;
        try {
            $ldap_conn = ldap_connect($settings->ldap_server);
        } catch (Exception $e) {
            $this->error("WARNING: Exception caught when executing 'ldap_connect()' - ".$e->getMessage().". We will try to guess.");
        }

        if(!$ldap_conn) {
            $this->error("WARNING: LDAP Server setting of: ".$settings->ldap_server." cannot be parsed. We will try to guess.");
            //exit(-1);
        }
        //since we never use $ldap_conn again, we don't have to ldap_unbind() it (it's not even connected, tbh - that only happens at bind-time)

        $parsed = parse_url($settings->ldap_server);

        if(@$parsed['scheme'] != 'ldap' && @$parsed['scheme'] != 'ldaps') {
            $this->error("WARNING: LDAP URL Scheme of '".@$parsed['scheme']."' is probably incorrect; should usually be ldap or ldaps");
        }

        if(!@$parsed['host']) {
            $this->error("ERROR: Cannot determine hostname or IP from ldap URL: ".$settings->ldap_server.". ABORTING.");
            exit(-1);
        } else {
            $this->info("Determined LDAP hostname to be: ".$parsed['host']);
        }

        $raw_ips = [];

        if (inet_pton($parsed['host']) !== false) {
            $this->line($parsed['host'] . " already looks like an address; skipping DNS lookup");
            $raw_ips[] = $parsed['host'];
        } else {
            $this->line("Performing DNS lookup of: " . $parsed['host']);
            $ips = dns_get_record($parsed['host']);

            //$this->info("Host IP is: ".print_r($ips,true));

            if (!$ips || count($ips) == 0) {
                $this->error("ERROR: DNS lookup of host: " . $parsed['host'] . " has failed. ABORTING.");
                exit(-1);
            }
            $this->debugout("IP's? " . print_r($ips, true));
            foreach ($ips as $ip) {
                if (!isset($ip['ip'])) {
                    continue;
                }
                $raw_ips[] = $ip['ip'];
            }
        }
        foreach ($raw_ips as $ip) {
            if ($ip == "127.0.0.1") {
                $this->error("WARNING: Using the localhost IP as the LDAP server. This is usually wrong");
            }
            if (ip_in_range($ip, '10.0.0.0/8') || ip_in_range($ip, '192.168.0.0/16') || ip_in_range($ip, '172.16.0.0/12')) {
                $this->error("WARNING: Using an RFC1918 Private address for LDAP server. This may be correct, but it can be a problem if your Snipe-IT instance is not hosted on your private network");
            }
        }

        $this->line("STAGE 2: Checking basic network connectivity");
        $ports = [636, 389];
        if(@$parsed['port'] && !in_array($parsed['port'],$ports)) {
            $ports[] = $parsed['port'];
        }

        $open_ports=[];
        foreach($ports as $port ) {
            $errno = 0;
            $errstr = '';
            $timeout = 30.0;
            $result = '';
            $this->line("Attempting to connect to port: " . $port . " - may take up to $timeout seconds");
            try {
                $result = fsockopen($parsed['host'], $port, $errno, $errstr, 30.0);
            } catch(Exception $e) {
                $this->error("Exception: ".$e->getMessage());
            }
            if($result) {
                $this->info("Success!");
                $open_ports[] = $port;
            } else {
                $this->error("WARNING: Cannot connect to port: $port - $errstr ($errno)");
            }
        }

        if(count($open_ports) == 0) {
            $this->error("ERROR - no open ports. ABORTING.");
            exit(-1);
        }

        $this->line("STAGE 3: Determine encryption algorithm, if any");

        $ldap_urls = []; // [url, cert-check?, start_tls?]
        $pretty_ldap_urls = [];
        foreach($open_ports as $port) {
            $this->line("Trying TLS first for port $port");
            $ldap_url = "ldaps://".$parsed['host'].":$port";
            if($this->test_anonymous_bind($ldap_url)) {
                $this->info("Anonymous bind succesful to $ldap_url!");
                $ldap_urls[] = [ $ldap_url, true, false ];
                $pretty_ldap_urls[] = [$ldap_url, "enabled", "n/a (no)"];
                continue; // TODO - lots of copypasta in these if(test_anonymous_bind()) routines...
            } else {
                $this->error("WARNING: Failed to bind to $ldap_url - trying without certificate checks.");
            }

            if($this->test_anonymous_bind($ldap_url, false)) {
                $this->info("Anonymous bind successful to $ldap_url with certificate-checks disabled");
                $ldap_urls[] = [$ldap_url, false, false];
                $pretty_ldap_urls[] = [$ldap_url, "DISABLED", "n/a (no)"];
                continue;
            } else {
                $this->error("WARNING: Failed to bind to $ldap_url with certificate checks disabled. Trying unencrypted with STARTTLS");
            }

            // now switching to ldap:// URL's from ldaps://
            $ldap_url = "ldap://".$parsed['host'].":$port";

            if($this->test_anonymous_bind($ldap_url, true, true)) {
                $this->info("Plain connection to $ldap_url with STARTTLS succesful!");
                $ldap_urls[] = [ $ldap_url, true, true ];
                $pretty_ldap_urls[] = [$ldap_url, "enabled", "STARTTLS ENABLED"];
                continue;
            } else {
                $this->error("WARNING: Failed to bind to $ldap_url with STARTTLS enabled. Trying without certificate checks.");
            }

            if ($this->test_anonymous_bind($ldap_url, false, true)) {
                $this->info("Plain connection to $ldap_url with STARTTLS and cert checks *disabled* successful!");
                $ldap_urls[] = [$ldap_url, false, true];
                $pretty_ldap_urls[] = [$ldap_url, "DISABLED", "STARTTLS ENABLED"];
                continue;
            } else {
                $this->error("WARNING: Failed to bind to $ldap_url with STARTTLS enabled, and cert checks disabled. Trying without STARTTLS");
            }

            if($this->test_anonymous_bind($ldap_url)) {
                $this->info("Plain connection to $ldap_url succesful!");
                $ldap_urls[] = [ $ldap_url, true, false ];
                $pretty_ldap_urls[] = [$ldap_url, "n/a", "starttls disabled"];
                continue;
            } else {
                $this->error("WARNING: Failed to bind to $ldap_url. Giving up on port $port");
            }
        }

        $this->debugout(print_r($ldap_urls,true));

        if(count($ldap_urls) > 0 ) {
            $this->debugout("Found working LDAP URL's: ");
            foreach($ldap_urls as $ldap_url) { // TODO maybe do this as a $this->table() instead?
                $this->debugout("LDAP URL: " . $ldap_url[0]);
                $this->debugout($ldap_url[0] . ($ldap_url[1] ? " certificate checks enabled" : " certificate checks disabled") . ($ldap_url[2] ? " STARTTLS Enabled " : " STARTTLS Disabled"));
            }
            $this->table(["URL", "Cert Checks?", "STARTTLS?"], $pretty_ldap_urls);
        } else {
            $this->error("ERROR - no valid LDAP URL's available - ABORTING");
            exit(1);
        }

        $this->line("STAGE 4: Test Administrative Bind for LDAP Sync");
        foreach($ldap_urls AS $ldap_url) {
            try {
                $w = Crypt::Decrypt($settings->ldap_pword);
            } catch (\Exception $e) {
                $this->warn("Could not decrypt password. This usually means an LDAP password was not set or the APP_KEY was changed since the LDAP pasword was last saved.  Aborting.");
                exit(0);
            }
            $this->test_authed_bind($ldap_url[0], $ldap_url[1], $ldap_url[2], $settings->ldap_uname, $w);
        }

        $this->line("STAGE 5: Test BaseDN");
        //grab all LDAP_ constants and fill up a reversed array mapping from weird LDAP dotted-strings to (Constant Name)
        $all_defined_constants = get_defined_constants();
        $ldap_constants = [];
        foreach($all_defined_constants AS $key => $val) {
            if(starts_with($key,"LDAP_") && is_string($val)) {
                $ldap_constants[$val] = $key; // INVERT the meaning here!
            }
        }
        $this->debugout("LDAP constants are: ".print_r($ldap_constants,true));

        foreach($ldap_urls AS $ldap_url) {
            try {
                $w = Crypt::Decrypt($settings->ldap_pword);
            } catch (\Exception $e) {
                $this->warn("Could not decrypt password. This usually means an LDAP password was not set or the APP_KEY was changed since the LDAP pasword was last saved.  Aborting.");
                exit(0);
            }

            if($this->test_informational_bind($ldap_url[0],$ldap_url[1],$ldap_url[2],$settings->ldap_uname,$w,$settings)) {
                $this->info("Success getting informational bind!");
            } else {
                $this->error("Unable to get information from bind.");
            }
        }

        $this->line("STAGE 6: Test LDAP Login to Snipe-IT");
        foreach($ldap_urls AS $ldap_url) {
            $this->line("Starting auth to " . $ldap_url[0]);
            while(true) {
                $with_tls = $ldap_url[1] ? "with": "without";
                $with_startssl = $ldap_url[2] ? "using": "not using";
                if(!$this->confirm('Do you wish to try to authenticate to this directory: '.$ldap_url[0]." $with_tls TLS and $with_startssl STARTSSL?")) {
                    break;
                }
                $username = $this->ask("Username");
                $password = $this->secret("Password");
                $results = $this->test_authed_bind($ldap_url[0], $ldap_url[1], $ldap_url[2], $username, $password); // FIXME - should do some other stuff here, maybe with the concatenating or something? maybe? and/or should put up some results?
                if ($results) {
                    $this->info("Success authenticating with " . $username);
                } else {
                    $this->error("Unable to authenticate with " . $username);
                }
            }
        }

        $this->info("LDAP TROUBLESHOOTING COMPLETE!");
    }

    public function connect_to_ldap($ldap_url, $check_cert, $start_tls) 
    {
        if ($check_cert) {
            $this->line("we *ARE* checking certs");
            Ldap::ignoreCertificates(false);

        } else {
            $this->line("we are IGNORING certs");
            Ldap::ignoreCertificates(true);
        }
        $lconn = ldap_connect($ldap_url);
        ldap_set_option($lconn, LDAP_OPT_PROTOCOL_VERSION, 3); // should we 'test' different protocol versions here? Does anyone even use anything other than LDAPv3?
                                                             // no - it's formally deprecated: https://tools.ietf.org/html/rfc3494
        if($this->settings->ldap_client_tls_cert && $this->settings->ldap_client_tls_key) {
            // client-side TLS certificate support for LDAP (Google Secure LDAP)
            putenv('LDAPTLS_CERT=storage/ldap_client_tls.cert');
            putenv('LDAPTLS_KEY=storage/ldap_client_tls.key');
        }
        if($start_tls) {
            if(!ldap_start_tls($lconn)) {
                $this->error("WARNING: Unable to start TLS");
                return false;
            }
        }
        if(!$lconn) {
            $this->error("WARNING: Failed to generate connection string - using: ".$ldap_url);
            return false;
        }
        $net = ldap_set_option($lconn, LDAP_OPT_NETWORK_TIMEOUT, $this->option('timeout'));
        $time = ldap_set_option($lconn, LDAP_OPT_TIMELIMIT, $this->option('timeout'));
        if(!$net || !$time) {
            $this->error("Unable to set timeouts!");
        }
        return $lconn;
    }

    public function test_anonymous_bind($ldap_url, $check_cert = true, $start_tls = false)
    {
        return $this->timed_boolean_execute(function () use ($ldap_url, $check_cert , $start_tls) {
            try {
                $lconn = $this->connect_to_ldap($ldap_url, $check_cert, $start_tls);
                $this->line("Attempting to bind now, this can take a while if we mess it up");
                $bind_results = ldap_bind($lconn);
                $this->line("Bind results are: " . $bind_results . " which translate into boolean: " . (bool)$bind_results);
                ldap_close($lconn);
                return (bool)$bind_results;
            } catch (Exception $e) {
                $this->error("WARNING: Exception caught during bind - ".$e->getMessage());
                return false;
            }
        });
    }

    public function test_authed_bind($ldap_url, $check_cert, $start_tls, $username, $password) 
    {
        return $this->timed_boolean_execute(function () use ($ldap_url, $check_cert, $start_tls, $username, $password) {
            try {
                $lconn = $this->connect_to_ldap($ldap_url, $check_cert, $start_tls);
                $bind_results = ldap_bind($lconn, $username, $password);
                ldap_close($lconn);
                if(!$bind_results) {
                    $this->error("WARNING: Failed to bind to $ldap_url as $username");
                    return false;
                } else {
                    $this->info("SUCCESS - Able to bind to $ldap_url as $username");
                    return (bool)$lconn;
                }
            } catch (Exception $e) {
                $this->error("WARNING: Exception caught during Authed bind to $username - ".$e->getMessage());
                return false;
            }
        });
    }

    public function test_informational_bind($ldap_url, $check_cert, $start_tls, $username, $password,$settings)
    {
        return $this->timed_boolean_execute(function () use ($ldap_url, $check_cert, $start_tls, $username, $password, $settings) {
            try { // TODO - copypasta'ed from test_authed_bind
                $conn = $this->connect_to_ldap($ldap_url, $check_cert, $start_tls);
                $bind_results = ldap_bind($conn, $username, $password);
                if(!$bind_results) {
                    $this->error("WARNING: Failed to bind to $ldap_url as $username");
                    return false;
                }
                $this->info("SUCCESS - Able to bind to $ldap_url as $username");
                $cleaned_results = [];
                try {
                    // This _may_ only work for Active Directory?
                    $result = ldap_read($conn, '', '(objectClass=*)'/* , ['supportedControl']*/);
                    $results = ldap_get_entries($conn, $result);
                    $cleaned_results = $this->ldap_results_cleaner($results);
                    //$this->line(print_r($cleaned_results,true));
                    $default_naming_contexts = $cleaned_results[0]['namingcontexts'];
                    $this->info("Default Naming Contexts:");
                    $this->info(implode(", ", $default_naming_contexts));
                    //okay, great - now how do we display those results? I have no idea.
                } catch (\Exception $e) {
                    $this->error("Unable to get base naming contexts - here's what we *did* get:");
                    $this->line(print_r($cleaned_results, true));
                }
                // I don't see why this throws an Exception for Google LDAP, but I guess we ought to try and catch it?
                $this->debugout("I guess we're trying to do the ldap search here, but sometimes it takes too long?");
                $this->debugout("Base DN is: ".$settings->ldap_basedn." and filter is: ".parenthesized_filter($settings->ldap_filter));
                $search_results = ldap_search($conn, $settings->ldap_basedn, parenthesized_filter($settings->ldap_filter));
                $entries = ldap_get_entries($conn, $search_results);
                $this->info("Printing first 10 results: ");
                $pretty_data = array_slice($this->ldap_results_cleaner($entries), 0, 10);
                //print_r($data);
                $headers = [];
                foreach ($pretty_data as $row) {
                    //populate headers
                    foreach ($row as $key => $value) {
                        //skip objectsid and objectguid because it junks up output
                        if ($key == "objectsid" || $key == "objectguid") {
                            continue;
                        }
                        if (!in_array($key, $headers)) {
                            $headers[] = $key;
                        }
                    }
                }
                $table = [];
                //repeat again to populate table
                foreach ($pretty_data as $row) {
                    $newrow = [];
                    foreach ($headers as $header) {
                        if (is_array(@$row[$header])) {
                            $newrow[] = "[" . implode(", ", $row[$header]) . "]";
                        } else {
                            $newrow[] = @$row[$header];
                        }
                    }
                    $table[] = $newrow;
                }

                $this->table($headers, $table);
            } catch (\Exception $e) {
                $this->error("WARNING: Exception caught during Authed bind to $username - ".$e->getMessage());
                return false;
            } finally {
                ldap_close($conn);
            }
        });
    }

    /***********************************************
     * 
     * This function executes $function - which is expected to be some kind of executable function - 
     * with a timeout set. It respects the timeout by forking execution and setting a strict timer
     * for which to get back a SIGUSR1 or SIGUSR2 signal from the forked process.
     * 
     ***********************************************/
    private function timed_boolean_execute($function)
    {
        if(!(function_exists('pcntl_sigtimedwait') && function_exists('posix_getpid') && function_exists('pcntl_fork') && function_exists('posix_kill') && function_exists('pcntl_wifsignaled'))) {
            // POSIX functions needed for forking aren't present, just run the function inline (ignoring timeout)
            $this->line('WARNING: Unable to execute POSIX fork() commands, timeout may not be respected');
            return $function();
        } else {
            $parent_pid = posix_getpid();
            $pid = pcntl_fork();
            switch($pid) {
                case 0:
                    //we're the 'child'
                    if($function()) {
                        //SUCCESS = SIGUSR1
                        posix_kill($parent_pid, SIGUSR1);
                    } else {
                        //FAILURE = SIGUSR2
                        posix_kill($parent_pid, SIGUSR2);
                    }
                    exit();
                    break; //yes I know we don't need it.
                case -1:
                    //couldn't fork
                    $this->error("COULD NOT FORK - assuming failure");
                    return false;
                    break; //I still know that we don't need it
                default:
                    //we remain the 'parent', $pid is the PID of the forked process.
                    $siginfo = [];
                    $exit_status = pcntl_sigtimedwait ([SIGUSR1, SIGUSR2], $siginfo, $this->option('timeout'));
                    if ($exit_status == SIGUSR1) {
                        return true;
                    } else {
                        posix_kill($pid, SIGKILL); //make sure we don't have processes hanging around that might try and send signals during later executions, confusing us
                        return false;
                    }
                    break; //Yeah I get it already, shush.
            }
        }

    }


}
