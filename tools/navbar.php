<?php
// navbar.php
function renderNavbar($pageRoute = 'Dashboard', $notificationsPageURL = '/public/admin.php')
{
    global $config;
    ?>
    <nav class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 sticky top-0 z-30">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <!-- Left Section -->
            <div class="flex items-center space-x-4">
                <!-- زر السايدبار للموبايل -->
                <button id="sidebarToggle" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center">
                        <!-- <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                        </svg> -->
                        <img src="../assets/images/logo.png" alt="">
                    </div>
                    <span class="text-sm font-medium text-gray-700">KCML</span>
                </div>
                <div class="text-sm text-gray-500">
                    <span><?php echo $pageRoute ?></span>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center space-x-4">

                <button id="notifications"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-medium rounded-lg hover:shadow-lg transition-all duration-200">
                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 16 21">
                        <path class="text-white" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 3.464V1.1m0 2.365a5.338 5.338 0 0 1 5.133 5.368v1.8c0 2.386 1.867 2.982 1.867 4.175C15 15.4 15 16 14.462 16H1.538C1 16 1 15.4 1 14.807c0-1.193 1.867-1.789 1.867-4.175v-1.8A5.338 5.338 0 0 1 8 3.464ZM4.54 16a3.48 3.48 0 0 0 6.92 0H4.54Z" />
                    </svg>
                </button>
                <button id="logoutButton"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-medium rounded-lg hover:shadow-lg transition-all duration-200">
                    Logout
                </button>
            </div>
        </div>
    </nav>

    <script>
        function deleteCookie(name) {
            document.cookie = name + '=; Max-Age=0; path=/; SameSite=Strict; Secure';
        }

        function logout() {
            window.location.href = '<?= BASE_URL ?>/public/logout.php';
        }

        document.getElementById('logoutButton').addEventListener('click', logout);
        document.getElementById('notifications').addEventListener('click', () => {
            window.location.href = '<?= BASE_URL . $notificationsPageURL ?>';
        });
    </script>
    <?php
}
?>