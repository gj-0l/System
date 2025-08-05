document.addEventListener("DOMContentLoaded", () => {
  const equipmentSelect = document.getElementById("equipmentSelect");
  const checklistForm = document.getElementById("checklistForm");
  const checklistTableBody = document.querySelector("#checklistTable tbody");
  const messageDiv = document.getElementById("message");
  const noChecklistMsg = document.getElementById("noChecklist");

  // تحميل المعدات
  fetch(`${BASE_URL}/routes/checklist_routes.php?action=getEquipments`)
    .then((res) => res.json())
    .then((data) => {
      data.forEach((eq) => {
        const opt = document.createElement("option");
        opt.value = eq.id;
        opt.textContent = eq.equipment_name;
        equipmentSelect.appendChild(opt);
      });
    });

  // عند اختيار المعدة
  equipmentSelect.addEventListener("change", () => {
    const id = equipmentSelect.value;
    checklistTableBody.innerHTML = "";
    checklistForm.style.display = "none";
    noChecklistMsg.style.display = "none";

    if (!id) return;

    fetch(
      `${BASE_URL}/routes/checklist_routes.php?action=getChecklist&id=${id}`
    )
      .then((res) => res.json())
      .then((data) => {
        if (data.length === 0) {
          noChecklistMsg.style.display = "block";
          return;
        }

        data.forEach((item) => {
          const row = document.createElement("tr");

          row.innerHTML = `
                        <td>${item.test_name}</td>
                        <td>${item.initial_action}</td>
                        <td>
                            <select name="status[${item.id}]">
                                <option value="accepted">✔️ مقبول</option>
                                <option value="rejected">❌ مرفوض</option>
                            </select>
                        </td>
                    `;

          checklistTableBody.appendChild(row);
        });

        checklistForm.style.display = "block";
      });
  });

  // إرسال النتائج
  checklistForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const selects = checklistForm.querySelectorAll("select[name^='status']");
    const status = {};

    selects.forEach((select) => {
      const id = select.name.match(/\d+/)[0];
      status[id] = select.value;
    });

    fetch(`${BASE_URL}/routes/checklist_routes.php?action=submitChecklist`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ status }),
    })
      .then((res) => res.json())
      .then((data) => {
        Swal.fire({
          icon: "success",
          title: "تم الحفظ",
          text: data.message,
          confirmButtonText: "موافق",
          confirmButtonColor: "#3085d6",
        });

        checklistForm.reset();
        checklistForm.style.display = "none";
        equipmentSelect.value = "";
        checklistTableBody.innerHTML = "";
        noChecklistMsg.style.display = "none";
      })
      .catch(() => {
        Swal.fire({
          icon: "error",
          title: "حدث خطأ",
          text: "تعذر حفظ النتائج. حاول مرة أخرى.",
          confirmButtonText: "موافق",
          confirmButtonColor: "#d33",
        });
      });
  });
});
