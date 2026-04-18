@extends('layouts.admission')

@section('title', 'Frequently Asked Questions')

@section('styles')
<style>
    .faq-accordion .card {
        border: 1px solid #e9ecef;
        margin-bottom: 15px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .faq-accordion .card-header {
        background: #f8f9fa;
        padding: 0;
    }
    
    .faq-accordion .btn-link {
        color: #2c3e50;
        font-weight: 600;
        text-align: left;
        text-decoration: none;
        width: 100%;
        padding: 15px 20px;
        font-size: 16px;
        white-space: normal;
        word-wrap: break-word;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .faq-accordion .btn-link:hover {
        color: #007bff;
        background: #fff;
    }
    
    .faq-accordion .btn-link .faq-icon {
        font-size: 18px;
        transition: transform 0.3s;
    }
    
    .faq-accordion .btn-link.collapsed .faq-icon {
        transform: rotate(0deg);
    }
    
    .faq-accordion .btn-link:not(.collapsed) .faq-icon {
        transform: rotate(90deg);
    }
    
    .faq-accordion .card-body {
        padding: 20px;
        background: #fff;
        border-top: 1px solid #e9ecef;
        line-height: 1.6;
        color: #555;
    }
    
    .category-tabs .nav-link {
        color: #6c757d;
        font-weight: 500;
        padding: 10px 20px;
        border-radius: 30px;
        margin-right: 10px;
    }
    
    .category-tabs .nav-link.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .category-tabs .nav-link:hover:not(.active) {
        background: #e9ecef;
        color: #007bff;
    }
    
    .search-box {
        max-width: 500px;
        margin: 0 auto;
    }
    
    .search-box .input-group {
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        border-radius: 50px;
        overflow: hidden;
    }
    
    .search-box input {
        border: none;
        padding: 12px 20px;
    }
    
    .search-box button {
        padding: 0 25px;
        border-radius: 0;
    }
    
    mark {
        background: #fff3cd;
        padding: 2px 4px;
        border-radius: 3px;
    }
    
    .still-need-help {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 30px;
        color: white;
        text-align: center;
        margin-top: 30px;
    }
    
    .still-need-help h4 {
        color: white;
        margin-bottom: 15px;
    }
    
    .still-need-help p {
        color: rgba(255,255,255,0.9);
        margin-bottom: 20px;
    }
    
    .still-need-help .btn {
        background: white;
        color: #667eea;
        border: none;
        padding: 10px 30px;
        font-weight: 600;
    }
    
    .still-need-help .btn:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="feather-help-circle text-primary"></i> Frequently Asked Questions
                    </h4>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#supportTicketModal">
                        <i class="feather-help-circle"></i> Still Need Help?
                    </button>
                </div>
                <div class="card-body">
                    
                    <!-- Search Box -->
                    <div class="search-box mb-4">
                        <form method="GET" action="{{ route('admission.support.faq') }}" id="searchForm">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search FAQs..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="feather-search"></i> Search
                                    </button>
                                </div>
                                @if(request('search'))
                                    <div class="input-group-append">
                                        <a href="{{ route('admission.support.faq') }}" class="btn btn-secondary">
                                            <i class="feather-x"></i> Clear
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                    
                    <!-- Category Tabs -->
                    <ul class="nav nav-tabs category-tabs mb-4">
                        <li class="nav-item">
                            <a class="nav-link {{ !request('category') ? 'active' : '' }}" 
                               href="{{ route('admission.support.faq') }}">
                                All FAQs
                            </a>
                        </li>
                        @foreach($categories as $cat)
                            <li class="nav-item">
                                <a class="nav-link {{ request('category') == $cat ? 'active' : '' }}" 
                                   href="{{ route('admission.support.faq', ['category' => $cat]) }}">
                                    {{ ucfirst($cat) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    
                    <!-- Search Results Info -->
                    @if(request('search'))
                        <div class="alert alert-info">
                            <i class="feather-search"></i> 
                            Search results for: <strong>"{{ request('search') }}"</strong>
                            <a href="{{ route('admission.support.faq') }}" class="float-right">Clear search</a>
                        </div>
                    @endif
                    
                    @if(request('category'))
                        <div class="alert alert-secondary">
                            <i class="feather-folder"></i> 
                            Showing FAQs in category: <strong>{{ ucfirst(request('category')) }}</strong>
                        </div>
                    @endif
                    
                    <!-- FAQ Accordion -->
                    @forelse($faqsByCategory as $catName => $faqs)
                        <div class="mb-5">
                            <h4 class="mb-3 pb-2 border-bottom">
                                <i class="feather-folder text-primary"></i> {{ ucfirst($catName) }}
                            </h4>
                            <div class="faq-accordion" id="accordion{{ Str::slug($catName) }}">
                                @foreach($faqs as $index => $faq)
                                    <div class="card">
                                        <div class="card-header" id="heading{{ $faq->id }}">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link {{ $index !== 0 ? 'collapsed' : '' }}" 
                                                        type="button" data-toggle="collapse" 
                                                        data-target="#collapse{{ $faq->id }}" 
                                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                                        aria-controls="collapse{{ $faq->id }}">
                                                    <span>
                                                        <i class="feather-help-circle text-primary mr-2"></i>
                                                        {!! request('search') ? preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark>$1</mark>', e($faq->question)) : e($faq->question) !!}
                                                    </span>
                                                    <i class="feather-chevron-right faq-icon"></i>
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="collapse{{ $faq->id }}" 
                                             class="collapse {{ $index === 0 ? 'show' : '' }}" 
                                             aria-labelledby="heading{{ $faq->id }}" 
                                             data-parent="#accordion{{ Str::slug($catName) }}">
                                            <div class="card-body">
                                                {!! request('search') ? preg_replace('/(' . preg_quote(request('search'), '/') . ')/i', '<mark>$1</mark>', nl2br(e($faq->answer))) : nl2br(e($faq->answer)) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-warning text-center py-5">
                            <i class="feather-alert-circle fa-3x mb-3 d-block"></i>
                            <h5>No FAQs Found</h5>
                            <p>We couldn't find any FAQs matching your criteria.</p>
                            <a href="{{ route('admission.support.faq') }}" class="btn btn-primary">
                                <i class="feather-refresh-cw"></i> View All FAQs
                            </a>
                        </div>
                    @endforelse
                    
                    <!-- Still Need Help Section -->
                    <div class="still-need-help">
                        <i class="feather-message-square fa-3x mb-3"></i>
                        <h4>Still have questions?</h4>
                        <p>Can't find what you're looking for? Our support team is here to help!</p>
                        <button type="button" class="btn" data-toggle="modal" data-target="#supportTicketModal">
                            <i class="feather-help-circle"></i> Contact Support
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Support Ticket Modal -->
<div class="modal fade" id="supportTicketModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="feather-help-circle text-primary"></i> Submit Support Ticket
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="ticketForm" method="POST" action="{{ route('admission.support.ticket.submit') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="feather-info"></i> Our support team will respond within 24 hours.
                    </div>
                    
                    <div class="form-group">
                        <label>Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" required 
                               placeholder="Brief description of your issue">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority <span class="text-danger">*</span></label>
                                <select name="priority" class="form-control" required>
                                    <option value="low">Low - General question</option>
                                    <option value="medium">Medium - Need assistance</option>
                                    <option value="high">High - Urgent matter</option>
                                    <option value="urgent">Urgent - Critical issue</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-control" required>
                                    <option value="technical">Technical Issue</option>
                                    <option value="bug">Bug Report</option>
                                    <option value="feature_request">Feature Request</option>
                                    <option value="account">Account Issue</option>
                                    <option value="payment">Payment Issue</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Message <span class="text-danger">*</span></label>
                        <textarea name="message" class="form-control" rows="6" required 
                                  placeholder="Please provide detailed information about your issue..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="feather-x"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitTicketBtn">
                        <i class="feather-send"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Auto-expand FAQ from URL hash
    if (window.location.hash) {
        var target = window.location.hash.substring(1);
        $('#' + target).collapse('show');
    }
    
    // Smooth scroll to expanded FAQ
    $('.faq-accordion .btn-link').on('click', function() {
        var target = $(this).data('target');
        setTimeout(function() {
            if ($(target).hasClass('show')) {
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 100
                }, 500);
            }
        }, 300);
    });
    
    // Form submission with AJAX
    $('#ticketForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submitTicketBtn');
        var originalText = submitBtn.html();
        
        // Disable button and show loading
        submitBtn.prop('disabled', true).html('<i class="feather-loader fa-spin"></i> Submitting...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message
                    toastr.success(response.message);
                    
                    // Reset form and close modal
                    form[0].reset();
                    $('#supportTicketModal').modal('hide');
                    
                    // Optional: Show ticket number
                    if (response.ticket_number) {
                        toastr.info('Your ticket number: ' + response.ticket_number);
                    }
                } else {
                    toastr.error(response.message || 'Failed to submit ticket');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Failed to submit ticket';
                
                if (xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        errorMsg = '';
                        $.each(errors, function(key, value) {
                            errorMsg += value[0] + '\n';
                        });
                    }
                    toastr.error(errorMsg);
                } else if (xhr.status === 403) {
                    toastr.error('Session expired. Please refresh the page and try again.');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                }
            },
            complete: function() {
                // Re-enable button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Reset modal when closed
    $('#supportTicketModal').on('hidden.bs.modal', function() {
        $('#ticketForm')[0].reset();
        $('#ticketForm .is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });
});
</script>
@endsection