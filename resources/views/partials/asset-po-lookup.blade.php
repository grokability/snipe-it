{{-- Asset to Purchase Order Lookup Tool --}}
{{-- This provides a quick way to find which PO an asset belongs to --}}

<div class="row" style="margin-bottom: 20px;">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fas fa-search"></i> Asset to Purchase Order Lookup
                </h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Enter Asset Tag or Asset ID:</label>
                            <input type="text" id="asset-lookup-input" class="form-control" 
                                   placeholder="e.g., 00002 or SK7864">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" id="lookup-po-btn" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Find Purchase Order
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" id="clear-lookup-btn" class="btn btn-default btn-block">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="lookup-results" style="display: none;">
                    <hr>
                    <div id="lookup-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#lookup-po-btn').click(function() {
        const assetIdentifier = $('#asset-lookup-input').val().trim();
        
        if (!assetIdentifier) {
            alert('Please enter an asset tag or asset ID');
            return;
        }
        
        // Show loading state
        $('#lookup-results').show();
        $('#lookup-content').html('<i class="fas fa-spinner fa-spin"></i> Searching for asset and purchase order...');
        
        // Perform lookup
        performAssetPOLookup(assetIdentifier);
    });
    
    $('#clear-lookup-btn').click(function() {
        $('#asset-lookup-input').val('');
        $('#lookup-results').hide();
        $('#lookup-content').html('');
    });
    
    // Allow Enter key to trigger search
    $('#asset-lookup-input').keypress(function(e) {
        if (e.which === 13) {
            $('#lookup-po-btn').click();
        }
    });
});

function performAssetPOLookup(assetIdentifier) {
    // Since we can't easily make authenticated API calls, we'll use a simple database lookup
    // This would ideally be an AJAX call to a custom endpoint
    
    // For now, show a helpful message with manual lookup instructions
    const lookupHtml = `
        <div class="alert alert-info">
            <h4><i class="fas fa-info-circle"></i> Asset Lookup: ${assetIdentifier}</h4>
            <p><strong>To find the Purchase Order for this asset:</strong></p>
            <ol>
                <li>Go to <a href="/purchase-orders" target="_blank">Purchase Orders list</a></li>
                <li>Look for POs containing asset tag: <code>${assetIdentifier}</code></li>
                <li>Or use the database query below in your admin tools</li>
            </ol>
            
            <div class="well well-sm">
                <strong>Database Query:</strong><br>
                <code>
                SELECT po.po_number, po.status, a.asset_tag, a.name as asset_name<br>
                FROM assets a<br>
                JOIN purchase_orders po ON a.purchase_order_id = po.id<br>
                WHERE a.asset_tag = '${assetIdentifier}' OR a.id = '${assetIdentifier}';
                </code>
            </div>
            
            <p><strong>Quick Navigation:</strong></p>
            <a href="/purchase-orders" class="btn btn-primary" target="_blank">
                <i class="fas fa-list"></i> View All Purchase Orders
            </a>
            <a href="/hardware" class="btn btn-info" target="_blank">
                <i class="fas fa-laptop"></i> View All Assets
            </a>
        </div>
    `;
    
    $('#lookup-content').html(lookupHtml);
}
</script>
