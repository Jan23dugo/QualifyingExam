
<!-- sidebar-topbar.php -->
<nav class="navbar align-items-start sidebar sidebar-dark accordion bg-gradient-primary p-0 navbar-dark" style="color: #005684; background: #005684;">
    <div class="container-fluid d-flex flex-column p-0">
        <a class="navbar-brand d-flex justify-content-center align-items-center sidebar-brand m-0" href="#" style="text-align: center;">
            <div class="sidebar-brand-icon rotate-n-15"></div>
            <img src="assets/img/Logo.png" style="width: 47px; opacity: 1;">
            <div class="sidebar-brand-text mx-3"></div>
        </a>
        <hr class="sidebar-divider my-0">
        <ul class="navbar-nav text-light" id="accordionSidebar">
            <li class="nav-item">
                <a class="nav-link" href="admin-dashboard.php" style="font-family: 'Open Sans', sans-serif;">
                    <i class="far fa-square" style="font-size: 21px; width: 20px; height: 20px;"></i>
                    <span style="font-family: 'Open Sans', sans-serif;">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="create-exam.html">
                    <i class="far fa-edit" style="font-size: 23px; width: 20px; height: 20px;"></i>
                    <span style="font-family: 'Open Sans', sans-serif;">Create Exam</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="students_folder.html">
                    <i class="far fa-user" style="font-size: 20px;"></i>
                    <span style="font-family: 'Open Sans', sans-serif;">Students</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="analytics.html">
                    <i class="fas fa-chart-bar" style="font-size: 21px;"></i>
                    <span style="font-family: 'Open Sans', sans-serif;">Analytics</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="calendar.html">
                    <i class="far fa-calendar-alt" style="font-size: 23px;"></i>
                    <span style="font-family: 'Open Sans', sans-serif;">Calendar</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="login.php">
                    <i class="fas fa-sign-out-alt" style="font-size: 20px;"></i>
                    <span style="font-family: 'Open Sans', sans-serif;">Sign Out</span>
                </a>
            </li>
        </ul>
        <div class="text-center d-none d-md-inline">
            <button class="btn rounded-circle border-0" id="sidebarToggle" type="button"></button>
        </div>
    </div>
</nav>

<nav class="navbar navbar-expand bg-white shadow mb-4 topbar">
    <div class="container-fluid">
        <button class="btn btn-link d-md-none rounded-circle me-3" id="sidebarToggleTop" type="button">
            <i class="fas fa-bars"></i>
        </button>
        <form class="d-none d-sm-inline-block me-auto ms-md-3 my-2 my-md-0 mw-100 navbar-search">
            <div class="input-group">
                <input class="bg-light form-control border-0 small" type="text" placeholder="Search for ...">
                <button class="btn btn-primary py-0" type="button" style="background: rgb(255,255,255);">
                    <i class="fas fa-search" style="font-size: 19px;color: var(--bs-secondary-color);"></i>
                </button>
            </div>
        </form>
        <ul class="navbar-nav flex-nowrap ms-auto">
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="dropdown-toggle nav-link" aria-expanded="false" data-bs-toggle="dropdown" href="#" style="width: 60px;height: 60px;">
                    <i class="far fa-user-circle" style="font-size: 30px;color: var(--bs-navbar-disabled-color);backdrop-filter: brightness(99%);-webkit-backdrop-filter: brightness(99%);"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>

