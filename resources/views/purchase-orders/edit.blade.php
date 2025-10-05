@extends('layouts/default')

@section('title')
    Edit Purchase Order {{ $purchaseOrder->po_number }}
@parent
@stop

@section('header_right')
    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-default pull-right">
        {{ trans('general.back') }}
    </a>
@stop

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Edit Purchase Order: {{ $purchaseOrder->po_number }}</h3>
            </div>
            
            <form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder) }}" id="purchase-order-form">
                @csrf
                @method('PUT')
                
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>PO Number</label>
                                <input type="text" class="form-control" value="{{ $purchaseOrder->po_number }}" readonly>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group {{ $errors->has('supplier_id') ? 'has-error' : '' }}">
                                <label for="supplier_id">Supplier *</label>
                                <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                    <option value="">Select a supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                                {{ (old('supplier_id', $purchaseOrder->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                {!! $errors->first('supplier_id', '<span class="alert-msg">:message</span>') !!}
                                <small class="help-block">Asset models will be filtered based on selected supplier</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label for="status">Status *</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="draft" {{ old('status', $purchaseOrder->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="pending" {{ old('status', $purchaseOrder->status) == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="approved" {{ old('status', $purchaseOrder->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="ordered" {{ old('status', $purchaseOrder->status) == 'ordered' ? 'selected' : '' }}>Ordered</option>
                                    <option value="received" {{ old('status', $purchaseOrder->status) == 'received' ? 'selected' : '' }}>Received</option>
                                    <option value="cancelled" {{ old('status', $purchaseOrder->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                {!! $errors->first('status', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group {{ $errors->has('order_date') ? 'has-error' : '' }}">
                                <label for="order_date">Order Date *</label>
                                <input type="date" name="order_date" id="order_date" class="form-control" 
                                       value="{{ old('order_date', $purchaseOrder->order_date ? $purchaseOrder->order_date->format('Y-m-d') : '') }}" required>
                                {!! $errors->first('order_date', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('expected_delivery_date') ? 'has-error' : '' }}">
                                <label for="expected_delivery_date">Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" id="expected_delivery_date" 
                                       class="form-control" 
                                       value="{{ old('expected_delivery_date', $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('Y-m-d') : '') }}">
                                {!! $errors->first('expected_delivery_date', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group {{ $errors->has('notes') ? 'has-error' : '' }}">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                                {!! $errors->first('notes', '<span class="alert-msg">:message</span>') !!}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h4>Items</h4>
                    <div id="items-container">
                        @foreach($purchaseOrder->items as $index => $item)
                            <div class="item-row" data-index="{{ $index }}">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Asset Model</label>
                                            <select name="items[{{ $index }}][asset_model_id]" 
                                                    class="form-control asset-model-select"
                                                    data-current-value="{{ $item->asset_model_id }}">
                                                <option value="">Loading models...</option>
                                            </select>
                                            <small class="help-block">Models filtered by selected supplier</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Model Name *</label>
                                            <input type="text" name="items[{{ $index }}][model_name]" class="form-control model-name-input" 
                                                   placeholder="Enter model name" value="{{ $item->model_name }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Quantity *</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" 
                                                   min="1" value="{{ $item->quantity }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Unit Price *</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price-input" 
                                                   min="0" step="0.01" value="{{ $item->unit_price }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-block remove-item">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-11">
                                        <div class="form-group">
                                            <label>Item Notes</label>
                                            <input type="text" name="items[{{ $index }}][notes]" class="form-control" 
                                                   placeholder="Optional notes for this item" value="{{ $item->notes }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="item-separator">
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" id="add-item" class="btn btn-success">
                                <i class="fa fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="pull-right">
                                <h4>Total: $<span id="total-amount">{{ number_format($purchaseOrder->total_amount, 2) }}</span></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check"></i> Update Purchase Order
                    </button>
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-default">
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
    let itemIndex = {{ $purchaseOrder->items->count() }};
    let supplierModels = [];
    let initialSupplierId = $('#supplier_id').val();

    // Initialize Select2
    $('.select2').select2();

    // Load initial supplier models if supplier is already selected
    if (initialSupplierId) {
        loadSupplierModels(initialSupplierId, true);
    }

    // Supplier change handler
    $('#supplier_id').change(function() {
        const supplierId = $(this).val();
        
        if (supplierId) {
            loadSupplierModels(supplierId);
        } else {
            clearAssetModelSelects();
        }
    });

    // Load asset models for selected supplier
    function loadSupplierModels(supplierId, isInitial = false) {
        // Show loading state
        if (!isInitial) {
            $('.asset-model-select').prop('disabled', true).html('<option value="">Loading...</option>');
        }
        
        $.ajax({
            url: `/api/purchase-orders/suppliers/${supplierId}/models`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    supplierModels = response.models;
                    updateAssetModelSelects(isInitial);
                    
                    if (response.models.length === 0) {
                        showNoModelsMessage();
                    }
                } else {
                    showErrorMessage('Error loading models: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                showErrorMessage('Error loading asset models. Please try again.');
                console.error('AJAX Error:', error);
            }
        });
    }

    // Update all asset model select dropdowns
    function updateAssetModelSelects(preserveValues = false) {
        $('.asset-model-select').each(function() {
            const $select = $(this);
            const currentValue = preserveValues ? $select.data('current-value') || $select.val() : null;
            
            // Clear and populate options
            $select.empty().append('<option value="">Select model (optional)</option>');
            
            supplierModels.forEach(function(model) {
                const isSelected = currentValue && currentValue == model.id ? 'selected' : '';
                $select.append(`<option value="${model.id}" data-name="${model.name}" ${isSelected}>${model.display_name}</option>`);
            });
            
            $select.prop('disabled', false);
        });
    }

    // Clear asset model selects when no supplier selected
    function clearAssetModelSelects() {
        $('.asset-model-select').prop('disabled', true).html('<option value="">Select supplier first</option>');
        supplierModels = [];
    }

    // Show message when no models found
    function showNoModelsMessage() {
        $('.asset-model-select').html('<option value="">No models found for this supplier</option>');
    }

    // Show error message
    function showErrorMessage(message) {
        $('.asset-model-select').prop('disabled', false).html('<option value="">Error loading models</option>');
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
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + itemIndex + ']'));
            }
            if ($(this).is('input')) {
                $(this).val('');
                if ($(this).hasClass('quantity-input')) {
                    $(this).val('1');
                }
            } else if ($(this).is('select')) {
                $(this).val('');
            }
        });
        
        $('#items-container').append(newItem);
        
        // Update the new asset model select with current supplier models
        const newSelect = newItem.find('.asset-model-select');
        if (supplierModels.length > 0) {
            newSelect.empty().append('<option value="">Select model (optional)</option>');
            supplierModels.forEach(function(model) {
                newSelect.append(`<option value="${model.id}" data-name="${model.name}">${model.display_name}</option>`);
            });
            newSelect.prop('disabled', false);
        } else {
            newSelect.prop('disabled', true).html('<option value="">Select supplier first</option>');
        }
        
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

    // Auto-fill model name when asset model is selected
    $(document).on('change', '.asset-model-select', function() {
        const selectedOption = $(this).find('option:selected');
        const modelName = selectedOption.data('name');
        const modelNameInput = $(this).closest('.item-row').find('.model-name-input');
        
        if (modelName && !modelNameInput.val()) {
            modelNameInput.val(modelName);
        }
    });

    // Calculate total when quantity or unit price changes
    $(document).on('input', '.quantity-input, .unit-price-input', function() {
        calculateTotal();
    });

    // Calculate total amount
    function calculateTotal() {
        let total = 0;
        
        $('.item-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            const unitPrice = parseFloat($(this).find('.unit-price-input').val()) || 0;
            total += quantity * unitPrice;
        });
        
        $('#total-amount').text(total.toFixed(2));
    }

    // Form validation
    $('#purchase-order-form').submit(function(e) {
        let isValid = true;
        let errorMessage = '';

        // Check if supplier is selected
        if (!$('#supplier_id').val()) {
            isValid = false;
            errorMessage += 'Please select a supplier.\n';
        }

        // Check if at least one item exists
        if ($('.item-row').length === 0) {
            isValid = false;
            errorMessage += 'At least one item is required.\n';
        }

        // Check each item
        $('.item-row').each(function(index) {
            const modelName = $(this).find('.model-name-input').val().trim();
            const quantity = $(this).find('.quantity-input').val();
            const unitPrice = $(this).find('.unit-price-input').val();

            if (!modelName) {
                isValid = false;
                errorMessage += `Item ${index + 1}: Model name is required.\n`;
            }
            if (!quantity || quantity < 1) {
                isValid = false;
                errorMessage += `Item ${index + 1}: Valid quantity is required.\n`;
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
