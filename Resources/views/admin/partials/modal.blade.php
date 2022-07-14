<div class="modal modal-danger fade" id="modalRemove">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Delete Confirmation</h4>
            </div>
            <div class="modal-body">                    
                <h5></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                <form action="" method="POST" id="form-confirm-delete">
                    @method('delete')
                    @csrf
                    <button type="submit" class="btn btn-outline" id="confirm-delete">Delete</button>
                </form>
            </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>