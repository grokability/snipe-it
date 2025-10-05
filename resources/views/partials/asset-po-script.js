// Asset Purchase Order Information Injection Script
$(document).ready(function() {
    // Check if we're on an asset detail page
    if (window.location.pathname.match(/\/hardware\/\d+$/)) {
        // Extract asset ID from URL
        const assetId = window.location.pathname.split('/').pop();
        
        // Load PO information for this asset
        loadAssetPurchaseOrderInfo(assetId);
    }
});

function loadAssetPurchaseOrderInfo(assetId) {
    $.ajax({
        url: `/api/purchase-orders/asset/${assetId}/info`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.purchase_order) {
                injectPurchaseOrderInfo(response.purchase_order);
            }
        },
        error: function(xhr, status, error) {
            console.log('No PO information available for this asset');
        }
    });
}

function injectPurchaseOrderInfo(po) {
    const statusColors = {
        'draft': 'secondary',
        'pending': 'warning', 
        'approved': 'info',
        'ordered': 'primary',
        'received': 'success',
        'cancelled': 'danger'
    };
    
    const statusColor = statusColors[po.status] || 'secondary';
    const orderDate = po.order_date ? new Date(po.order_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';
    const deliveryDate = po.expected_delivery_date ? new Date(po.expected_delivery_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';
    
    const poInfoHtml = `
        <div class="row" id="purchase-order-info" style="margin-top: 20px;">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fas fa-shopping-cart"></i> Purchase Order Information
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-striped table-condensed">
                                    <tr>
                                        <td><strong>PO Number:</strong></td>
                                        <td>
                                            <a href="/purchase-orders/${po.id}" class="btn btn-sm btn-primary">
                                                ${po.po_number}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="label label-${statusColor}">
                                                ${po.status.charAt(0).toUpperCase() + po.status.slice(1)}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Order Date:</strong></td>
                                        <td>${orderDate}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped table-condensed">
                                    <tr>
                                        <td><strong>Expected Delivery:</strong></td>
                                        <td>${deliveryDate}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total Amount:</strong></td>
                                        <td><strong>$${parseFloat(po.total_amount || 0).toFixed(2)}</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Supplier:</strong></td>
                                        <td>${po.supplier_name || 'N/A'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        ${po.notes ? `
                        <div class="row">
                            <div class="col-md-12">
                                <strong>Notes:</strong>
                                <p class="well well-sm">${po.notes}</p>
                            </div>
                        </div>
                        ` : ''}
                        
                        <div class="row">
                            <div class="col-md-12">
                                <a href="/purchase-orders/${po.id}" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Full Purchase Order
                                </a>
                                <a href="/purchase-orders/${po.id}/pdf" class="btn btn-info" target="_blank">
                                    <i class="fas fa-download"></i> Download PO PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Find a good place to inject the PO info (after the first box)
    const targetElement = $('.content-wrapper .content .row .box').first().closest('.row');
    if (targetElement.length) {
        targetElement.after(poInfoHtml);
    } else {
        // Fallback: add to main content area
        $('.content-wrapper .content').append(poInfoHtml);
    }
}
