<div class="col-lg-3 pe-lg-4">
	<div class="tablet-view">
		<a href="javascript:void(0)" class="closebtn d-block d-lg-none" onclick="closeNav()">&times;</a>
		
		<div class="border-0">
			<h3>My Account</h3>
			<div class="side-menu">
				<ul class="mb-0 pb-0">
					<li>
						<a href="{{ route('customer.manage-account') }}"><div class="d-flex align-items-center ps-3"><i class="icon-line-head fs-5 me-3"></i> Manage Account</div></a>
					</li>
					<li class="h-bg-gray-color ts">
						<a href="{{ route('my-account.change-password') }}"><div class="d-flex align-items-center ps-3"><i class="icon-line-key fs-5 me-3"></i> Change Password</div></a>
					</li>
					<!--<li class="h-bg-gray-color ts">
						<a href="{{ route('profile.sales') }}"><div class="d-flex align-items-center ps-3"><i class="icon-line-shopping-bag fs-5 me-3"></i> MRS Request</div></a>
					</li>-->
					<li class="h-bg-gray-color ts">
						<a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><div class="d-flex align-items-center ps-3"><i class="icon-line-log-out fs-5 me-3"></i> Log Out</div></a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>