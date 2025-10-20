<?php
// sidebar.php
function renderSidebar($activePage = '')
{
    //get user role
    $userRole = $_SESSION['user_type'];

    switch ($userRole) {
        case 'admin':
            $links = [
                'notifications' => [
                    'label' => 'Notifications',
                    'href' => BASE_URL . '/public/admin.php',
                    'icon' => '<path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />'
                ],
                'add_equipment' => [
                    'label' => 'Add Equipment',
                    'href' => BASE_URL . '/public/add_equipment.php',
                    'icon' => '<path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" />'
                ],
                'equipments' => [
                    'label' => 'Equipments List',
                    'href' => BASE_URL . '/public/equipments.php',
                    'icon' => '<path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" />'
                ],
                'users' => [
                    'label' => 'Users List',
                    'href' => BASE_URL . '/public/users.php',
                    'icon' => '<path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" />'
                ],
                'add_check_item' => [
                    'label' => 'Add Check',
                    'href' => BASE_URL . '/public/add_check_item.php',
                    'icon' => '<path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" />'
                ],
                'check_list_items' => [
                    'label' => 'Check List',
                    'href' => BASE_URL . '/public/check_list_items.php',
                    'icon' => '<path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" />'
                ],
                'today_requests' => [
                    'label' => 'Today Requests',
                    'href' => BASE_URL . '/public/events.php',
                    'icon' => '<path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" />'
                ],
                'dashboard' => [
                    'label' => 'Executer Dashboard',
                    'href' => BASE_URL . '/public/executer_dashboard.php',
                    'icon' => '<path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />'
                ],
            ];
            break;
        case 'execution':
            $links = [
                'dashboard' => [
                    'label' => 'Dashboard',
                    'href' => BASE_URL . '/public/executer_dashboard.php',
                    'icon' => '<path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />'
                ],
                'all_asset_types' => [
                    'label' => 'All Mobile equpment',
                    'href' => BASE_URL . '/public/checklist.php',
                    'icon' => '<path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />'
                ],

            ];
            break;
        case 'requester':
            $links = [
                'calendar' => [
                    'label' => 'Requester',
                    'href' => BASE_URL . '/public/requester_calendar.php',
                    'icon' => '<path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />'
                ],
                'today_requests' => [
                    'label' => 'Today Requests',
                    'href' => BASE_URL . '/public/events.php',
                    'icon' => '<path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" />'
                ],
            ];
            break;
        case 'manager':
            $links = [
                'notifications' => [
                    'label' => 'Notifications',
                    'href' => BASE_URL . '/public/manager.php',
                    'icon' => '<path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />'
                ],
            ];
            break;
        default:
            $links = [];
            break;
    }
    ?>

    <!-- Overlay للموبايل -->
    <div id="mobileSidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="bg-white shadow-sm border-r border-gray-200 h-[calc(100vh-64px)] w-64 fixed top-18 left-0 transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-50">
        <div class="p-6">
            <div class="space-y-2">
                <?php foreach ($links as $key => $link):
                    $isActive = $activePage === $key;
                    $activeClasses = $isActive ? "text-[#0b6f76] bg-purple-50 font-medium" : "text-gray-700 hover:bg-gray-100";
                    ?>
                    <a href="<?= $link['href'] ?>"
                        class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors <?= $activeClasses ?>">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <?= $link['icon'] ?>
                        </svg>
                        <span><?= $link['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggle');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
    </script>

    <?php
}
