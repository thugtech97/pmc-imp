<div class="modal effect-scale" id="prompt-remove-logo" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('standard.settings.website.remove_logo_confirmation_title')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{__('standard.settings.website.remove_logo_confirmation')}}</p>
            </div>
            <form action="{{route('website-settings.remove-logo')}}" method="POST">
            @csrf
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-danger">Yes, remove logo</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="prompt-remove-icon" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('standard.settings.website.remove_icon_confirmation_title')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{__('standard.settings.website.remove_icon_confirmation')}}</p>
            </div>
            <form action="{{route('website-settings.remove-icon')}}" method="POST">
            @csrf
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-danger">Yes, remove icon</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="prompt-delete-social" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">{{__('standard.settings.website.remove_social_account_confirmation_title')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{__('standard.settings.website.remove_social_account_confirmation')}}</p>
            </div>
            <form action="{{route('website-settings.remove-media')}}" method="POST">
            @csrf
            <input type="hidden" id="mid" name="id">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-danger">Yes, remove account</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal effect-scale" id="prompt-add-bank" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Add Payment Option</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('setting.ecommerce-add-payment-option') }}" method="post">
                @method('POST')
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="d-block">Name *</label>
                        <input required type="text" name="name" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="d-block">Account Name *</label>
                        <input required type="text" name="account_name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="d-block">Account Number *</label>
                        <input required type="text" name="account_no" class="form-control"> 
                    </div>
                    
                    <div class="form-group">
                        <label class="d-block">Branch </label>
                        <input type="text" name="branch" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="prompt-edit-bank" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Update Payment Option</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('setting.ecommerce-update-payment-option') }}" method="post">
                @method('POST')
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="d-block">Name *</label>
                        <input type="hidden" name="id" id="bank_id">
                        <input required type="text" name="name" id="bankname" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label class="d-block">Account Name *</label>
                        <input required type="text" name="account_name" id="bankaccountname" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="d-block">Account Number *</label>
                        <input required type="text" name="account_no" id="bankaccountno" class="form-control"> 
                    </div>
                    
                    <div class="form-group">
                        <label class="d-block">Branch</label>
                        <input type="text" name="branch" id="bankbranch" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="prompt-delete-bank" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">Remove Payment Option</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item?</p>
            </div>
            <form action="{{route('setting.ecommerce-delete-payment-option')}}" method="POST">
            @csrf
                <input type="hidden" name="id" id="dbank_id">
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-danger">Yes, remove payment option</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>