<?php
/**
 * StatsService Unit Tests
 * Tests for the StatsService functionality
 */

declare(strict_types=1);

require_once __DIR__ . '/../../services/StatsService.php';
require_once __DIR__ . '/../BaseTestCase.php';

class StatsServiceTest extends BaseTestCase
{
    private $statsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statsService = new StatsService();
    }

    public function testStatsServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(StatsService::class, $this->statsService);
    }

    public function testGetDashboardStatsReturnsArray(): void
    {
        $stats = $this->statsService->getDashboardStats();
        $this->assertIsArray($stats);
        
        // Check that expected keys are present
        $expectedKeys = ['vendors_count', 'invoices_count', 'invoice_items_count', 'stock_recounts_count'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $stats);
        }
    }

    public function testGetCountsReturnIntegers(): void
    {
        $this->assertIsInt($this->statsService->getVendorCount());
        $this->assertIsInt($this->statsService->getInvoiceCount());
        $this->assertIsInt($this->statsService->getInvoiceItemCount());
        $this->assertIsInt($this->statsService->getStockRecountCount());
    }
}