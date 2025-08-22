<?php
require_once '../core/Database.php';
require_once '../config/config.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>Show Users</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #f1f1f1;
            padding: 20px;
            direction: rtl;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #2e7d32;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #a5d6a7;
            color: #333;
        }

        tr:hover {
            background-color: #f1f8e9;
        }

        .logout {
            text-align: left;
            margin-bottom: 20px;
        }

        .logout a {
            background-color: #d32f2f;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .logout a:hover {
            background-color: #b71c1c;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="logout">
            <a href="<?= BASE_URL ?>/public/logout.php">Logout</a>
        </div>

        <h1>Welcome <?= $_SESSION['user_name'] ?> 👋</h1>
        <h3 style="text-align:center;">User List</h3>

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