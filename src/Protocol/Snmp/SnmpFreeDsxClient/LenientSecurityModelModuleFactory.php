<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Protocol\Snmp\SnmpFreeDsxClient;

use FreeDSx\Snmp\Protocol\Factory\SecurityModelModuleFactory;

/**
 * Custom SecurityModelModuleFactory that uses LenientUserSecurityModelModule.
 */
class LenientSecurityModelModuleFactory extends SecurityModelModuleFactory
{
    /**
     * @var array<int, class-string>
     */
    protected $map = [];

    public function __construct()
    {
        $module = LenientUserSecurityModelModule::class;
        $this->map[\call_user_func($module . '::supports')] = $module;
    }
}
