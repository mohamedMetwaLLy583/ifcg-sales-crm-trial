<!DOCTYPE html>
<html lang="ar" dir="rtl">
    
@include('dashboard.layouts.head')

<body data-pc-preset="preset-1" data-pc-direction="rtl" data-pc-theme="light" dir="rtl">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    
    @include('dashboard.layouts.sidebar')
    @include('dashboard.layouts.header')
    

    
    <!-- هنا نضيف الـ Toast Component -->
    
    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <!-- المحتوى الرئيسي -->
            @yield('content')
        </div>
    </div>
    <!-- [ Main Content ] end -->
    
    @include('dashboard.layouts.footer')
    
    @include('dashboard.layouts.scripts')
</body>
<!-- [Body] end -->

</html>