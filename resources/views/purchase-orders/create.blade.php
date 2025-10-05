@extends('layouts/default')

@section('title')
    Create Purchase Order
@parent
@stop

@section('header_right')
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-default pull-right">
        {{ trans('general.back') }}
    </a>
@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Create New Purchase Order</h3>
            </div>
            
            <form method="POST" action="{{ route('purchase-orders.store') }}" id="purchase-order-form">
                @csrf
                
                <div class="box-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('supplier_id') ? 'has-error' : '' }}">
                                <label for="supplier_id">Supplier *</label>
                                <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                    <option value="">Select a supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('supplier_id', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group {{ $errors->has('order_date') ? 'has-error' : '' }}">
                                <label for="order_date">Order Date *</label>
                                <input type="date" name="order_date" id="order_date" class="form-control" 
                                       value="{{ old('order_date', date('Y-m-d')) }}" required>
                                {!! $errors->first('order_date', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group {{ $errors->has('expected_delivery_date') ? 'has-error' : '' }}">
                                <label for="expected_delivery_date">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" 
                                       class="form-control" value="{{ old('expected_delivery_date') }}">
                                {!! $errors->first('expected_delivery_date', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group {{ $errors->has('notes') ? 'has-error' : '' }}">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                {!! $errors->first('notes', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4>Assets to Include in Purchase Order</h4>
                    <div id="supplier-warning" class="alert alert-warning" style="display: none;">
                        <i class="fa fa-warning"></i> Please select a supplier first to see available assets.
                    </div>
                    
                    <div id="items-container">
                        <div class="item-row" data-index="0">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Asset *</label>
                                        <select name="items[0][asset_id]" class="form-control asset-select" disabled required>
                                            <option value="">Select supplier first</option>
                                        </select>
                                        <small class="help-block">Choose a specific asset from the selected supplier</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Unit Price *</label>
                                        <input type="number" name="items[0][unit_price]" class="form-control unit-price-input" 
                                               min="0" step="0.01" placeholder="0.00" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <input type="text" name="items[0][notes]" class="form-control" 
                                               placeholder="Optional notes for this asset">
                                    </div>
                                </div>
                                
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-block remove-item" disabled>
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Hidden fields for backward compatibility --}}
                            <input type="hidden" name="items[0][model_name]" class="model-name-hidden" value="">
                            <input type="hidden" name="items[0][quantity]" class="quantity-hidden" value="1">
                            
                            <hr class="item-separator">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" id="add-item" class="btn btn-success">
                                <i class="fa fa-plus"></i> Add Asset
                            </button>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="pull-right">
                                <h4>Total: $<span id="total-amount">0.00</span></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i> Create Purchase Order
                    </button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-default">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('moar_scripts')
<script>
$(document).ready(function() {
    let itemIndex = 1;
    let supplierAssets = [];

    // Initialize Select2
    $('.select2').select2();

    // Handle supplier selection
    $('#supplier_id').change(function() {
        const supplierId = $(this).val();
        
        if (supplierId) {
            loadSupplierAssets(supplierId);
            $('#supplier-warning').hide();
        } else {
            clearAssetSelects();
            $('#supplier-warning').show();
        }
    });

    // Load assets for selected supplier
    function loadSupplierAssets(supplierId) {
        // Show loading state
        $('.asset-select').prop('disabled', true).html('<option value="">Loading assets...</option>');
        
        $.ajax({
            url: `/api/purchase-orders/suppliers/${supplierId}/assets`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    supplierAssets = response.assets;
                    updateAssetSelects();
                    
                    if (response.assets.length === 0) {
                        showNoAssetsMessage();
                    }
                } else {
                    showErrorMessage('Error loading assets: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showErrorMessage('Error loading assets. Please try again.');
                console.error('AJAX Error:', error);
            }
        });
    }

    // Update all asset select dropdowns
    function updateAssetSelects() {
        $('.asset-select').each(function() {
            const $select = $(this);
            const currentValue = $select.val();
            
            // Clear and populate options
            $select.html('<option value="">Select an asset</option>');
            
            supplierAssets.forEach(function(asset) {
                $select.append(`<option value="${asset.id}" data-model="${asset.model_name}">${asset.display_name}</option>`);
            });
            
            // Restore previous selection if still valid
            if (currentValue && supplierAssets.find(a => a.id == currentValue)) {
                $select.val(currentValue);
            }
            
            $select.prop('disabled', false);
        });
    }

    // Clear asset selects
    function clearAssetSelects() {
        $('.asset-select').prop('disabled', true).html('<option value="">Select supplier first</option>');
        supplierAssets = [];
    }

    // Show no assets message
    function showNoAssetsMessage() {
        $('.asset-select').html('<option value="">No available assets for this supplier</option>');
    }

    // Show error message
    function showErrorMessage(message) {
        $('.asset-select').html('<option value="">Error loading assets</option>');
        alert(message);
    }

    // Add new item
    $('#add-item').click(function() {
        const newItem = $('.item-row:first').clone();
        
        // Update indices and clear values
        newItem.attr('data-index', itemIndex);
        newItem.find('input, select').each(function() {
            const name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace('[0]', '[' + itemIndex + ']'));
            }
            if ($(this).is('input')) {
                // Set default values for hidden fields
                if ($(this).hasClass('quantity-hidden')) {
                    $(this).val('1');
                } else if ($(this).hasClass('model-name-hidden')) {
                    $(this).val('');
                } else {
                    $(this).val('');
                }
            } else if ($(this).is('select')) {
                $(this).val('');
            }
        });
        
        // Update asset select with current supplier's assets
        if (supplierAssets.length > 0) {
            const $newSelect = newItem.find('.asset-select');
            $newSelect.html('<option value="">Select an asset</option>');
            supplierAssets.forEach(function(asset) {
                $newSelect.append(`<option value="${asset.id}" data-model="${asset.model_name}">${asset.display_name}</option>`);
            });
            $newSelect.prop('disabled', false);
        }
        
        $('#items-container').append(newItem);
        itemIndex++;
        
        updateRemoveButtons();
        calculateTotal();
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        updateRemoveButtons();
        calculateTotal();
    });

    // Update remove buttons state
    function updateRemoveButtons() {
        const itemCount = $('.item-row').length;
        $('.remove-item').prop('disabled', itemCount <= 1);
    }

    // Handle asset selection to update hidden fields
    $(document).on('change', '.asset-select', function() {
        const $select = $(this);
        const selectedAssetId = $select.val();
        const $itemRow = $select.closest('.item-row');
        
        if (selectedAssetId) {
            // Find the selected asset data
            const selectedAsset = supplierAssets.find(asset => asset.id == selectedAssetId);
            
            if (selectedAsset) {
                // Update hidden model_name field
                $itemRow.find('.model-name-hidden').val(selectedAsset.model_name);
                
                // Quantity is always 1 for asset-based items
                $itemRow.find('.quantity-hidden').val(1);
            }
        } else {
            // Clear hidden fields if no asset selected
            $itemRow.find('.model-name-hidden').val('');
            $itemRow.find('.quantity-hidden').val('1');
        }
    });

    // Calculate total when unit price changes
    $(document).on('input', '.unit-price-input', function() {
        calculateTotal();
    });

    // Calculate total amount
    function calculateTotal() {
        let total = 0;
        
        $('.item-row').each(function() {
            const unitPrice = parseFloat($(this).find('.unit-price-input').val()) || 0;
            total += unitPrice; // Each asset is quantity 1
        });
        
        $('#total-amount').text(total.toFixed(2));
    }

    // Form validation
    $('#purchase-order-form').submit(function(e) {
        let isValid = true;
        let errorMessage = '';

        // Check if at least one item exists
        if ($('.item-row').length === 0) {
            isValid = false;
            errorMessage += 'At least one asset is required.\n';
        }

        // Check each item
        $('.item-row').each(function(index) {
            const assetId = $(this).find('.asset-select').val();
            const unitPrice = $(this).find('.unit-price-input').val();

            if (!assetId) {
                isValid = false;
                errorMessage += `Item ${index + 1}: Asset selection is required.\n`;
            }
            if (!unitPrice || unitPrice < 0) {
                isValid = false;
                errorMessage += `Item ${index + 1}: Valid unit price is required.\n`;
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errorMessage);
        }
    });

    // Initialize
    updateRemoveButtons();
    calculateTotal();
});
</script>
@stop
