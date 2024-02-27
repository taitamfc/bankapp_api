{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Users" icon="la la-question" :link="backpack_url('user')" />
<x-backpack::menu-item title="Banks" icon="la la-question" :link="backpack_url('bank')" />
<x-backpack::menu-item title="Transactions" icon="la la-question" :link="backpack_url('transaction')" />
<x-backpack::menu-item title="Blogs" icon="la la-question" :link="backpack_url('blog')" />
<x-backpack::menu-item title="Verify codes" icon="la la-question" :link="backpack_url('verify-code')" />
<x-backpack::menu-item title="Owner banks" icon="la la-question" :link="backpack_url('owner-bank')" />