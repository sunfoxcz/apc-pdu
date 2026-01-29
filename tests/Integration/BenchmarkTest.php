<?php

declare(strict_types=1);

namespace Sunfox\ApcPdu\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sunfox\ApcPdu\ApcPdu;
use Sunfox\ApcPdu\ApcPduFactory;
use Sunfox\ApcPdu\DeviceMetric;
use Sunfox\ApcPdu\OutletMetric;

/**
 * Benchmark tests comparing batch vs individual SNMP requests.
 *
 * @group integration
 * @group benchmark
 */
class BenchmarkTest extends TestCase
{
    private ?ApcPdu $pdu = null;

    protected function setUp(): void
    {
        $host = getenv('PDU_HOST');
        $user = getenv('PDU_SNMP_USER');
        $authPass = getenv('PDU_SNMP_AUTH_PASS');
        $privPass = getenv('PDU_SNMP_PRIV_PASS');

        if (empty($host) || empty($user) || empty($authPass)) {
            $this->markTestSkipped('PDU connection environment variables not set.');
        }

        $this->pdu = ApcPduFactory::snmpV3($host, $user, $authPass, $privPass ?: '');
    }

    public function testBenchmarkDeviceMetrics(): void
    {
        // Warm up
        $this->pdu->getDeviceStatus(1);

        // Benchmark batched (current implementation)
        $batchedTimes = [];
        for ($i = 0; $i < 5; $i++) {
            $start = microtime(true);
            $this->pdu->getDeviceStatus(1);
            $batchedTimes[] = microtime(true) - $start;
        }

        // Benchmark individual requests (simulated by calling getDevice for each metric)
        $individualTimes = [];
        for ($i = 0; $i < 5; $i++) {
            $start = microtime(true);
            foreach (DeviceMetric::cases() as $metric) {
                $this->pdu->getDevice($metric, 1);
            }
            $individualTimes[] = microtime(true) - $start;
        }

        $batchedAvg = array_sum($batchedTimes) / count($batchedTimes);
        $individualAvg = array_sum($individualTimes) / count($individualTimes);
        $speedup = $individualAvg / $batchedAvg;

        // Batched should be faster than individual
        $this->assertGreaterThan(1.0, $speedup, sprintf(
            'Batched (%.3fs) should be faster than individual (%.3fs), got %.1fx',
            $batchedAvg,
            $individualAvg,
            $speedup,
        ));
    }

    public function testBenchmarkOutletMetrics(): void
    {
        // Warm up
        $this->pdu->getOutletStatus(1, 1);

        // Benchmark batched (current implementation)
        $batchedTimes = [];
        for ($i = 0; $i < 5; $i++) {
            $start = microtime(true);
            $this->pdu->getOutletStatus(1, 1);
            $batchedTimes[] = microtime(true) - $start;
        }

        // Benchmark individual requests
        $individualTimes = [];
        for ($i = 0; $i < 5; $i++) {
            $start = microtime(true);
            foreach (OutletMetric::cases() as $metric) {
                $this->pdu->getOutlet(1, 1, $metric);
            }
            $individualTimes[] = microtime(true) - $start;
        }

        $batchedAvg = array_sum($batchedTimes) / count($batchedTimes);
        $individualAvg = array_sum($individualTimes) / count($individualTimes);
        $speedup = $individualAvg / $batchedAvg;

        // Batched should be faster than individual
        $this->assertGreaterThan(1.0, $speedup, sprintf(
            'Batched (%.3fs) should be faster than individual (%.3fs), got %.1fx',
            $batchedAvg,
            $individualAvg,
            $speedup,
        ));
    }

    public function testBenchmarkAllOutlets(): void
    {
        // Warm up
        $this->pdu->getAllOutlets(1);

        // Benchmark batched (current implementation - 24 batch requests)
        $batchedTimes = [];
        for ($i = 0; $i < 3; $i++) {
            $start = microtime(true);
            $this->pdu->getAllOutlets(1);
            $batchedTimes[] = microtime(true) - $start;
        }

        $batchedAvg = array_sum($batchedTimes) / count($batchedTimes);

        // Estimate individual time based on outlet metrics benchmark
        // 24 outlets * 13 metrics = 312 individual requests
        // We'll estimate based on single outlet timing
        $singleOutletStart = microtime(true);
        foreach (OutletMetric::cases() as $metric) {
            $this->pdu->getOutlet(1, 1, $metric);
        }
        $singleOutletTime = microtime(true) - $singleOutletStart;
        $estimatedIndividualTime = $singleOutletTime * 24;

        $speedup = $estimatedIndividualTime / $batchedAvg;

        // Batched should be faster than individual
        $this->assertGreaterThan(1.0, $speedup, sprintf(
            'Batched (%.3fs) should be faster than individual (~%.3fs), got %.1fx',
            $batchedAvg,
            $estimatedIndividualTime,
            $speedup,
        ));
    }

    public function testBenchmarkFullStatus(): void
    {
        // Warm up
        $this->pdu->getFullStatus();

        // Benchmark batched (current implementation)
        $batchedTimes = [];
        for ($i = 0; $i < 3; $i++) {
            $start = microtime(true);
            $status = $this->pdu->getFullStatus();
            $batchedTimes[] = microtime(true) - $start;
        }

        $batchedAvg = array_sum($batchedTimes) / count($batchedTimes);
        $pduCount = count($status);

        // Estimate: per PDU = 1 device batch + 24 outlet batches = 25 requests
        // Old way: per PDU = 18 device + 312 outlet = 330 requests
        $estimatedOldRequests = $pduCount * 330;
        $actualNewRequests = $pduCount * 25;

        // Full status should complete in reasonable time
        $requestReduction = (1 - $actualNewRequests / $estimatedOldRequests) * 100;
        $this->assertLessThan(60, $batchedAvg, sprintf(
            'Full status took %.3fs (%d batched vs %d individual requests, %.0f%% reduction)',
            $batchedAvg,
            $actualNewRequests,
            $estimatedOldRequests,
            $requestReduction,
        ));
    }
}
