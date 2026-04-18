@extends('layouts.admission')

@section('title', 'Offer Letters')

@section('styles')
<style>
    /* Custom styles for offer letters page */
    .btn-loading {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .send-single, #bulkSendSubmitBtn {
        transition: all 0.3s ease;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .action-buttons .btn {
        padding: 4px 8px;
        font-size: 11px;
    }
    
    .info-box {
        transition: transform 0.2s;
        cursor: pointer;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .info-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .info-box-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 24px;
        color: white;
    }
    
    .info-box-icon.bg-success { background: #28a745; }
    .info-box-icon.bg-info { background: #17a2b8; }
    .info-box-icon.bg-warning { background: #ffc107; }
    
    .info-box-content {
        flex: 1;
        padding-left: 15px;
    }
    
    .info-box-text {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .info-box-number {
        font-size: 24px;
        font-weight: bold;
        color: #2c3e50;
    }
    
    /* Loading overlay */
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    .loading-content {
        background: white;
        padding: 30px 40px;
        border-radius: 10px;
        text-align: center;
    }
    
    /* Toast notifications */
    #toastContainer {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 10000;
    }
    
    .toast-custom {
        min-width: 300px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        margin-bottom: 10px;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    /* Modal styling */
    .modal-custom {
        display: none;
        position: fixed;
        z-index: 10050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-custom-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        animation: modalopen 0.3s;
    }
    
    @keyframes modalopen {
        from { opacity: 0; transform: translateY(-50px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .modal-header {
        padding: 15px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        border-radius: 0 0 8px 8px;
        text-align: right;
    }
    
    .close-modal {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close-modal:hover {
        color: #000;
    }
    
    .contact-info {
        font-size: 12px;
    }
    
    .has-email {
        color: #28a745;
    }
    
    .has-phone {
        color: #17a2b8;
    }
    
    .no-contact {
        color: #dc3545;
    }
    
    /* Disabled button style */
    button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards Row -->
    <div class="row">
        <div class="col-md-4">
            <div class="info-box d-flex align-items-center" onclick="filterByStatus('approved')">
                <div class="info-box-icon bg-success">
                    <i class="feather-users"></i>
                </div>
                <div class="info-box-content">
                    <span class="info-box-text">Total Approved</span>
                    <span class="info-box-number">{{ $statistics['total_approved'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box d-flex align-items-center" onclick="filterByStatus('sent')">
                <div class="info-box-icon bg-info">
                    <i class="feather-mail"></i>
                </div>
                <div class="info-box-content">
                    <span class="info-box-text">Letters Sent</span>
                    <span class="info-box-number">{{ $statistics['letters_sent'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box d-flex align-items-center" onclick="filterByStatus('pending')">
                <div class="info-box-icon bg-warning">
                    <i class="feather-clock"></i>
                </div>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ $statistics['letters_pending'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">
                <i class="feather-mail text-primary"></i> Admission Offer Letters
            </h4>
            <div>
                <button type="button" class="btn btn-primary btn-sm" id="bulkSendBtn">
                    <i class="feather-mail"></i> Bulk Send
                </button>
            </div>
        </div>
        <div class="card-body">
            
            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" id="filterForm" action="{{ route('admission.offers.letters') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Programme</label>
                            <select name="programme_id" class="form-select">
                                <option value="">All Programmes</option>
                                @foreach($programmes as $programme)
                                    <option value="{{ $programme->id }}" {{ ($selectedProgrammeId ?? '') == $programme->id ? 'selected' : '' }}>
                                        {{ $programme->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Intake</label>
                            <select name="intake" class="form-select">
                                <option value="March" {{ ($selectedIntake ?? 'March') == 'March' ? 'selected' : '' }}>March</option>
                                <option value="September" {{ ($selectedIntake ?? 'March') == 'September' ? 'selected' : '' }}>September</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Letter Status</label>
                            <select name="letter_status" class="form-select" id="letter_status_filter">
                                <option value="">All</option>
                                <option value="sent" {{ ($letterStatus ?? '') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="pending" {{ ($letterStatus ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Name, App No, Phone, Email..." value="{{ $search ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary form-control">
                                <i class="feather-filter"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Selected Count Display -->
            <div id="selectedCount" class="alert alert-info" style="display: none;">
                <i class="feather-check-circle"></i> 
                <span id="selectedCountText">0</span> applicant(s) selected
                <button type="button" class="btn btn-sm btn-secondary float-end" id="clearSelectionBtn">Clear</button>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>App No.</th>
                            <th>Full Name</th>
                            <th>Programme</th>
                            <th>Contact Information</th>
                            <th>Letter Status</th>
                            <th width="220">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                            <tr>
                                <td>
                                    <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" class="app-checkbox form-check-input"
                                           data-has-email="{{ $app->email ? 'true' : 'false' }}"
                                           data-has-phone="{{ $app->phone ? 'true' : 'false' }}">
                                </td>
                                <td>{{ $app->application_number }}</td>
                                <td>
                                    <strong>{{ $app->first_name }} {{ $app->middle_name }} {{ $app->last_name }}</strong>
                                </td>
                                <td>{{ $app->programme_name }}</td>
                                <td>
                                    <div class="contact-info">
                                        @if($app->email)
                                            <div class="has-email">
                                                <i class="feather-mail"></i> 
                                                <small>{{ $app->email }}</small>
                                                <span class="badge bg-success" style="font-size: 9px;">Email</span>
                                            </div>
                                        @endif
                                        @if($app->phone)
                                            <div class="has-phone mt-1">
                                                <i class="feather-phone"></i> 
                                                <small>{{ $app->phone }}</small>
                                                <span class="badge bg-info" style="font-size: 9px;">SMS</span>
                                            </div>
                                        @endif
                                        @if(!$app->phone && !$app->email)
                                            <div class="no-contact">
                                                <i class="feather-alert-circle"></i> 
                                                <strong>No contact info!</strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($app->admission_letter_sent_at)
                                        <span class="badge bg-success">
                                            <i class="feather-check"></i> Sent
                                        </span>
                                        <br>
                                        <small>{{ \Carbon\Carbon::parse($app->admission_letter_sent_at)->format('d/m/Y H:i') }}</small>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="feather-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admission.offers.letter.preview', $app->id) }}" 
                                           class="btn btn-sm btn-info" target="_blank" title="Preview Letter">
                                            <i class="feather-eye"></i> Preview
                                        </a>
                                        <a href="{{ route('admission.offers.letter.generate', $app->id) }}" 
                                           class="btn btn-sm btn-secondary" title="Download PDF">
                                            <i class="feather-download"></i> PDF
                                        </a>
                                        @if(!$app->admission_letter_sent_at)
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary send-single" 
                                                    data-id="{{ $app->id }}"
                                                    data-name="{{ $app->first_name }} {{ $app->last_name }}"
                                                    data-email="{{ $app->email }}"
                                                    data-phone="{{ $app->phone }}"
                                                    data-has-email="{{ $app->email ? 'true' : 'false' }}"
                                                    data-has-phone="{{ $app->phone ? 'true' : 'false' }}">
                                                <i class="feather-mail"></i> Send
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="feather-inbox fa-3x text-muted"></i>
                                    <p class="mt-2">No approved applications found.</p>
                                    <small class="text-muted">Try changing your filters or check back later.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Bulk Action Buttons -->
            <div class="row mt-3 align-items-center">
                <div class="col-md-6">
                    <button type="button" class="btn btn-primary" id="bulkSendSubmitBtn">
                        <i class="feather-mail"></i> Send to Selected
                    </button>
                    <button type="button" class="btn btn-secondary ms-2" id="checkContactsBtn">
                        <i class="feather-check-circle"></i> Check Contacts
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Showing {{ $applications->firstItem() ?? 0 }} to {{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }} entries
                    </small>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="sendModal" class="modal-custom">
    <div class="modal-custom-content">
        <div class="modal-header">
            <h5 class="modal-title">Confirm Sending Offer Letter</h5>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div id="modalMessage"></div>
            <div id="modalContactInfo" class="mt-3"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelSendBtn">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmSendBtn">Send Now</button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Processing... Please wait</p>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    console.log('Offer Letters page loaded - Ready for action');
    
    // Variables for modal
    var pendingSendId = null;
    var pendingSendName = null;
    
    // Function to show toast
    window.showToast = function(message, type = 'success') {
        var bgColor = type === 'success' ? '#28a745' : (type === 'error' ? '#dc3545' : (type === 'warning' ? '#ffc107' : '#17a2b8'));
        var textColor = type === 'warning' ? '#000' : '#fff';
        var icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'alert-circle' : (type === 'warning' ? 'alert-triangle' : 'info'));
        
        var toastId = 'toast_' + Date.now();
        var toastHtml = `
            <div id="${toastId}" class="toast-custom" style="background: ${bgColor}; color: ${textColor}; padding: 12px 20px; margin-bottom: 10px; border-radius: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="feather-${icon}"></i>
                    <strong>${message}</strong>
                    <button type="button" style="margin-left: auto; background: none; border: none; color: ${textColor}; cursor: pointer; font-size: 20px;" onclick="$('#${toastId}').remove()">×</button>
                </div>
            </div>
        `;
        
        $('#toastContainer').append(toastHtml);
        
        setTimeout(function() {
            $('#' + toastId).fadeOut(function() { $(this).remove(); });
        }, 5000);
    };
    
    // Update selected count function
    function updateSelectedCount() {
        var count = $('.app-checkbox:checked').length;
        if (count > 0) {
            $('#selectedCount').fadeIn();
            $('#selectedCountText').text(count);
        } else {
            $('#selectedCount').fadeOut();
        }
    }
    
    // Select All functionality
    $('#selectAll').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.app-checkbox').prop('checked', isChecked);
        updateSelectedCount();
    });
    
    // Individual checkbox change
    $(document).on('change', '.app-checkbox', function() {
        updateSelectedCount();
        var total = $('.app-checkbox').length;
        var checked = $('.app-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
    });
    
    // Clear selection
    $('#clearSelectionBtn').on('click', function() {
        $('.app-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateSelectedCount();
        showToast('Selection cleared', 'info');
    });
    
    // Check Contacts button
    $('#checkContactsBtn').on('click', function() {
        var selectedRows = $('.app-checkbox:checked');
        var count = selectedRows.length;
        
        if (count === 0) {
            showToast('Please select at least one applicant to check contacts.', 'warning');
            return;
        }
        
        var hasEmail = 0;
        var hasPhone = 0;
        var hasBoth = 0;
        var hasNone = 0;
        
        selectedRows.each(function() {
            var hasEmailVal = $(this).data('has-email') === 'true';
            var hasPhoneVal = $(this).data('has-phone') === 'true';
            
            if (hasEmailVal && hasPhoneVal) hasBoth++;
            else if (hasEmailVal) hasEmail++;
            else if (hasPhoneVal) hasPhone++;
            else hasNone++;
        });
        
        var message = `Contact Information Summary for ${count} selected applicant(s):\n\n`;
        message += `✅ Both Email & Phone: ${hasBoth}\n`;
        message += `📧 Email only: ${hasEmail}\n`;
        message += `📱 Phone only: ${hasPhone}\n`;
        message += `❌ No contact info: ${hasNone}\n\n`;
        
        if (hasNone > 0) {
            message += `⚠️ Warning: ${hasNone} applicant(s) have no contact information!\n`;
            message += `Please update their contact info before sending letters.`;
        } else {
            message += `✓ All selected applicants have contact information.`;
        }
        
        alert(message);
    });
    
    // Modal functions
    function openModal(message, contactInfo, id, name) {
        $('#modalMessage').html(message);
        $('#modalContactInfo').html(contactInfo);
        pendingSendId = id;
        pendingSendName = name;
        $('#sendModal').fadeIn();
    }
    
    function closeModal() {
        $('#sendModal').fadeOut();
        pendingSendId = null;
        pendingSendName = null;
    }
    
    $('.close-modal, #cancelSendBtn').on('click', function() {
        closeModal();
    });
    
    $(window).on('click', function(event) {
        if ($(event.target).is('#sendModal')) {
            closeModal();
        }
    });
    
    // Single Send button
    $(document).on('click', '.send-single', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var id = $(this).data('id');
        var name = $(this).data('name');
        var email = $(this).data('email');
        var phone = $(this).data('phone');
        var hasEmail = $(this).data('has-email') === 'true';
        var hasPhone = $(this).data('has-phone') === 'true';
        
        console.log('Send button clicked for ID:', id);
        
        if (!hasEmail && !hasPhone) {
            showToast('Cannot send: No contact information available for this applicant', 'error');
            return false;
        }
        
        var message = `<strong>Send offer letter to ${name}?</strong><br><br>`;
        var contactInfo = '<div class="alert alert-info"><strong>Contact Information:</strong><br>';
        
        if (hasEmail) {
            contactInfo += `<i class="feather-mail"></i> Email: ${email}<br>`;
        } else {
            contactInfo += `<i class="feather-x"></i> <span class="text-danger">No email address found!</span><br>`;
        }
        
        if (hasPhone) {
            contactInfo += `<i class="feather-phone"></i> Phone: ${phone}<br>`;
        } else {
            contactInfo += `<i class="feather-x"></i> <span class="text-danger">No phone number found!</span><br>`;
        }
        
        contactInfo += '</div>';
        
        if (!hasEmail) {
            message += '<div class="alert alert-warning"><strong>⚠️ Note:</strong> No email address found. Only SMS will be sent.</div>';
        }
        
        if (!hasPhone) {
            message += '<div class="alert alert-warning"><strong>⚠️ Note:</strong> No phone number found. Only email will be sent.</div>';
        }
        
        message += '<div class="alert alert-success"><strong>What will be sent:</strong><br>';
        if (hasEmail) message += '📧 Admission offer letter via email (with PDF attachment)<br>';
        if (hasPhone) message += '📱 SMS notification with congratulations message<br>';
        message += '</div>';
        
        openModal(message, contactInfo, id, name);
    });
    
    // Confirm send from modal
    $('#confirmSendBtn').on('click', function() {
        if (!pendingSendId) {
            closeModal();
            return;
        }
        
        var id = pendingSendId;
        var name = pendingSendName;
        closeModal();
        
        var $btn = $(`.send-single[data-id="${id}"]`);
        var originalText = $btn.html();
        
        $btn.prop('disabled', true);
        $btn.html('<i class="feather-loader fa-spin"></i> Sending...');
        
        $.ajax({
            url: '{{ url("/admission/offers/letter/send") }}/' + id,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(response) {
                console.log('AJAX Success:', response);
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showToast('Error: ' + response.message, 'error');
                    $btn.prop('disabled', false);
                    $btn.html(originalText);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr);
                var errorMsg = 'Failed to send letter. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showToast('Error: ' + errorMsg, 'error');
                $btn.prop('disabled', false);
                $btn.html(originalText);
            }
        });
    });
    
    // Bulk Send button
    $('#bulkSendSubmitBtn').on('click', function(e) {
        e.preventDefault();
        
        var selectedIds = [];
        var selectedRows = $('.app-checkbox:checked');
        
        selectedRows.each(function() {
            selectedIds.push($(this).val());
        });
        
        var count = selectedIds.length;
        
        if (count === 0) {
            showToast('Please select at least one applicant to send letters.', 'warning');
            return;
        }
        
        var hasEmail = 0;
        var hasPhone = 0;
        var hasNone = 0;
        
        selectedRows.each(function() {
            var hasEmailVal = $(this).data('has-email') === 'true';
            var hasPhoneVal = $(this).data('has-phone') === 'true';
            
            if (hasEmailVal) hasEmail++;
            if (hasPhoneVal) hasPhone++;
            if (!hasEmailVal && !hasPhoneVal) hasNone++;
        });
        
        var message = `Send offer letters to ${count} selected applicant(s)?\n\n`;
        message += `Contact Summary:\n`;
        message += `- With email: ${hasEmail}\n`;
        message += `- With phone: ${hasPhone}\n`;
        message += `- Without any contact: ${hasNone}\n\n`;
        
        if (hasNone > 0) {
            message += `⚠️ WARNING: ${hasNone} applicant(s) have NO contact information!\n`;
            message += `These will FAIL to send.\n\n`;
        }
        
        message += `This will send:\n`;
        message += `📧 Email notifications with PDF attachment to applicants with email\n`;
        message += `📱 SMS notifications to applicants with phone numbers\n\n`;
        message += `Continue?`;
        
        if (!confirm(message)) {
            return;
        }
        
        $('#loadingOverlay').fadeIn();
        
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true);
        $btn.html('<i class="feather-loader fa-spin"></i> Sending...');
        
        $.ajax({
            url: '{{ route("admission.offers.letters.bulk-send") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                application_ids: selectedIds
            },
            dataType: 'json',
            success: function(response) {
                console.log('Bulk send success:', response);
                $('#loadingOverlay').fadeOut();
                
                if (response.success) {
                    showToast(response.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showToast('Error: ' + response.message, 'error');
                    $btn.prop('disabled', false);
                    $btn.html(originalText);
                }
            },
            error: function(xhr) {
                console.log('Bulk send error:', xhr);
                $('#loadingOverlay').fadeOut();
                
                var errorMsg = 'Failed to send letters. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showToast('Error: ' + errorMsg, 'error');
                $btn.prop('disabled', false);
                $btn.html(originalText);
            }
        });
    });
    
    // Top Bulk Send button
    $('#bulkSendBtn').on('click', function(e) {
        e.preventDefault();
        $('#bulkSendSubmitBtn').click();
    });
    
    // Filter by status function
    window.filterByStatus = function(status) {
        if (status === 'sent') {
            $('#letter_status_filter').val('sent');
        } else if (status === 'pending') {
            $('#letter_status_filter').val('pending');
        } else {
            $('#letter_status_filter').val('');
        }
        $('#filterForm').submit();
    };
    
    // Initialize
    updateSelectedCount();
    
    console.log('Send buttons found:', $('.send-single').length);
    console.log('Bulk send button exists:', $('#bulkSendSubmitBtn').length > 0);
});
</script>
@endsection