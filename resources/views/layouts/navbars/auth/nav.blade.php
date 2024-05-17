<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg position-sticky mt-4 top-1 px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
    id="navbarBlur" data-scroll="true">
    
    <div class="container-fluid py-1 px-3">
        <li style="padding-right:10px;" class="nav-item d-xl-none ps-0 d-flex align-items-center">
            <a href="javascript:;" class="toggle nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                </div>
            </a>
        </li>
        <nav aria-label="breadcrumb">
            <h6 class="font-weight-bolder mb-0 text-capitalize">{{ str_replace('-', ' ', Request::path()) }}</h6>
        </nav>
        
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4 justify-content-end" id="navbar">

            <ul class="navbar-nav  justify-content-end">
                <li class="nav-item d-flex align-items-center">
                    <a href="{{ url('/logout') }}" class="nav-link text-body font-weight-bold px-0">
                        <i class="fa fa-user me-sm-1"></i>
                        <span class="d-sm-inline d-none">Sign Out</span>
                    </a>
                </li>
                
                <!-- COnfig layout
        <li class="nav-item px-3 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0">
            <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
            </a>
        </li> -->
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->

<style>
     @media only screen and (min-width: 768px) and (max-width: 1200px) {
        #navbarBlur {
            transition: left 0.5s ease;
        }
        .minimizeNav {
            
            width: calc(100% - 300px);
            left: 300px;
        }
    }
</style>


<script>
const toggle = document.querySelector('.toggle');
const navbarBlur = document.getElementById('navbarBlur');

toggle.addEventListener('click', function() {
  if (navbarBlur.classList.contains('minimizeNav')) {
    navbarBlur.classList.remove('minimizeNav');
  } else {
    navbarBlur.classList.add('minimizeNav');
  }
});
</script>
