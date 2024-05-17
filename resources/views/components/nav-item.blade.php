<div class="nav-item {{ $isActive ? 'active' : '' }}">
    <a class="nav-link {{ $isActive ? 'active' : '' }}"
       href="{{$href}}">
        <span class="sidenav-normal">
            {{ $slot }}
        </span>
    </a>
</div>
