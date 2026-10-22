<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Tests\TestCase;

/**
 * Coverage for the SMTP block in config/mail.php: the configurable EHLO hostname
 * (MAIL_EHLO_DOMAIN), optional SMTP AUTH, and the handling of unresolved
 * "${VAR}" references that docker/docker.env leaves in .env.
 *
 * The config file is loaded for real under different env combinations and the
 * resulting array is handed to the installed MailManager, so a regression in the
 * file or in how Laravel passes the options to Symfony Mailer is caught. The wire
 * tests talk to a throw-away SMTP server on 127.0.0.1; nothing leaves the machine.
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class MailSmtpConfigurationTest extends TestCase
{
    /**
     * Every env key this test manipulates; the snapshot/restore and the reset in
     * loadSmtpConfig must cover the same set.
     */
    private const MAIL_KEYS = [
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'MAIL_ENCRYPTION',
        'MAIL_TLS_VERIFY_PEER',
        'MAIL_EHLO_DOMAIN',
    ];

    private array $originalMailEnv = [];

    /** @var resource|null */
    private $serverProcess = null;

    private ?string $serverDirectory = null;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::MAIL_KEYS as $key) {
            $this->originalMailEnv[$key] = [
                'env' => $_ENV[$key] ?? false,
                'server' => $_SERVER[$key] ?? false,
                'getenv' => getenv($key),
            ];
        }

        $this->beforeApplicationDestroyed(function () {
            $this->stopSmtpServer();
            $this->restoreMailEnv();
        });
    }

    private function restoreMailEnv(): void
    {
        foreach ($this->originalMailEnv as $key => $original) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);

            if ($original['env'] !== false) {
                $_ENV[$key] = $original['env'];
            }
            if ($original['server'] !== false) {
                $_SERVER[$key] = $original['server'];
            }
            if ($original['getenv'] !== false) {
                putenv("$key={$original['getenv']}");
            }
        }
    }

    /**
     * Set the given env vars (every other MAIL_* key this test owns is unset),
     * re-require config/mail.php and return its "smtp" mailer array.
     *
     * @param  array<string, string>  $env
     * @return array<string, mixed>
     */
    private function loadSmtpConfig(array $env): array
    {
        foreach (self::MAIL_KEYS as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }

        foreach ($env as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }

        return (require base_path('config/mail.php'))['mailers']['smtp'];
    }

    /**
     * Build the SMTP transport the application would use for the given smtp config.
     */
    private function transportFor(array $smtp): EsmtpTransport
    {
        config(['mail.mailers.smtp' => $smtp]);
        Mail::purge('smtp');

        $transport = Mail::mailer('smtp')->getSymfonyTransport();
        $this->assertInstanceOf(EsmtpTransport::class, $transport);

        return $transport;
    }

    private function verifyPeerDisabled(EsmtpTransport $transport): bool
    {
        $ssl = $transport->getStream()->getStreamOptions()['ssl'] ?? [];

        return array_key_exists('verify_peer', $ssl) && $ssl['verify_peer'] === false;
    }

    public function test_configured_ehlo_domain_is_passed_to_the_smtp_transport(): void
    {
        $smtp = $this->loadSmtpConfig([
            'MAIL_HOST' => 'smtp.example.invalid',
            'MAIL_EHLO_DOMAIN' => 'snipe-it.example.invalid',
        ]);

        $this->assertSame('snipe-it.example.invalid', $smtp['local_domain']);
        $this->assertSame('snipe-it.example.invalid', $this->transportFor($smtp)->getLocalDomain());
    }

    public function test_unset_or_empty_ehlo_domain_keeps_the_symfony_default(): void
    {
        $default = (new EsmtpTransport('smtp.example.invalid'))->getLocalDomain();

        foreach ([[], ['MAIL_EHLO_DOMAIN' => ''], ['MAIL_EHLO_DOMAIN' => '${MAIL_EHLO_DOMAIN}']] as $env) {
            $smtp = $this->loadSmtpConfig($env + ['MAIL_HOST' => 'smtp.example.invalid']);

            $this->assertNull($smtp['local_domain']);
            $this->assertSame($default, $this->transportFor($smtp)->getLocalDomain());
        }
    }

    public function test_no_credentials_configures_no_smtp_auth(): void
    {
        $smtp = $this->loadSmtpConfig(['MAIL_HOST' => 'smtp.example.invalid']);

        $this->assertNull($smtp['username']);
        $this->assertNull($smtp['password']);

        $transport = $this->transportFor($smtp);
        $this->assertSame('', $transport->getUsername());
        $this->assertSame('', $transport->getPassword());
    }

    public function test_credentials_are_passed_to_the_smtp_transport(): void
    {
        $smtp = $this->loadSmtpConfig([
            'MAIL_HOST' => 'smtp.example.invalid',
            'MAIL_USERNAME' => 'mailer-user',
            'MAIL_PASSWORD' => 'mailer-password',
        ]);

        $transport = $this->transportFor($smtp);

        $this->assertSame('mailer-user', $transport->getUsername());
        $this->assertSame('mailer-password', $transport->getPassword());
    }

    public function test_unresolved_docker_env_references_are_not_used_as_credentials(): void
    {
        $smtp = $this->loadSmtpConfig([
            'MAIL_HOST' => 'smtp.example.invalid',
            'MAIL_USERNAME' => '${MAIL_ENV_USERNAME}',
            'MAIL_PASSWORD' => '${MAIL_ENV_PASSWORD}',
        ]);

        $this->assertNull($smtp['username']);
        $this->assertNull($smtp['password']);
        $this->assertSame('', $this->transportFor($smtp)->getUsername());
    }

    public function test_tls_peer_verification_stays_on_by_default_and_for_unresolved_references(): void
    {
        foreach ([[], ['MAIL_TLS_VERIFY_PEER' => '${MAIL_TLS_VERIFY_PEER}'], ['MAIL_TLS_VERIFY_PEER' => 'true'], ['MAIL_TLS_VERIFY_PEER' => '1']] as $env) {
            $smtp = $this->loadSmtpConfig($env + ['MAIL_HOST' => 'smtp.example.invalid']);

            $this->assertFalse($this->verifyPeerDisabled($this->transportFor($smtp)), json_encode($env));
        }
    }

    public function test_tls_peer_verification_can_still_be_turned_off_explicitly(): void
    {
        foreach (['false', '0'] as $value) {
            $smtp = $this->loadSmtpConfig(['MAIL_HOST' => 'smtp.example.invalid', 'MAIL_TLS_VERIFY_PEER' => $value]);

            $this->assertTrue($this->verifyPeerDisabled($this->transportFor($smtp)), $value);
        }
    }

    public function test_setting_the_ehlo_domain_does_not_change_tls_verification(): void
    {
        $smtp = $this->loadSmtpConfig(['MAIL_HOST' => 'smtp.example.invalid', 'MAIL_EHLO_DOMAIN' => 'snipe-it.example.invalid']);

        $this->assertFalse($this->verifyPeerDisabled($this->transportFor($smtp)));
    }

    public function test_ehlo_name_is_announced_on_the_wire_and_no_auth_is_attempted_without_credentials(): void
    {
        $port = $this->startSmtpServer();

        $smtp = $this->loadSmtpConfig([
            'MAIL_HOST' => '127.0.0.1',
            'MAIL_PORT' => (string) $port,
            'MAIL_EHLO_DOMAIN' => 'snipe-it.example.invalid',
            'MAIL_USERNAME' => '${MAIL_ENV_USERNAME}',
            'MAIL_PASSWORD' => '${MAIL_ENV_PASSWORD}',
        ]);
        $this->sendTestMail($smtp);

        $commands = $this->serverCommands();
        $this->assertContains('EHLO snipe-it.example.invalid', $commands);
        $this->assertContains('MAIL FROM', $commands);
        $this->assertNotContains('AUTH', $commands, 'the server offers AUTH, but no credentials are configured');
    }

    public function test_smtp_auth_is_used_when_credentials_are_configured(): void
    {
        $port = $this->startSmtpServer();

        $smtp = $this->loadSmtpConfig([
            'MAIL_HOST' => '127.0.0.1',
            'MAIL_PORT' => (string) $port,
            'MAIL_USERNAME' => 'mailer-user',
            'MAIL_PASSWORD' => 'mailer-password',
        ]);
        $this->sendTestMail($smtp);

        $commands = $this->serverCommands();
        $this->assertContains('AUTH', $commands);
        $this->assertContains('MAIL FROM', $commands);
        $this->assertNotContains('EHLO snipe-it.example.invalid', $commands);
    }

    private function sendTestMail(array $smtp): void
    {
        config(['mail.mailers.smtp' => $smtp]);
        Mail::purge('smtp');

        Mail::mailer('smtp')->raw('SMTP configuration test', function ($message) {
            $message->from('sender@example.invalid')->to('recipient@example.invalid')->subject('SMTP configuration test');
        });
    }

    /**
     * Start a one-connection SMTP server on a free 127.0.0.1 port. It advertises
     * AUTH PLAIN, accepts anything, and records only the SMTP verbs and the EHLO
     * argument (never an AUTH payload) in a log file.
     */
    private function startSmtpServer(): int
    {
        $this->serverDirectory = sys_get_temp_dir().'/snipeit-smtp-'.bin2hex(random_bytes(6));
        mkdir($this->serverDirectory, 0700);

        $script = $this->serverDirectory.'/server.php';
        file_put_contents($script, <<<'PHP'
<?php
[$script, $dir] = $argv;
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
file_put_contents($dir.'/port', (string) parse_url('tcp://'.stream_socket_get_name($server, false), PHP_URL_PORT));
$connection = stream_socket_accept($server, 20);
stream_set_timeout($connection, 10);
$log = fn (string $line) => file_put_contents($dir.'/commands.log', $line."\n", FILE_APPEND);
fwrite($connection, "220 test.example.invalid ESMTP\r\n");
$inData = false;
while (($line = fgets($connection)) !== false) {
    $line = rtrim($line, "\r\n");
    if ($inData) {
        if ($line === '.') {
            $inData = false;
            fwrite($connection, "250 queued\r\n");
        }
        continue;
    }
    $verb = strtoupper(strtok($line, ' '));
    if ($verb === 'EHLO' || $verb === 'HELO') {
        $log('EHLO '.substr($line, 5));
        fwrite($connection, "250-test.example.invalid\r\n250-AUTH PLAIN\r\n250 8BITMIME\r\n");
    } elseif ($verb === 'AUTH') {
        $log('AUTH');
        fwrite($connection, "235 2.7.0 accepted\r\n");
    } elseif ($verb === 'MAIL' || $verb === 'RCPT') {
        $log($verb === 'MAIL' ? 'MAIL FROM' : 'RCPT TO');
        fwrite($connection, "250 ok\r\n");
    } elseif ($verb === 'DATA') {
        $inData = true;
        fwrite($connection, "354 go ahead\r\n");
    } elseif ($verb === 'QUIT') {
        fwrite($connection, "221 bye\r\n");
        break;
    } else {
        fwrite($connection, "250 ok\r\n");
    }
}
PHP);

        $this->serverProcess = proc_open(
            [PHP_BINARY, $script, $this->serverDirectory],
            [0 => ['file', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null', 'r'], 1 => ['file', $this->serverDirectory.'/stdout', 'w'], 2 => ['file', $this->serverDirectory.'/stderr', 'w']],
            $pipes
        );
        $this->assertIsResource($this->serverProcess);

        for ($waited = 0; $waited < 100 && ! is_file($this->serverDirectory.'/port'); $waited++) {
            usleep(50000);
        }
        $this->assertFileExists($this->serverDirectory.'/port', 'the test SMTP server did not start');
        usleep(50000);

        return (int) trim(file_get_contents($this->serverDirectory.'/port'));
    }

    /**
     * @return list<string>
     */
    private function serverCommands(): array
    {
        $log = $this->serverDirectory.'/commands.log';

        return is_file($log) ? array_values(array_filter(explode("\n", file_get_contents($log)))) : [];
    }

    private function stopSmtpServer(): void
    {
        if (is_resource($this->serverProcess)) {
            proc_terminate($this->serverProcess);
            proc_close($this->serverProcess);
            $this->serverProcess = null;
        }

        if ($this->serverDirectory !== null && is_dir($this->serverDirectory)) {
            foreach (glob($this->serverDirectory.'/*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->serverDirectory);
            $this->serverDirectory = null;
        }
    }
}
