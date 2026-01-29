<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Ssh;

use Sunfox\ApcPdu\PduException;

final class SshClient
{
    /** @var resource|null */
    private mixed $shell = null;

    public function __construct(
        private string $host,
        private string $username,
        private string $password,
        private int $port = 22,
    ) {
    }

    public function execute(string $command): string
    {
        $this->connect();

        // Clear any pending output
        $this->readOutput(100);

        // Send command
        fwrite($this->shell, $command . "\n");

        // Wait for response and read output
        usleep(300000); // 300ms delay for command execution
        $output = $this->readOutput(500);

        return $output;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    private function readOutput(int $timeoutMs): string
    {
        $output = '';
        $startTime = microtime(true);
        $timeout = $timeoutMs / 1000;

        while ((microtime(true) - $startTime) < $timeout) {
            $buf = @fread($this->shell, 4096);
            if ($buf !== false && $buf !== '') {
                $output .= $buf;
            } else {
                usleep(10000); // 10ms sleep between reads
            }
        }

        return $output;
    }

    private function connect(): void
    {
        if ($this->shell !== null) {
            return;
        }

        $connection = @ssh2_connect($this->host, $this->port);
        if ($connection === false) {
            throw new PduException("Failed to connect to SSH host: {$this->host}:{$this->port}");
        }

        if (!@ssh2_auth_password($connection, $this->username, $this->password)) {
            throw new PduException("SSH authentication failed for user: {$this->username}");
        }

        // Open interactive shell
        $shell = @ssh2_shell($connection, 'vt102', [], 80, 40, SSH2_TERM_UNIT_CHARS);
        if ($shell === false) {
            throw new PduException('Failed to open SSH shell');
        }

        stream_set_blocking($shell, false);
        $this->shell = $shell;

        // Wait for initial prompt and clear it
        usleep(1000000); // 1 second for initial menu
        $this->readOutput(500);
    }
}
