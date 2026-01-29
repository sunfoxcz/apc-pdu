<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Ssh;

use Sunfox\ApcPdu\PduException;

final class SshClient
{
    /** @var resource|null */
    private mixed $connection = null;

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

        $stream = @ssh2_exec($this->connection, $command);
        if ($stream === false) {
            throw new PduException("Failed to execute SSH command: {$command}");
        }

        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);

        if ($output === false) {
            throw new PduException('Failed to read SSH command output');
        }

        return $output;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    private function connect(): void
    {
        if ($this->connection !== null) {
            return;
        }

        $connection = @ssh2_connect($this->host, $this->port);
        if ($connection === false) {
            throw new PduException("Failed to connect to SSH host: {$this->host}:{$this->port}");
        }

        if (!@ssh2_auth_password($connection, $this->username, $this->password)) {
            throw new PduException("SSH authentication failed for user: {$this->username}");
        }

        $this->connection = $connection;
    }
}
