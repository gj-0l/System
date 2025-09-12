<?php
require_once '../core/Database.php';
require_once '../config/config.php';

require_once __DIR__ . '/../../tools/sidebar.php';
require_once __DIR__ . '/../../tools/navbar.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Show Users</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        h2 {
            color: #1d8e96;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #c8e6c9;
            text-align: right;
        }

        th {
            background-color: #a8e6cf;
            color: #1d8e96;
        }

        tr:hover {
            background-color: #f1f8f4;
        }

        .btn-delete,
        .btn-update {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
            margin-bottom: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-delete {
            background-color: #e53935;
            color: white;
        }

        .btn-delete:hover {
            background-color: #ab000d;
        }

        .btn-update {
            background-color: #3587e5;
            color: white;
        }

        .btn-update a {
            text-decoration: none;
            color: white;
            display: block;
            width: 100%;
        }

        .btn-update:hover {
            background-color: #1c5fc4;
        }

        a.back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #1d8e96;
            font-weight: bold;
            text-decoration: none;
        }

        a.back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                display: none;
            }

            tr {
                margin-bottom: 15px;
                border: 1px solid #c8e6c9;
                border-radius: 10px;
                padding: 10px;
            }

            td {
                text-align: right;
                /* ✅ كل النصوص يمين */
                padding: 8px 10px 8px 130px;
                /* ✅ نترك مساحة لليبل */
                position: relative;
                direction: rtl;
                /* ✅ يلتزم بالاتجاه */
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                position: absolute;
                right: 10px;
                /* ✅ الليبل على اليمين */
                top: 50%;
                transform: translateY(-50%);
                white-space: nowrap;
                color: #333;
            }

            td:last-child a {
                display: block;
                /* ✅ كل زر بسطر */
                width: 100%;
                /* ✅ ياخذ عرض كامل */
                text-align: center;
                /* ✅ النص بالوسط */
                margin-bottom: 6px;
                /* ✅ مسافة بين الأزرار */
            }

            td:last-child a:last-child {
                margin-bottom: 0;
                /* ✅ آخر زر بلا مسافة إضافية */
            }
        }
    </style>
</head>

<body>
    <?php renderNavbar('Users List'); ?>
    <div class="dashboard-container min-h-screen bg-gray-50">
        <?php renderSidebar('users'); ?>

        <main class="p-6 ml-4 md:pl-64">
            <h3 style="text-align:center;">User List</h3>
            <div class="w-full max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>action</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <!-- سيتم ملؤه عبر JavaScript -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // جلب البيانات من الراوت
        fetch('../routes/auth.php?action=get_users')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('userTableBody');
                tbody.innerHTML = '';

                if (!data.success) {
                    tbody.innerHTML = `<tr><td colspan="4">${data.message || 'حدث خطأ في جلب البيانات'}</td></tr>`;
                    return;
                }

                if (!Array.isArray(data.users)) {
                    tbody.innerHTML = '<tr><td colspan="4">حدث خطأ في جلب البيانات</td></tr>';
                    return;
                }

                data.users.forEach((user, index) => {
                    const row = document.createElement('tr');

                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td>${user.type}</td>
                        <td>${user.status}</td>
                        <td>
                            <a href="<?= BASE_URL ?>/public/update_user.php?id=${user.id}" style="background:#1976d2; color:white; padding:6px 12px; border-radius:6px; text-decoration:none; margin-right:4px;">Edit</a>
                            <a href="#" 
                                data-id="${user.id}" 
                                onclick="deleteUser(event)" 
                                style="background:#d32f2f; color:white; padding:6px 12px; border-radius:6px; text-decoration:none;">
                                Delete
                            </a>
                        </td >
                    `;

                    tbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching users:', error);
                document.getElementById('userTableBody').innerHTML =
                    '<tr><td colspan="4">فشل في الاتصال بالخادم</td></tr>';
            });

        function deleteUser(event) {
            event.preventDefault();

            const userId = event.target.getAttribute('data-id');

            if (!userId) {
                Swal.fire("خطأ", "لا يمكن تحديد المستخدم", "error");
                return;
            }

            Swal.fire({
                title: "Are you sure?",
                text: "you won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Confirm"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`../routes/user.php`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'delete',
                            id: userId
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("Done", data.message, "success").then(() => location.reload());
                            } else {
                                Swal.fire("خطأ", data.message || "فشل حذف المستخدم", "error");
                            }
                        })
                        .catch(() => {
                            Swal.fire("خطأ", "فشل الاتصال بالخادم", "error");
                        });
                }
            });
        }

    </script>

</body>

</html>