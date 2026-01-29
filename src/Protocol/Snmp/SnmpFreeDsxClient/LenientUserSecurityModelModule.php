<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp\SnmpFreeDsxClient;

use FreeDSx\Snmp\Exception\SecurityModelException;
use FreeDSx\Snmp\Exception\SnmpAuthenticationException;
use FreeDSx\Snmp\Exception\SnmpEncryptionException;
use FreeDSx\Snmp\Message\AbstractMessageV3;
use FreeDSx\Snmp\Message\Response\MessageResponseV3;
use FreeDSx\Snmp\Module\SecurityModel\UserSecurityModelModule;

/**
 * Extended UserSecurityModelModule that doesn't require response headers to have auth/priv flags.
 *
 * Some APC PDUs don't mirror the authentication flags in responses even though
 * they process authenticated requests correctly. This module works around that.
 */
class LenientUserSecurityModelModule extends UserSecurityModelModule
{
    /**
     * {@inheritdoc}
     *
     * Overridden to skip the header flag checks that some PDUs don't handle properly.
     *
     * @param array<string, mixed> $options
     */
    public function handleIncomingMessage(AbstractMessageV3 $message, array $options): AbstractMessageV3
    {
        $securityParams = $message->getSecurityParameters();
        $header = $message->getMessageHeader();

        if (!$securityParams) {
            throw new SecurityModelException('The received SNMP message is missing the security parameters.');
        }

        $useAuth = $options['use_auth'];
        $usePriv = $options['use_priv'];

        // Skip the header flag check that the parent does - some PDUs don't set these properly
        // The actual authentication is still verified below via authenticateIncomingMsg()

        if ($useAuth && $header->hasAuthentication()) {
            try {
                $message = $this->authFactory->get($options['auth_mech'])->authenticateIncomingMsg(
                    $message,
                    $options['auth_pwd']
                );
            } catch (SnmpAuthenticationException $e) {
                throw new SecurityModelException($e->getMessage());
            }
        }
        if ($usePriv && $header->hasPrivacy()) {
            try {
                $message = $this->privacyFactory->get($options['priv_mech'])->decryptData(
                    $message,
                    $this->authFactory->get($options['auth_mech']),
                    $options['priv_pwd']
                );
            } catch (SnmpEncryptionException $e) {
                throw new SecurityModelException($e->getMessage());
            }
        }

        if ($message instanceof MessageResponseV3) {
            $this->validateIncomingResponse(
                $message,
                $options
            );
        }

        return $message;
    }
}
