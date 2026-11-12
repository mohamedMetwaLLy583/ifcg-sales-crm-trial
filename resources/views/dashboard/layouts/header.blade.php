<nav class="navbar navbar-expand-lg pc-header">
    <div class="container-fluid">
        <button class="btn btn-light d-lg-none me-2" type="button" id="mobile-collapse" aria-label="فتح القائمة">
            <i class="ti ti-menu-2"></i>
        </button>

        <div class="ms-auto d-flex align-items-center gap-2">
            <div class="text-end d-none d-sm-block">
                <div class="fw-semibold">{{ Auth::user()->name }}</div>
                <small class="text-muted">لوحة الإدارة</small>
            </div>

            <div class="dropdown">
                <a class="btn btn-light dropdown-toggle" href="#" id="navbarDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ti ti-user-circle"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile') }}">الملف الشخصي</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-2"></i>
                                تسجيل الخروج
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
