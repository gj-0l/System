<?php
require_once __DIR__ . '/../../tools/navbar.php';
require_once __DIR__ . '/../../tools/sidebar.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>لوحة الإحصائيات والتقارير</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <?php renderNavbar('Dashboard', '/public/admin.php'); ?>
    <div class="dashboard-container min-h-screen">
        <?php renderSidebar('statistics'); ?>

        <main class="p-4 md:p-8 ml-0 md:ml-64" dir="rtl">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <!-- Header with Title & Date Pickers -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">لوحة الإحصائيات والمراقبة</h1>
                        <p class="text-sm text-gray-500 mt-1">متابعة حالة المعدات والطلبات اليومية وإصدار التقارير الشهرية</p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Date Filter for Daily Stats -->
                        <div class="flex flex-col">
                            <label for="filterDate" class="text-xs font-semibold text-gray-500 mb-1">تاريخ الإحصائيات اليومية</label>
                            <input type="date" id="filterDate" 
                                   class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#0b6f76]" />
                        </div>

                        <!-- Month Filter for Monthly Export -->
                        <div class="flex flex-col">
                            <label for="reportMonth" class="text-xs font-semibold text-gray-500 mb-1">شهر التقرير (Excel / PDF)</label>
                            <div class="flex items-center gap-2">
                                <input type="month" id="reportMonth" 
                                       class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#0b6f76]" />
                                
                                <button onclick="downloadExcel(event)" 
                                        class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-3 py-2 rounded-lg transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>Excel</span>
                                </button>
                                
                                <button onclick="downloadPDF(event)" 
                                        class="flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-3 py-2 rounded-lg transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    <span>PDF</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Daily Requests Count Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 border border-blue-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 text-blue-200/40">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1-1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V4a2 2 0 00-2-2h-1V1a1 1 0 10-2 0v1H7V1a1 1 0 00-1-1zm3 10a1 1 0 100-2 1 1 0 000 2zM6 8a1 1 0 100-2 1 1 0 000 2zm9 4a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-blue-800">إجمالي طلبات اليوم</h3>
                        <div class="mt-4 space-y-1">
                            <!-- <div class="flex justify-between items-baseline">
                                <span class="text-xs text-blue-600">المجدولة للتنفيذ:</span>
                                <span id="statScheduledRequests" class="text-2xl font-black text-blue-900">0</span>
                            </div> -->
                            <div class="flex justify-between items-center text-xs text-blue-500 pt-1 border-t border-blue-200/50">
                                <span>المستلمة (المنشأة) اليوم:</span>
                                <span id="statCreatedRequests" class="font-bold">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Out of System Card -->
                    <div onclick="filterTableByStatus('out_of_system')" 
                         class="bg-gradient-to-br from-red-50 to-red-100/50 border border-red-100 rounded-2xl p-5 shadow-sm cursor-pointer hover:shadow transition-all relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-red-200/40 group-hover:scale-105 transition-transform">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-red-800">معدات خارج الخدمة</h3>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span id="statOutOfSystem" class="text-3xl font-black text-red-900">0</span>
                            <span class="text-xs text-red-600">معدات متعطلة</span>
                        </div>
                        <p class="text-[11px] text-red-500 mt-2 hover:underline">اضغط للتصفية وعرض الأسماء 📋</p>
                    </div>

                    <!-- Reserved Card -->
                    <div onclick="filterTableByStatus('reserved')" 
                         class="bg-gradient-to-br from-amber-50 to-amber-100/50 border border-amber-100 rounded-2xl p-5 shadow-sm cursor-pointer hover:shadow transition-all relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-amber-200/40 group-hover:scale-105 transition-transform">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-amber-800">معدات محجوزة</h3>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span id="statReserved" class="text-3xl font-black text-amber-900">0</span>
                            <span class="text-xs text-amber-600">لديها حجوزات نشطة</span>
                        </div>
                        <p class="text-[11px] text-amber-500 mt-2 hover:underline">اضغط للتصفية وعرض التفاصيل 📋</p>
                    </div>

                    <!-- Available Card -->
                    <div onclick="filterTableByStatus('available')" 
                         class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 border border-emerald-100 rounded-2xl p-5 shadow-sm cursor-pointer hover:shadow transition-all relative overflow-hidden group">
                        <div class="absolute -right-4 -bottom-4 text-emerald-200/40 group-hover:scale-105 transition-transform">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-emerald-800">معدات متاحة بدون حجز</h3>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span id="statAvailable" class="text-3xl font-black text-emerald-900">0</span>
                            <span class="text-xs text-emerald-600">شغالة وبدون حجز</span>
                        </div>
                        <p class="text-[11px] text-emerald-500 mt-2 hover:underline">اضغط للتصفية وعرض التفاصيل 📋</p>
                    </div>
                </div>

                <!-- Equipment Status List/Table Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <span>جدول حالة المعدات وتفاصيلها</span>
                                <span id="tableFilterIndicator" class="hidden text-xs bg-[#0b6f76]/10 text-[#0b6f76] px-2 py-0.5 rounded-full"></span>
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">يعرض هذا الجدول جميع المعدات وتصنيفاتها لليوم المختار</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Reset Filters -->
                            <button id="btnResetFilters" onclick="resetTableFilters()" 
                                    class="hidden text-xs text-gray-500 hover:text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition">
                                عرض الكل
                            </button>

                            <!-- Search Input -->
                            <div class="relative w-64">
                                <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" id="searchEquipment" oninput="handleSearch()" placeholder="ابحث بالاسم أو الكود..." 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-lg pr-9 pl-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#0b6f76]" />
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full border-collapse text-right">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="py-4 px-6">المعدة</th>
                                    <th class="py-4 px-6">الكود</th>
                                    <th class="py-4 px-6">الحالة لليوم</th>
                                    <th class="py-4 px-6">عدد الحجوزات اليومية</th>
                                    <th class="py-4 px-6">تفاصيل إضافية</th>
                                </tr>
                            </thead>
                            <tbody id="equipmentStatsTableBody" class="divide-y divide-gray-100 text-sm">
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        جاري تحميل البيانات...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Monthly Report Hidden Printable Template Container -->
    <div id="printTemplate" class="hidden"></div>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        let dashboardData = null;
        let currentStatusFilter = null;
        let currentSearchQuery = "";

        // Initialize Dates
        document.addEventListener('DOMContentLoaded', () => {
            const todayStr = new Date().toISOString().split('T')[0];
            document.getElementById('filterDate').value = todayStr;
            
            const monthStr = todayStr.substring(0, 7); // YYYY-MM
            document.getElementById('reportMonth').value = monthStr;

            // Load Initial Data
            loadDailyStats(todayStr);

            // Add Event Listener to Date Picker
            document.getElementById('filterDate').addEventListener('change', (e) => {
                loadDailyStats(e.target.value);
            });
        });

        // Fetch Stats
        function loadDailyStats(date) {
            const tableBody = document.getElementById('equipmentStatsTableBody');
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="py-12 text-center text-gray-500">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-[#0b6f76]" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>جاري جلب إحصائيات يوم ${date}...</span>
                        </div>
                    </td>
                </tr>
            `;

            fetch(`${BASE_URL}/routes/stats.php?action=daily&date=${date}`)
                .then(res => res.json())
                .then(data => {
                    dashboardData = data;
                    
                    // Update Cards
                    document.getElementById('statCreatedRequests').innerText = data.requests_created || 0;
                    //document.getElementById('statScheduledRequests').innerText = data.requests_scheduled || 0;
                    document.getElementById('statOutOfSystem').innerText = data.counts.out_of_system || 0;
                    document.getElementById('statReserved').innerText = data.counts.reserved || 0;
                    document.getElementById('statAvailable').innerText = data.counts.available || 0;

                    // Render Table
                    renderTable();
                })
                .catch(err => {
                    console.error(err);
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="py-12 text-center text-red-500 font-semibold">
                                فشل في تحميل البيانات. يرجى التحقق من اتصال الخادم.
                            </td>
                        </tr>
                    `;
                });
        }

        // Render stats table
        function renderTable() {
            const tableBody = document.getElementById('equipmentStatsTableBody');
            tableBody.innerHTML = '';

            if (!dashboardData || !dashboardData.all_equipment || dashboardData.all_equipment.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400">
                            لا توجد معدات مسجلة في النظام
                        </td>
                    </tr>
                `;
                return;
            }

            let list = dashboardData.all_equipment;

            // Apply Status Filter
            if (currentStatusFilter) {
                list = list.filter(item => item.status === currentStatusFilter);
                
                const filterLabels = {
                    'out_of_system': 'خارج الخدمة',
                    'reserved': 'محجوزة',
                    'available': 'متاحة بدون حجز'
                };
                
                const indicator = document.getElementById('tableFilterIndicator');
                indicator.innerText = filterLabels[currentStatusFilter];
                indicator.classList.remove('hidden');
                document.getElementById('btnResetFilters').classList.remove('hidden');
            } else {
                document.getElementById('tableFilterIndicator').classList.add('hidden');
                document.getElementById('btnResetFilters').classList.add('hidden');
            }

            // Apply Search Query
            if (currentSearchQuery.trim() !== '') {
                const query = currentSearchQuery.toLowerCase();
                list = list.filter(item => 
                    (item.equipment_name && item.equipment_name.toLowerCase().includes(query)) ||
                    (item.equipment_code && item.equipment_code.toLowerCase().includes(query))
                );
            }

            if (list.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400">
                            لا توجد نتائج تطابق خيارات التصفية أو البحث
                        </td>
                    </tr>
                `;
                return;
            }

            list.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50 transition-colors';

                let badgeHtml = '';
                if (item.status === 'out_of_system') {
                    badgeHtml = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                            خارج الخدمة (متوقفة)
                        </span>
                    `;
                } else if (item.status === 'reserved') {
                    badgeHtml = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                            محجوزة
                        </span>
                    `;
                } else {
                    badgeHtml = `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            شغالة ومتاحة
                        </span>
                    `;
                }

                tr.innerHTML = `
                    <td class="py-4 px-6 font-semibold text-gray-800">${item.equipment_name}</td>
                    <td class="py-4 px-6 text-gray-500 font-mono">${item.equipment_code}</td>
                    <td class="py-4 px-6">${badgeHtml}</td>
                    <td class="py-4 px-6 text-center font-bold text-gray-700">${item.events_count || 0}</td>
                    <td class="py-4 px-6 text-xs text-gray-400 max-w-xs truncate" title="${item.description ?? ''}">
                        ${item.description || '<span class="text-gray-300">لا توجد تفاصيل</span>'}
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // Filter Table by clicking on cards
        function filterTableByStatus(status) {
            currentStatusFilter = status;
            renderTable();
            // Scroll to table smoothly
            document.getElementById('equipmentStatsTableBody').closest('.bg-white').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Reset Table Filters
        function resetTableFilters() {
            currentStatusFilter = null;
            document.getElementById('searchEquipment').value = '';
            currentSearchQuery = '';
            renderTable();
        }

        // Handle search typing
        function handleSearch() {
            currentSearchQuery = document.getElementById('searchEquipment').value;
            renderTable();
        }

        // Monthly Reports Export: Excel File
        function downloadExcel(event) {
            const month = document.getElementById('reportMonth').value;
            if (!month) {
                alert('الرجاء اختيار الشهر أولاً');
                return;
            }

            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span> جاري التحميل...`;

            fetch(`${BASE_URL}/routes/stats.php?action=monthly&month=${month}`)
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    if (!data.daily_records || data.daily_records.length === 0) {
                        alert('لا توجد بيانات متاحة لهذا الشهر');
                        return;
                    }

                    // Prepare sheet 1: ملخص يومي
                    const dailyData = data.daily_records.map(row => ({
                        'التاريخ': row.date,
                        'عدد الطلبات المضافة': row.requests_created,
                        'عدد الحجوزات المنفذة/المجدولة': row.requests_scheduled,
                        'عدد المعدات خارج الخدمة': row.out_of_system,
                        'عدد المعدات المحجوزة': row.reserved,
                        'عدد المعدات المتاحة بدون حجز': row.available,
                        'إجمالي عدد المعدات': row.total_equipment
                    }));

                    // Prepare sheet 2: سجل الأعطال
                    const rejectionsData = data.out_of_service_log.map(row => ({
                        'التاريخ': row.date,
                        'اسم المعدة': row.equipment_name,
                        'كود المعدة': row.equipment_code,
                        'الحالة': 'خارج الخدمة (متعطلة)'
                    }));

                    // Prepare sheet 3: تفاصيل المعدات
                    const equipmentData = data.equipment_list.map(row => ({
                        'رقم المعدة ID': row.id,
                        'اسم المعدة': row.equipment_name,
                        'كود المعدة': row.equipment_code
                    }));

                    // Create Workbook
                    const wb = XLSX.utils.book_new();

                    const wsDaily = XLSX.utils.json_to_sheet(dailyData);
                    XLSX.utils.book_append_sheet(wb, wsDaily, "الملخص اليومي للطلبات");

                    const wsRejections = XLSX.utils.json_to_sheet(rejectionsData);
                    XLSX.utils.book_append_sheet(wb, wsRejections, "سجل الأعطال والمشاكل");

                    const wsEquipment = XLSX.utils.json_to_sheet(equipmentData);
                    XLSX.utils.book_append_sheet(wb, wsEquipment, "قائمة المعدات المسجلة");

                    // Save Excel File
                    XLSX.writeFile(wb, `Report_Monthly_${month}.xlsx`);
                })
                .catch(err => {
                    console.error(err);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert('حدث خطأ أثناء محاولة تصدير ملف Excel');
                });
        }

        // Monthly Reports Export: PDF Report
        function downloadPDF(event) {
            const month = document.getElementById('reportMonth').value;
            if (!month) {
                alert('الرجاء اختيار الشهر أولاً');
                return;
            }

            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span> جاري التحميل...`;

            fetch(`${BASE_URL}/routes/stats.php?action=monthly&month=${month}`)
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    if (!data.daily_records || data.daily_records.length === 0) {
                        alert('لا توجد بيانات متاحة لهذا الشهر');
                        return;
                    }

                    // Open a clean printable window and style it beautifully
                    const printWindow = window.open('', '_blank', 'width=1000,height=800');
                    
                    // Sum statistics
                    let totalCreated = 0;
                    let totalScheduled = 0;
                    data.daily_records.forEach(r => {
                        totalCreated += r.requests_created;
                        totalScheduled += r.requests_scheduled;
                    });

                    // Build daily records table rows HTML
                    let tableRowsHtml = '';
                    data.daily_records.forEach(r => {
                        tableRowsHtml += `
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 10px; font-weight: bold;">${r.date}</td>
                                <td style="padding: 10px; text-align: center;">${r.requests_created}</td>
                                <td style="padding: 10px; text-align: center;">${r.requests_scheduled}</td>
                                <td style="padding: 10px; text-align: center; color: #dc2626; font-weight: ${r.out_of_system > 0 ? 'bold' : 'normal'};">
                                    ${r.out_of_system}
                                </td>
                                <td style="padding: 10px; text-align: center; color: #d97706;">${r.reserved}</td>
                                <td style="padding: 10px; text-align: center; color: #059669;">${r.available}</td>
                            </tr>
                        `;
                    });

                    // Build rejections log rows HTML
                    let rejectionRowsHtml = '';
                    if (data.out_of_service_log && data.out_of_service_log.length > 0) {
                        data.out_of_service_log.forEach(rej => {
                            rejectionRowsHtml += `
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 10px; font-weight: bold;">${rej.date}</td>
                                    <td style="padding: 10px;">${rej.equipment_name}</td>
                                    <td style="padding: 10px; font-family: monospace;">${rej.equipment_code}</td>
                                    <td style="padding: 10px; color: #dc2626; font-weight: bold;">خارج الخدمة (متعطلة)</td>
                                </tr>
                            `;
                        });
                    } else {
                        rejectionRowsHtml = `
                            <tr>
                                <td colspan="4" style="padding: 20px; text-align: center; color: #64748b; font-style: italic;">
                                    لم يتم تسجيل أي أعطال أو خروج عن النظام خلال هذا الشهر
                                </td>
                            </tr>
                        `;
                    }

                    // Complete HTML Content for print
                    const htmlContent = `
                        <!DOCTYPE html>
                        <html lang="ar" dir="rtl">
                        <head>
                            <meta charset="UTF-8">
                            <title>تقرير النظام الشهري - ${month}</title>
                            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
                            <style>
                                body {
                                    font-family: 'Cairo', sans-serif;
                                    color: #1e293b;
                                    margin: 40px;
                                    background: #ffffff;
                                    direction: rtl;
                                }
                                .header {
                                    text-align: center;
                                    margin-bottom: 30px;
                                    border-bottom: 3px double #0b6f76;
                                    padding-bottom: 20px;
                                }
                                .header h1 {
                                    margin: 0;
                                    font-size: 26px;
                                    color: #0b6f76;
                                }
                                .header p {
                                    margin: 5px 0 0 0;
                                    color: #64748b;
                                    font-size: 14px;
                                }
                                .summary-grid {
                                    display: grid;
                                    grid-template-cols: repeat(3, 1fr);
                                    gap: 20px;
                                    margin-bottom: 40px;
                                }
                                .summary-card {
                                    border: 1px solid #e2e8f0;
                                    border-radius: 12px;
                                    padding: 15px;
                                    background: #f8fafc;
                                    text-align: center;
                                }
                                .summary-card h3 {
                                    margin: 0;
                                    font-size: 13px;
                                    color: #64748b;
                                }
                                .summary-card .value {
                                    font-size: 24px;
                                    font-weight: 800;
                                    color: #0f172a;
                                    margin-top: 5px;
                                }
                                .section-title {
                                    font-size: 18px;
                                    font-weight: 700;
                                    color: #0b6f76;
                                    margin-bottom: 15px;
                                    border-right: 4px solid #0b6f76;
                                    padding-right: 10px;
                                }
                                table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin-bottom: 40px;
                                    font-size: 12px;
                                }
                                th {
                                    background-color: #0b6f76;
                                    color: white;
                                    font-weight: 700;
                                    padding: 12px 10px;
                                    text-align: right;
                                }
                                td {
                                    padding: 8px 10px;
                                }
                                .footer {
                                    text-align: center;
                                    margin-top: 50px;
                                    font-size: 11px;
                                    color: #94a3b8;
                                    border-top: 1px solid #e2e8f0;
                                    padding-top: 15px;
                                }
                                @media print {
                                    body { margin: 20px; }
                                    .no-print { display: none; }
                                }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h1>التقرير الشهري لحالة المعدات والطلبات</h1>
                                <p>شهر التقرير: ${month} | تاريخ الطباعة والتصدير: ${new Date().toLocaleDateString('ar-EG')}</p>
                            </div>

                            <div class="summary-grid">
                                <div class="summary-card">
                                    <h3>إجمالي طلبات الحجز المضافة</h3>
                                    <div class="value">${totalCreated}</div>
                                </div>
                                <div class="summary-card" style="border-color: #bfdbfe;">
                                    <h3>إجمالي الطلبات المجدولة للتنفيذ</h3>
                                    <div class="value" style="color: #2563eb;">${totalScheduled}</div>
                                </div>
                                <div class="summary-card" style="border-color: #fca5a5;">
                                    <h3>عدد الأعطال المسجلة (حالات خارج الخدمة)</h3>
                                    <div class="value" style="color: #dc2626;">${data.out_of_service_log ? data.out_of_service_log.length : 0}</div>
                                </div>
                            </div>

                            <div class="section-title">الملخص اليومي التفصيلي</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th style="text-align: center;">الطلبات المضافة</th>
                                        <th style="text-align: center;">الطلبات المجدولة</th>
                                        <th style="text-align: center;">خارج الخدمة</th>
                                        <th style="text-align: center;">محجوزة</th>
                                        <th style="text-align: center;">متاحة بدون حجز</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${tableRowsHtml}
                                </tbody>
                            </table>

                            <div class="section-title">سجل مشاكل المعدات وخروجها من الخدمة</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th>اسم المعدة</th>
                                        <th>الكود</th>
                                        <th>حالة المعدة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rejectionRowsHtml}
                                </tbody>
                            </table>

                            <div class="footer">
                                تقرير تلقائي صادر عن نظام إدارة المعدات والطلبات الذكي. جميع الحقوق محفوظة &copy; ${new Date().getFullYear()}
                            </div>

                            <script>
                                window.onload = function() {
                                    window.print();
                                }
                            <\/script>
                        </body>
                        </html>
                    `;

                    printWindow.document.open();
                    printWindow.document.write(htmlContent);
                    printWindow.document.close();
                })
                .catch(err => {
                    console.error(err);
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    alert('حدث خطأ أثناء محاولة تصدير تقرير PDF');
                });
        }
    </script>
</body>

</html>