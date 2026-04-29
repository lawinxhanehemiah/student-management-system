{{-- resources/views/superadmin/results/modals/view-result.blade.php --}}

<div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Result Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="viewResultContent">
                <div class="text-center">Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printResult()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function printResult() {
    let content = $('#viewResultContent').html();
    let printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head><title>Result Details</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
            </head>
            <body>
                <div class="container mt-3">${content}</div>
                <script>window.print();<\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
}
</script>