  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      @php /*
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{ asset('cms/dist/assets/images/user2-160x160.jpg') }}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>Alexander Pierce</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      */ @endphp
      
      @php
      /*
      <!-- search form -->
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
          <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>
      <!-- /.search form -->
      */
      @endphp

      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN NAVIGATION</li>
        
        @php
          function setActive($path)
          {
              return Request::is($path. '*' ) ? ' active' :  '';
          }          
        @endphp

        @foreach($menus as $index=>$menu)
          
          @if(isset($menu['subs']))  
              
              <li class="treeview {{ setActive($menu['path']) }}">
                <a href="#">
                  <i class="fa {{ $menu['fa'] }}"></i> <span>{{ $menu['name'] }}</span>
                  <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                </a>  
                <ul class="treeview-menu">
                  @foreach($menu['subs'] as $submenu)
                  <li class="{{ setActive($submenu['path']) }}">
                    <a href="{{ route($submenu['permission']) }}"><i class="fa fa-circle-o"></i> {{ $submenu['name'] }}</a>
                  </li>
                  @endforeach
                </ul>
              </li>
          @else
            <li class="{{ setActive($menu['path']) }}">
              <a href="{{ route($menu['permission']) }}"><i class="fa {{ $menu['fa'] }}"></i> <span>{{ $menu['name'] }}</span></a>
            </li>
          @endif

        @endforeach          

      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>