<div class="oc-item">
    <div class="product">
        <div class="product-image">
            <a href="#"><img src="{{ asset('storage/' . $image) }}" alt="Checked Short Dress"></a>
            <a href="#"><img src="{{ asset('storage/' . $image) }}" alt="Checked Short Dress"></a>
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
            <div class="product-title" style="height: 60px;"><h3 style="font-size:12px;" class="prod-name"><a href="{{ route('product.front.show', $slug) }}">{{$name}}</a></h3></div>
            
            <div class="quantity quantity-large me-0 w-100 justify-content-center flex-nowrap mt-3">
                <input type="button" value="-" class="minus border-top border-bottom">
                <input type="text" name="quantity[]" class="qty fs-12px wd-40-f border-0 border-top border-bottom" value="1" max="{{$inventory}}" id="quantity{{$id}}" />
                <input type="button" value="+" class="plus border-top border-bottom">
            </div>
        </div>
    </div>
</div>