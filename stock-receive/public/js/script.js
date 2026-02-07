// JavaScript for Stock Receive System

// Calculate total cost when quantity and unit price change
function calculateTotalCost() {
    const quantity = parseFloat(document.getElementById('quantity_received').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const totalCost = quantity * unitPrice;
    
    document.getElementById('total_cost').value = totalCost.toFixed(2);
}

// Add event listeners when the page loads
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity_received');
    const unitPriceInput = document.getElementById('unit_price');
    
    if(quantityInput && unitPriceInput) {
        quantityInput.addEventListener('input', calculateTotalCost);
        unitPriceInput.addEventListener('input', calculateTotalCost);
    }
});

// Confirmation for delete actions
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this record?');
}