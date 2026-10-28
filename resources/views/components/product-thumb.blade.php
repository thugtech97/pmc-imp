<div class="oc-item shadow" style="border-radius: 10px;">
    <div class="product">
        <div class="product-image p-2">
            <a href="#"><img src="{{ asset('storage/' . $image) }}" onerror="this.src='{{ asset('images/1667370521_download.jpg') }}'"></a>
            <div class="sale-flash badge bg-success p-2">{{$code}}</div>
            <div class="bg-overlay">
                <div class="bg-overlay-content align-items-end justify-content-between" data-hover-animate="fadeIn" data-hover-speed="400">
                    <a href="javascript:;" onclick="add_to_cart('{{$id}}');" class="btn btn-dark me-2"><i class="icon-shopping-cart"></i></a>
                    <a href="{{ route('product.front.show', $slug) }}" class="btn btn-dark"><i class="icon-line-expand"></i></a>
                </div>
                <div class="bg-overlay-bg bg-transparent"></div>
            </div>
        </div>
        <div class="product-desc center">
            <div class="product-title" style="height: 60px;"><h3 style="font-size:11px;" class="prod-name px-2"><a href="{{ route('product.front.show', $slug) }}">{{$name}}</a></h3></div>
            <!-- Red/Green Styled Quantity Selector -->
            <div class="quantity quantity-large me-0 w-100 justify-content-center flex-nowrap mt-4 d-flex" style="margin: 0 auto;">
                <div class="d-inline-flex rounded-3 overflow-hidden border" style="height: 38px;">
                    <input type="button" value="-" class="minus btn rounded-0 border-0 fs-5 fw-bold text-white p-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #d9534f; line-height: 1;">
                    <input type="text" name="quantity[]" class="qty fs-14px wd-40-f border-0 text-center fw-bold bg-white text-dark p-0" value="1" max="{{$inventory}}" id="quantity{{$id}}" style="width: 45px; height: 38px; outline: none; box-shadow: none; line-height: 38px;" />
                    <input type="button" value="+" class="plus btn rounded-0 border-0 fs-5 fw-bold text-white p-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #5cb85c; line-height: 1;">
                </div>
            </div>
        </div>
    </div>
</div>