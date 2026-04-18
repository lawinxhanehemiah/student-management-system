@extends('layouts.admission')

@section('title', $article->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <a href="{{ route('admission.support.help-center') }}" class="btn btn-secondary btn-sm">
                        <i class="feather-arrow-left"></i> Back to Help Center
                    </a>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge badge-info">{{ ucfirst($article->category) }}</span>
                            <span class="badge badge-secondary">
                                <i class="feather-eye"></i> {{ $article->views }} views
                            </span>
                        </div>
                        <small class="text-muted">
                            <i class="feather-calendar"></i> {{ \Carbon\Carbon::parse($article->created_at)->format('d F Y') }}
                        </small>
                    </div>
                    
                    <h2 class="mb-4">{{ $article->title }}</h2>
                    
                    <div class="article-content">
                        {!! nl2br(e($article->content)) !!}
                    </div>
                    
                    @if($article->tags)
                        <div class="mt-4">
                            <strong>Tags:</strong>
                            @foreach(explode(',', $article->tags) as $tag)
                                <span class="badge badge-light">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    @endif
                    
                    <hr>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <i class="feather-help-circle"></i> Was this article helpful?
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-success" onclick="markHelpful({{ $article->id }}, 1)">
                                        <i class="feather-thumbs-up"></i> Yes
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="markHelpful({{ $article->id }}, 0)">
                                        <i class="feather-thumbs-down"></i> No
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <i class="feather-message-square"></i> Still need help?
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#supportTicketModal">
                                        <i class="feather-help-circle"></i> Submit Support Ticket
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Articles -->
            @if($relatedArticles->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Related Articles</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($relatedArticles as $related)
                            <a href="{{ route('admission.support.article', $related->id) }}" class="list-group-item list-group-item-action">
                                <i class="feather-file-text"></i> {{ $related->title }}
                                <small class="text-muted float-right">{{ \Carbon\Carbon::parse($related->created_at)->diffForHumans() }}</small>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
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
            <form method="POST" action="{{ route('admission.support.ticket.submit') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Subject *</label>
                        <input type="text" name="subject" class="form-control" value="Regarding: {{ $article->title }}" required>
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
function markHelpful(articleId, helpful) {
    $.ajax({
        url: '{{ url("admission/support/article") }}/' + articleId + '/feedback',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            helpful: helpful
        },
        success: function(response) {
            toastr.success('Thank you for your feedback!');
        },
        error: function() {
            toastr.error('Failed to submit feedback');
        }
    });
}
</script>
@endsection