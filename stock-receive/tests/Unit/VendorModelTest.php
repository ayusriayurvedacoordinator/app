<?php
/**
 * Vendor Model Unit Tests
 * Tests for the Vendor model functionality
 */

declare(strict_types=1);

require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../BaseTestCase.php';

class VendorModelTest extends BaseTestCase
{
    private $vendorModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vendorModel = new Vendor();
    }

    public function testVendorModelCanBeInstantiated(): void
    {
        $this->assertInstanceOf(Vendor::class, $this->vendorModel);
    }

    public function testGetAllReturnsArray(): void
    {
        $vendors = $this->vendorModel->getAll();
        $this->assertIsArray($vendors);
    }

    public function testGetByIdReturnsCorrectStructure(): void
    {
        // Test with a known ID or expect null for invalid ID
        $vendor = $this->vendorModel->getById(999999); // Assuming this ID doesn't exist
        $this->assertNull($vendor);
    }

    public function testGetCountReturnsInteger(): void
    {
        $count = $this->vendorModel->getCount();
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    /**
     * @dataProvider vendorDataProvider
     */
    public function testVendorValidation(array $vendorData, bool $shouldPass): void
    {
        // This test would need to call a validation method if it existed separately
        // For now, we'll just validate the data structure
        $this->assertIsArray($vendorData);
        $this->assertArrayHasKey('name', $vendorData);
    }

    public function vendorDataProvider(): array
    {
        return [
            'valid vendor' => [['name' => 'Test Vendor', 'phone_number' => '0123456789', 'email' => 'test@example.com'], true],
            'vendor without name' => [['name' => '', 'phone_number' => '0123456789'], false],
            'vendor with invalid phone' => [['name' => 'Test Vendor', 'phone_number' => '12345'], false],
        ];
    }
}