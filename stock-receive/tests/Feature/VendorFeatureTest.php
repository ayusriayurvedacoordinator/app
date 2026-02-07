<?php
/**
 * Vendor Feature Tests
 * Tests for vendor-related functionality
 */

declare(strict_types=1);

require_once __DIR__ . '/../../models/Vendor.php';
require_once __DIR__ . '/../../includes/sanitize.php';
require_once __DIR__ . '/../BaseTestCase.php';

class VendorFeatureTest extends BaseTestCase
{
    private $vendorModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vendorModel = new Vendor();
    }

    public function testVendorCreation(): void
    {
        // Create a test vendor
        $testVendorData = [
            'name' => 'Test Vendor - ' . uniqid(),
            'phone_number' => '0123456789',
            'email' => 'test@example.com',
            'address' => 'Test Address'
        ];

        $vendorId = $this->vendorModel->create($testVendorData);
        
        // Verify the vendor was created
        $this->assertIsInt($vendorId);
        $this->assertGreaterThan(0, $vendorId);
        
        // Retrieve and verify the vendor
        $retrievedVendor = $this->vendorModel->getById($vendorId);
        $this->assertNotNull($retrievedVendor);
        $this->assertEquals($testVendorData['name'], $retrievedVendor['name']);
    }

    public function testVendorUpdate(): void
    {
        // Create a test vendor first
        $testVendorData = [
            'name' => 'Original Name - ' . uniqid(),
            'phone_number' => '0123456789',
            'email' => 'original@example.com',
            'address' => 'Original Address'
        ];

        $vendorId = $this->vendorModel->create($testVendorData);
        $this->assertIsInt($vendorId);

        // Update the vendor
        $updatedData = [
            'name' => 'Updated Name - ' . uniqid(),
            'phone_number' => '0987654321',
            'email' => 'updated@example.com',
            'address' => 'Updated Address'
        ];

        $result = $this->vendorModel->update($vendorId, $updatedData);
        $this->assertTrue($result);

        // Verify the update
        $updatedVendor = $this->vendorModel->getById($vendorId);
        $this->assertEquals($updatedData['name'], $updatedVendor['name']);
        $this->assertEquals($updatedData['email'], $updatedVendor['email']);
    }

    public function testVendorDeletion(): void
    {
        // Create a test vendor first
        $testVendorData = [
            'name' => 'Delete Test Vendor - ' . uniqid(),
            'phone_number' => '0123456789',
            'email' => 'delete@example.com',
            'address' => 'Delete Address'
        ];

        $vendorId = $this->vendorModel->create($testVendorData);
        $this->assertIsInt($vendorId);

        // Delete the vendor
        $result = $this->vendorModel->delete($vendorId);
        $this->assertTrue($result);

        // Verify the vendor is gone
        $deletedVendor = $this->vendorModel->getById($vendorId);
        $this->assertNull($deletedVendor);
    }

    public function testGetAllVendors(): void
    {
        $vendors = $this->vendorModel->getAll();
        $this->assertIsArray($vendors);
        
        // Check that vendors have expected properties
        if (!empty($vendors)) {
            $firstVendor = $vendors[0];
            $this->assertArrayHasKey('id', $firstVendor);
            $this->assertArrayHasKey('name', $firstVendor);
            $this->assertArrayHasKey('created_at', $firstVendor);
        }
    }
}