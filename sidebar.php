<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>


<?php
//hi try deploy
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Must be first
}

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); // redirect to login
    exit;
}

// Get user info from session safely
$roles = $_SESSION['roles'] ?? 'Employee';   
       // role for sidebar
       
$loggedInUserId = $_SESSION['employee_id'] ?? null; // employee UUID
$loggedInUserName = $_SESSION['user_name'] ?? 'Guest'; // display name

// Optional: redirect if employee_id is missing
if (!$loggedInUserId) {
    header("Location: ../index.php"); // ensure approver session exists
    exit;
}
?>

<!-- HTML content here -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sidebar</title>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="icon" type="image/png" href="picture/logo2.png" />
  
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <style>
    @media (max-width: 768px) {
      #sidebar {
        position: fixed;
        left: -100%;
        z-index: 50;
        transition: left 0.3s ease;
      }
      #sidebar.sidebar-open {
        left: 0;
      }
      #mobile-menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 40;
      }
      #sidebar.sidebar-open ~ #mobile-menu-overlay {
        display: block;
      }
    }
  </style>
</head>


<body class="flex">
  <!-- Mobile menu button -->
  <div class="md:hidden fixed top-4 left-4 z-30">
    <button id="mobile-menu-button" class="text-gray-800 focus:outline-none">
      <i data-lucide="menu" class="w-6 h-6"></i>
    </button>
  </div>
  
<!-- Sidebar -->
<div id="sidebar" class="relative bg-gray-800 text-white w-64 transition-all duration-300 h-screen flex flex-col overflow-visible">
  <!-- Toggle button (desktop only) -->
  <button id="sidebar-toggle" 
          class="absolute top-20 -right-3 hidden md:flex items-center justify-center w-6 h-6 rounded-full bg-gray-700 text-white shadow-lg hover:bg-gray-600 transition-all">
    <!-- Left chevron (expanded) -->
    <span class="chevron-left flex">
      <i data-lucide="chevron-left" class="w-6 h-6"></i>
    </span>
    <!-- Right chevron (collapsed) -->
    <span class="chevron-right hidden">
      <i data-lucide="chevron-right" class="w-6 h-6"></i>
    </span>
  </button>

  <!-- Logo + header -->
  <div class="flex items-center justify-between px-4 py-4 border-b border-gray-700">
    <a href="/public_html/timesheet/dashboard.php">
      <img src="../picture/logo.png" alt="Logo" class="h-20 sidebar-logo-expanded" />
    </a>
    <a href="/public_html/timesheet/dashboard.php">
      <img src="../picture/logo2.png" alt="Logo" class="h-20 sidebar-logo-collapsed hidden" />
    </a>
  </div>


<script>
document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.getElementById("sidebar-toggle");
  const sidebar = document.getElementById("sidebar");
  const logoExpanded = document.querySelector(".sidebar-logo-expanded");
  const logoCollapsed = document.querySelector(".sidebar-logo-collapsed");
  const sidebarText = document.querySelectorAll(".sidebar-text");
  const chevronLeft = document.querySelector(".chevron-left");
  const chevronRight = document.querySelector(".chevron-right");
  const mobileMenuButton = document.getElementById("mobile-menu-button");
  const mobileMenuOverlay = document.getElementById("mobile-menu-overlay");

  let isOpen = true; // Desktop sidebar state

  // Desktop sidebar toggle
  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      isOpen = !isOpen;

      sidebar.classList.toggle("w-64", isOpen);
      sidebar.classList.toggle("w-20", !isOpen);
      sidebar.classList.toggle("overflow-hidden", !isOpen);

      if (logoExpanded && logoCollapsed) {
        logoExpanded.classList.toggle("hidden", !isOpen);
        logoCollapsed.classList.toggle("hidden", isOpen);
      }

      sidebarText.forEach(el => {
        el.classList.toggle("hidden", !isOpen);
      });

      // Chevrons only on desktop
      if (window.innerWidth >= 768 && chevronLeft && chevronRight) {
        chevronLeft.classList.toggle("hidden", !isOpen);
        chevronRight.classList.toggle("hidden", isOpen);
      }

      const icon = toggleBtn.querySelector("i");
      if (icon) {
        icon.classList.toggle("rotate-180");
      }
    });
  }

  // Mobile menu toggle (hamburger button)
  if (mobileMenuButton) {
    mobileMenuButton.addEventListener("click", () => {
      sidebar.classList.toggle("sidebar-open");
    });
  }

  if (mobileMenuOverlay) {
    mobileMenuOverlay.addEventListener("click", () => {
      sidebar.classList.remove("sidebar-open");
    });
  }

  // Initialize Lucide icons
  if (typeof lucide !== "undefined" && lucide.createIcons) {
    lucide.createIcons();
  }
});
</script>


    
    <?php include 'chatbot.php'; ?>
<?php include '../loader.php'; ?>

   <!-- Navigation -->
<?php
$currentPage = $_SERVER['PHP_SELF']; // e.g. /public_html/dashboard.php
?>
<!-- Navigation -->
<nav class="flex-1 px-2 py-4 space-y-2">
<!-- Search input for sidebar with icon -->
<div class="px-3 py-2 relative">
  <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"></i>
  <input 
    type="text" 
    id="sidebarSearch" 
    placeholder="Search..." 
    class="w-full pl-10 pr-3 py-2 rounded bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
  >
</div>


  <!-- Only show Employee page link for Admin or Manager -->
  <?php if ($roles !== 'Employee'): ?>
    <a href="/public_html/employee/employee.php"
       class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
       <?php echo ($currentPage == '/public_html/employee/employee.php') 
                ? 'bg-gray-700 text-white font-semibold' 
                : 'hover:bg-gray-700'; ?>">
      <i data-lucide="users" class="w-5 h-5"></i>
      <span class="sidebar-text">Employee</span>
    </a>
  <?php endif; ?>

  <a href="/public_html/timesheet/dashboard.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/dashboard.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="home" class="w-5 h-5"></i>
    <span class="sidebar-text">Dashboard</span>
  </a>

  <a href="/public_html/timeAndattendance/time.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/timeAndattendance/time.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="clock" class="w-5 h-5"></i>
    <span class="sidebar-text">Time and Attendance</span>
  </a>

  <a href="/public_html/shift/assignShift.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/shift/assignShift.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="calendar-range" class="w-5 h-5"></i>
    <span class="sidebar-text">Shift & Schedule</span>
  </a>

  <a href="/public_html/timesheet/timesheet.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/timesheet/timesheet.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="file-text" class="w-5 h-5"></i>
    <span class="sidebar-text">Timesheet</span>
  </a>

  <a href="/public_html/leave/assignLeave.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/leave/leave.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="plane" class="w-5 h-5"></i>
    <span class="sidebar-text">Leave Management</span>
  </a>

  <a href="/public_html/claims/claims.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/claims/claims.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
    <span class="sidebar-text">Claims & Reimbursement</span>
  </a>

  <!-- Employee Self-Service (HR2) -->
  <a href="/public_html/hr2/employee-self-service.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/hr2/employee-self-service.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="user-check" class="w-5 h-5"></i>
    <span class="sidebar-text">Employee Self-Service (HR2)</span>
  </a>

  <!-- Smart Warehousing System (LOG1) -->
  <a href="/public_html/log1/smart-warehousing.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/log1/smart-warehousing.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="warehouse" class="w-5 h-5"></i>
    <span class="sidebar-text">Smart Warehousing System (LOG1)</span>
  </a>

  <!-- Asset Lifecycle & Maintenance (LOG1) -->
  <a href="/public_html/log1/asset-lifecycle.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/log1/asset-lifecycle.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="settings" class="w-5 h-5"></i>
    <span class="sidebar-text">Asset Lifecycle & Maintenance (LOG1)</span>
  </a>

  <!-- Document Tracking & Logistics Records (LOG1) -->
  <a href="/public_html/log1/document-tracking.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/log1/document-tracking.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="file-search" class="w-5 h-5"></i>
    <span class="sidebar-text">Document Tracking & Logistics Records (LOG1)</span>
  </a>

  <!-- Front desk & Reception Module (Core 1) -->
  <a href="/public_html/core1/front-desk.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/core1/front-desk.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="monitor" class="w-5 h-5"></i>
    <span class="sidebar-text">Front desk & Reception Module (Core 1)</span>
  </a>

  <!-- Reservation & Booking Module (Core 1) -->
  <a href="/public_html/core1/reservation-booking.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/core1/reservation-booking.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="calendar-check" class="w-5 h-5"></i>
    <span class="sidebar-text">Reservation & Booking Module (Core 1)</span>
  </a>

  <!-- Guest Relationship Management (Core 1) -->
  <a href="/public_html/core1/guest-relationship.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/core1/guest-relationship.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="users-2" class="w-5 h-5"></i>
    <span class="sidebar-text">Guest Relationship Management (Core 1)</span>
  </a>

<?php if ($roles !== 'Employee'): ?>
 
  <a href="/public_html/user/userProfile.php"
     class="flex items-center gap-3 px-3 py-2 rounded sidebar-link 
     <?php echo ($currentPage == '/public_html/user/createUser.php') 
              ? 'bg-gray-700 text-white font-semibold' 
              : 'hover:bg-gray-700'; ?>">
    <i data-lucide="user-plus" class="w-5 h-5"></i>
    <span class="sidebar-text">User Management</span>
  </a>
   <?php endif; ?>

  <!-- Logout Section -->
  <div class="mt-auto px-2 py-4 border-t border-gray-700">
    <div class="flex items-center gap-3 px-3 py-2 text-gray-300">
      <i data-lucide="user" class="w-5 h-5"></i>
      <span class="sidebar-text"><?php echo htmlspecialchars($loggedInUserName); ?></span>
    </div>
    <a href="/public_html/logout.php"
       class="flex items-center gap-3 px-3 py-2 rounded sidebar-link hover:bg-red-600 text-red-300 hover:text-white transition-colors">
      <i data-lucide="log-out" class="w-5 h-5"></i>
      <span class="sidebar-text">Logout</span>
    </a>
  </div>
</nav>

<!-- Sidebar search script -->
<script>
  const searchInput = document.getElementById('sidebarSearch');
  searchInput.addEventListener('keyup', function() {
    const filter = searchInput.value.toLowerCase();
    const links = document.querySelectorAll('.sidebar-link');
    links.forEach(link => {
      const text = link.querySelector('.sidebar-text').textContent.toLowerCase();
      link.style.display = text.includes(filter) ? '' : 'none';
    });
  });
</script>

  </div>

  <!-- Mobile menu overlay -->
  <div id="mobile-menu-overlay"></div>

</body>
</html>

