<?php
/**
 * Vendor Controller
 * Handles vendor-related HTTP requests
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Vendor.php';
require_once __DIR__ . '/../../../includes/sanitize.php';
require_once __DIR__ . '/../../../includes/audit_helper.php';

class VendorController extends BaseController
{
    private $vendorModel;

    public function __construct()
    {
        parent::__construct();
        $this->vendorModel = new Vendor();
    }

    /**
     * Display list of all vendors
     */
    public function index()
    {
        $vendors = $this->vendorModel->getAll();
        $this->set('page_title', 'Vendors - Stock Receive System');
        $this->set('vendors', $vendors);
        $this->render('vendors/index', [
            'vendors' => $vendors
        ]);
    }

    /**
     * Show form to create a new vendor
     */
    public function create()
    {
        $this->set('page_title', 'Add Vendor - Stock Receive System');
        $this->render('vendors/create');
    }

    /**
     * Store a new vendor
     */
    public function store()
    {
        if (!$this->isPost()) {
            $this->redirect('/stock-receive/vendors');
            return;
        }

        // Validate CSRF token
        $csrfToken = $this->post('csrf_token');
        if (!validate_csrf_token($csrfToken)) {
            $this->set('message', 'Invalid request. Please try again.');
            $this->set('message_type', 'danger');
            $this->create();
            return;
        }

        $name = $this->sanitizeInput($this->post('name'));
        $phone_number = $this->sanitizeInput($this->post('phone_number'));
        $email = $this->sanitizeInput($this->post('email'));
        $address = $this->sanitizeInput($this->post('address'));

        // Validate required fields
        if (empty(trim($name))) {
            $this->set('message', 'Vendor name is required.');
            $this->set('message_type', 'warning');
            $this->create();
            return;
        }

        // Validate phone number format
        if (!empty($phone_number) && !preg_match(PHONE_NUMBER_PATTERN, $phone_number)) {
            $this->set('message', 'Phone number must be 10 digits and start with 0.');
            $this->set('message_type', 'warning');
            $this->create();
            return;
        }

        // Validate input lengths
        if (strlen($name) > NAME_MAX_LENGTH) {
            $this->set('message', "Vendor name exceeds maximum length of " . NAME_MAX_LENGTH . " characters.");
            $this->set('message_type', 'warning');
            $this->create();
            return;
        }

        if (!empty($email) && strlen($email) > EMAIL_MAX_LENGTH) {
            $this->set('message', "Email address exceeds maximum length of " . EMAIL_MAX_LENGTH . " characters.");
            $this->set('message_type', 'warning');
            $this->create();
            return;
        }

        if (strlen($address) > ADDRESS_MAX_LENGTH) {
            $this->set('message', "Address exceeds maximum length of " . ADDRESS_MAX_LENGTH . " characters.");
            $this->set('message_type', 'warning');
            $this->create();
            return;
        }

        try {
            $vendorData = [
                'name' => $name,
                'phone_number' => $phone_number,
                'email' => $email,
                'address' => $address
            ];

            $vendorId = $this->vendorModel->create($vendorData);

            $this->set('message', "Vendor added successfully! Vendor ID: {$vendorId}");
            $this->set('message_type', 'success');
            $this->redirect('/stock-receive/vendors');
        } catch (Exception $e) {
            $this->set('message', 'Error: ' . $this->sanitizeOutput($e->getMessage()));
            $this->set('message_type', 'danger');
            $this->create();
        }
    }

    /**
     * Show form to edit a vendor
     */
    public function edit($id)
    {
        $id = (int)$id;
        $vendor = $this->vendorModel->getById($id);

        if (!$vendor) {
            $this->set('message', 'Vendor not found.');
            $this->set('message_type', 'danger');
            $this->index();
            return;
        }

        $this->set('page_title', 'Edit Vendor - Stock Receive System');
        $this->render('vendors/edit', [
            'vendor' => $vendor
        ]);
    }

    /**
     * Update an existing vendor
     */
    public function update($id)
    {
        $id = (int)$id;

        if (!$this->isPost()) {
            $this->redirect("/stock-receive/vendors/{$id}/edit");
            return;
        }

        // Validate CSRF token
        $csrfToken = $this->post('csrf_token');
        if (!validate_csrf_token($csrfToken)) {
            $this->set('message', 'Invalid request. Please try again.');
            $this->set('message_type', 'danger');
            $this->edit($id);
            return;
        }

        $name = $this->sanitizeInput($this->post('name'));
        $phone_number = $this->sanitizeInput($this->post('phone_number'));
        $email = $this->sanitizeInput($this->post('email'));
        $address = $this->sanitizeInput($this->post('address'));

        // Validate required fields
        if (empty(trim($name))) {
            $this->set('message', 'Vendor name is required.');
            $this->set('message_type', 'warning');
            $this->edit($id);
            return;
        }

        // Validate phone number format
        if (!empty($phone_number) && !preg_match(PHONE_NUMBER_PATTERN, $phone_number)) {
            $this->set('message', 'Phone number must be 10 digits and start with 0.');
            $this->set('message_type', 'warning');
            $this->edit($id);
            return;
        }

        // Validate input lengths
        if (strlen($name) > NAME_MAX_LENGTH) {
            $this->set('message', "Vendor name exceeds maximum length of " . NAME_MAX_LENGTH . " characters.");
            $this->set('message_type', 'warning');
            $this->edit($id);
            return;
        }

        if (!empty($email) && strlen($email) > EMAIL_MAX_LENGTH) {
            $this->set('message', "Email address exceeds maximum length of " . EMAIL_MAX_LENGTH . " characters.");
            $this->set('message_type', 'warning');
            $this->edit($id);
            return;
        }

        if (strlen($address) > ADDRESS_MAX_LENGTH) {
            $this->set('message', "Address exceeds maximum length of " . ADDRESS_MAX_LENGTH . " characters.");
            $this->set('message_type', 'warning');
            $this->edit($id);
            return;
        }

        try {
            $vendorData = [
                'name' => $name,
                'phone_number' => $phone_number,
                'email' => $email,
                'address' => $address
            ];

            $result = $this->vendorModel->update($id, $vendorData);

            if ($result) {
                $this->set('message', 'Vendor updated successfully!');
                $this->set('message_type', 'success');
            } else {
                $this->set('message', 'Error updating vendor.');
                $this->set('message_type', 'danger');
            }

            $this->redirect('/stock-receive/vendors');
        } catch (Exception $e) {
            $this->set('message', 'Error: ' . $this->sanitizeOutput($e->getMessage()));
            $this->set('message_type', 'danger');
            $this->edit($id);
        }
    }

    /**
     * Delete a vendor
     */
    public function delete($id)
    {
        $id = (int)$id;

        try {
            $result = $this->vendorModel->delete($id);

            if ($result) {
                $this->set('message', 'Vendor deleted successfully!');
                $this->set('message_type', 'success');
            } else {
                $this->set('message', 'Error deleting vendor.');
                $this->set('message_type', 'danger');
            }
        } catch (Exception $e) {
            $this->set('message', 'Error: ' . $this->sanitizeOutput($e->getMessage()));
            $this->set('message_type', 'danger');
        }

        $this->redirect('/stock-receive/vendors');
    }
}