@extends('layouts.admission')

@section('title', 'Help Center')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Help Center</h4>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#supportTicketModal">
                            <i class="feather-help-circle"></i> Submit Ticket
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search Bar -->
                    <div class="row mb-4">
                        <div class="col-md-8 mx-auto">
                            <form method="GET" action="{{ route('admission.support.help-center') }}">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control form-control-lg" placeholder="Search for help articles..." value="{{ $search }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary btn-lg" type="submit">
                                            <i class="feather-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Categories Sidebar -->
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Categories</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <a href="{{ route('admission.support.help-center') }}" class="list-group-item list-group-item-action {{ !$category ? 'active' : '' }}">
                                            All Articles
                                        </a>
                                        @foreach($categories as $cat)
                                            <a href="{{ route('admission.support.help-center', ['category' => $cat->category]) }}" 
                                               class="list-group-item list-group-item-action {{ $category == $cat->category ? 'active' : '' }}">
                                                {{ ucfirst($cat->category) }}
                                                <span class="badge badge-primary float-right">{{ $cat->total }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Popular Articles -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Popular Articles</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        @foreach($popularArticles as $article)
                                            <a href="{{ route('admission.support.article', $article->id) }}" class="list-group-item list-group-item-action">
                                                <i class="feather-file-text"></i> {{ Str::limit($article->title, 40) }}
                                                <br><small class="text-muted"><i class="feather-eye"></i> {{ $article->views }} views</small>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Articles -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">Recent Articles</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        @foreach($recentArticles as $article)
                                            <a href="{{ route('admission.support.article', $article->id) }}" class="list-group-item list-group-item-action">
                                                <i class="feather-clock"></i> {{ Str::limit($article->title, 40) }}
                                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($article->created_at)->diffForHumans() }}</small>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Articles List -->
                        <div class="col-md-9">
                            @if($search)
                                <div class="alert alert-info">
                                    <i class="feather-search"></i> Search results for: "{{ $search }}"
                                </div>
                            @endif
                            
                            @forelse($articles as $article)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5>
                                            <a href="{{ route('admission.support.article', $article->id) }}" class="text-dark">
                                                {{ $article->title }}
                                            </a>
                                        </h5>
                                        <p class="text-muted">{{ Str::limit($article->content, 200) }}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge badge-info">{{ ucfirst($article->category) }}</span>
                                                <span class="badge badge-secondary">
                                                    <i class="feather-eye"></i> {{ $article->views }} views
                                                </span>
                                                <span class="badge badge-light">
                                                    <i class="feather-calendar"></i> {{ \Carbon\Carbon::parse($article->created_at)->format('d M Y') }}
                                                </span>
                                            </div>
                                            <a href="{{ route('admission.support.article', $article->id) }}" class="btn btn-sm btn-primary">
                                                Read More <i class="feather-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-warning text-center">
                                    <i class="feather-alert-circle"></i> No articles found.
                                </div>
                            @endforelse
                            
                            <div class="mt-3">
                                {{ $articles->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Support Ticket Modal -->
<div class="modal fade" id="supportTicketModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Support Ticket</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="ticketForm" method="POST" action="{{ route('admission.support.ticket.submit') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Subject *</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority *</label>
                                <select name="priority" class="form-control" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category *</label>
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
                        <label>Message *</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="feather-info"></i> Our support team will respond within 24 hours.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#ticketForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#supportTicketModal').modal('hide');
                $('#ticketForm')[0].reset();
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                $.each(errors, function(key, value) {
                    toastr.error(value[0]);
                });
            } else {
                toastr.error('Failed to submit ticket');
            }
        }
    });
});
</script>
@endsection