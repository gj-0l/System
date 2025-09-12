<?php
require_once __DIR__ . '/../../tools/navbar.php';
require_once __DIR__ . '/../../tools/sidebar.php';

?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title></title>
    </title>
    <title> dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&display=swap" rel="stylesheet">
    <!-- Firebase SDKs -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // إعداد Firebase (استبدل القيم بقيمك)
        const firebaseConfig = {
            apiKey: "AIzaSyBwxIvQua1PMFur2bonw3ZSkRd2IL36e_A",
            authDomain: "mobile-equipment-3ac58.firebaseapp.com",
            projectId: "mobile-equipment-3ac58",
            storageBucket: "mobile-equipment-3ac58.firebasestorage.app",
            messagingSenderId: "736129810254",
            appId: "1:736129810254:web:1f70eaa87ec803279fa81f",
            measurementId: "G-DYC99K0M32", // يمكن وضعها هنا أو في دالة getToken مباشرة
        };
        firebase.initializeApp(firebaseConfig);

        const messaging = firebase.messaging();

        // اطلب إذن الإشعارات وجلب التوكن
        messaging.requestPermission()
            .then(() => messaging.getToken({ vapidKey: 'BLvVVJkkOyQNHDeca15iLwY7RLOqIf5xWooimnt_xWjqyGN7b6Q2I59qsX5WizmlrNRyuo57QqmCOpqaiJ90Da0' }))
            .then((currentToken) => {
                if (currentToken) {
                    // أرسل التوكن للسيرفر
                    fetch('../../routes/notifications.php?action=save_token', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ token: currentToken })
                    }).then(res => {
                        if (res.ok) {
                            console.log("تم حفظ التوكن بنجاح.");
                        }
                    }).catch(err => console.error("خطأ في حفظ التوكن:", err));
                } else {
                    console.log('لا يوجد توكن للإشعارات.');
                }
            })
            .catch((err) => {
                console.error('فشل في الحصول على إذن الإشعارات أو التوكن:', err);
            });
    </script>

    <style>
        .soft-shadow {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, .05);
        }

        .gradient-purple {
            background: linear-gradient(310deg, #7928ca 0%, #ff0080 100%);
        }

        .gradient-blue {
            background: linear-gradient(310deg, #2152ff 0%, #21d4fd 100%);
        }

        .gradient-green {
            background: linear-gradient(310deg, #17ad37 0%, #98ec2d 100%);
        }

        .gradient-orange {
            background: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
        }

        .gradient-dark {
            background: linear-gradient(310deg, #141727 0%, #3a416f 100%);
        }

        .rocket-card {
            background: linear-gradient(310deg, #ff0080 0%, #7928ca 100%);
        }

        .wealth-card {
            background: linear-gradient(310deg, #141727 0%, #3a416f 100%);
        }
    </style>
</head>

<body>
    <?php renderNavbar('Dashboard', '/public/admin.php'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('dashboard'); ?>

        <main class="p-6 ml-4 md:pl-64">
            <!-- Dashboard Page -->
            <div id="dashboardPage" class="page-content">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-2xl p-6 soft-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Today's Money</p>
                                <h3 class="text-2xl font-bold text-gray-900">$53,000</h3>
                                <p class="text-sm text-green-500 font-medium">+55%</p>
                            </div>
                            <div class="w-12 h-12 gradient-purple rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 soft-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Today's Users</p>
                                <h3 class="text-2xl font-bold text-gray-900">2,300</h3>
                                <p class="text-sm text-green-500 font-medium">+3%</p>
                            </div>
                            <div class="w-12 h-12 gradient-blue rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 soft-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">New Clients</p>
                                <h3 class="text-2xl font-bold text-gray-900">+3,462</h3>
                                <p class="text-sm text-red-500 font-medium">-2%</p>
                            </div>
                            <div class="w-12 h-12 gradient-green rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 soft-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Sales</p>
                                <h3 class="text-2xl font-bold text-gray-900">$103,430</h3>
                                <p class="text-sm text-green-500 font-medium">+5%</p>
                            </div>
                            <div class="w-12 h-12 gradient-orange rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-2xl p-8 soft-shadow">
                        <h3 class="text-sm font-medium text-gray-600 mb-2">Built by developers</h3>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Soft UI Dashboard</h2>
                        <p class="text-gray-600 mb-6">From colors, cards, typography to complex elements, you
                            will find
                            the full documentation.</p>
                        <button class="text-purple-600 font-medium text-sm hover:underline">Read More</button>
                    </div>

                    <div class="rocket-card rounded-2xl p-8 text-white relative overflow-hidden">
                        <div class="relative z-10">
                            <h2 class="text-2xl font-bold mb-4">Work with the rockets</h2>
                            <p class="text-white/90 mb-6">Wealth creation is an evolutionarily recent
                                positive-sum game.
                                It is all about who take the opportunity first.</p>
                            <button
                                class="bg-white/20 hover:bg-white/30 text-white font-medium text-sm py-2 px-4 rounded-lg transition-colors">Read
                                More</button>
                        </div>
                        <div class="absolute right-8 top-8 w-24 h-24">
                            <svg viewBox="0 0 100 100" class="w-full h-full text-white/20">
                                <path fill="currentColor"
                                    d="M50 10 L50 10 C50 10 45 25 45 40 L45 70 C45 85 50 90 50 90 C50 90 55 85 55 70 L55 40 C55 25 50 10 50 10 Z M35 50 L35 50 C35 50 25 45 25 45 L15 45 C5 45 0 50 0 50 C0 50 5 55 15 55 L25 55 C25 55 35 50 35 50 Z M65 50 L65 50 C65 50 75 45 75 45 L85 45 C95 45 100 50 100 50 C100 50 95 55 85 55 L75 55 C75 55 65 50 65 50 Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl p-8 soft-shadow">
                        <div class="h-64 bg-gray-800 rounded-lg flex items-center justify-center">
                            <canvas id="barChart" class="max-w-full max-h-full"></canvas>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-8 soft-shadow">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Sales overview</h3>
                        <p class="text-sm text-gray-600 mb-6">4% more in 2021</p>
                        <div class="h-48">
                            <canvas id="lineChart" class="max-w-full max-h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Page -->
            <div id="tablesPage" class="page-content hidden">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Tables</h1>
                    <p class="text-gray-600 mt-2">Here you can manage your data tables</p>
                </div>

                <div class="bg-white rounded-2xl soft-shadow overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Authors table</h2>
                        <p class="text-gray-600 mt-1">List of all registered users</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Author</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Function</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employed</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-purple-500 flex items-center justify-center text-white font-semibold">
                                                    JD</div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">John Doe</div>
                                                <div class="text-sm text-gray-500">john@creative-tim.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Manager</div>
                                        <div class="text-sm text-gray-500">Organization</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Online</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">23/04/18</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="#" class="text-purple-600 hover:text-purple-900">Edit</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                                    AS</div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Alexa Smith</div>
                                                <div class="text-sm text-gray-500">alexa@creative-tim.com</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Developer</div>
                                        <div class="text-sm text-gray-500">Programming</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Offline</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">11/01/19</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="#" class="text-purple-600 hover:text-purple-900">Edit</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold">
                                                    LB</div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Laurent Perrier
                                                </div>
                                                <div class="text-sm text-gray-500">laurent@creative-tim.com
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Executive</div>
                                        <div class="text-sm text-gray-500">Projects</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Online</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">19/09/17</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="#" class="text-purple-600 hover:text-purple-900">Edit</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>