<!-- Order Details Modal -->
<div class="modal-overlay" id="orderDetailsModal" style="display: none;">
    <div class="modal modal-order-details">
        <div class="modal-header">
            <h2 id="orderDetailsModalTitle">Order Details</h2>
            <button class="modal-close" id="closeOrderDetailsModal" aria-label="Close modal">&times;</button>
        </div>
        <div class="modal-body" id="orderDetailsModalBody">
            <div id="orderDetailsLoading" style="text-align: center; padding: 40px;">
                <p>Loading order details...</p>
            </div>
            <div id="orderDetailsContent" style="display: none;">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
        <div class="modal-footer" id="orderDetailsModalFooter" style="display: none;">
            <!-- Action buttons will be populated by JavaScript -->
        </div>
    </div>
</div>

