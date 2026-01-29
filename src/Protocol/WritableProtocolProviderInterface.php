<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol;

use Sunfox\ApcPdu\PduException;
use Sunfox\ApcPdu\PowerState;

interface WritableProtocolProviderInterface extends ProtocolProviderInterface
{
    /**
     * Set the name of an outlet.
     *
     * @param int $pduIndex PDU index (1-4)
     * @param int $outletNumber Outlet number on the given PDU (1-N)
     * @param string $name New name for the outlet
     * @throws PduException
     */
    public function setOutletName(int $pduIndex, int $outletNumber, string $name): void;

    /**
     * Set the power state of an outlet.
     *
     * @param int $pduIndex PDU index (1-4)
     * @param int $outletNumber Outlet number on the given PDU (1-N)
     * @param PowerState $state New power state
     * @throws PduException
     */
    public function setOutletState(int $pduIndex, int $outletNumber, PowerState $state): void;

    /**
     * Set the external link URL for an outlet.
     *
     * @param int $pduIndex PDU index (1-4)
     * @param int $outletNumber Outlet number on the given PDU (1-N)
     * @param string $url External link URL
     * @throws PduException
     */
    public function setOutletExternalLink(int $pduIndex, int $outletNumber, string $url): void;

    /**
     * Reset the device peak power value to current load.
     *
     * @param int $pduIndex PDU index (1-4)
     * @throws PduException
     */
    public function resetDevicePeakPower(int $pduIndex): void;

    /**
     * Reset the device energy meter to zero.
     *
     * @param int $pduIndex PDU index (1-4)
     * @throws PduException
     */
    public function resetDeviceEnergy(int $pduIndex): void;

    /**
     * Reset all outlet energy meters to zero.
     *
     * @param int $pduIndex PDU index (1-4)
     * @throws PduException
     */
    public function resetOutletsEnergy(int $pduIndex): void;

    /**
     * Reset all outlet peak power values to current load.
     *
     * @param int $pduIndex PDU index (1-4)
     * @throws PduException
     */
    public function resetOutletsPeakPower(int $pduIndex): void;

    /**
     * Check if the provider supports write operations.
     */
    public function isWritable(): bool;
}
