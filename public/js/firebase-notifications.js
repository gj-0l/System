import "./firebase-config.js";
import {
  getToken,
  onMessage,
} from "https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging.js";

fetch("/routes/notifications.php?action=save_token", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({ token: currentToken }),
})
  .then((res) => res.json())
  .then((data) => console.log("Token Save Response:", data))
  .catch((err) => console.error("Token Save Error:", err));

if ("serviceWorker" in navigator) {
  navigator.serviceWorker.register("/js/firebase-sw.js").then((swReg) => {
    console.log("Service Worker Registered", swReg);

    Notification.requestPermission().then((permission) => {
      if (permission === "granted") {
        getToken(window.messaging, {
          vapidKey:
            "BLvVVJkkOyQNHDeca15iLwY7RLOqIf5xWooimnt_xWjqyGN7b6Q2I59qsX5WizmlrNRyuo57QqmCOpqaiJ90Da0",
          serviceWorkerRegistration: swReg,
        }).then((currentToken) => {
          if (currentToken) {
            fetch("/routes/notifications.php?action=save_token", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ token: currentToken }),
            });
          }
        });
      }
    });

    onMessage(window.messaging, (payload) => {
      const { title, body } = payload.notification;

      const container = document.getElementById("notifications-container");
      const card = document.createElement("div");
      card.className = "notification";
      card.innerHTML = `<h4>${title}</h4><p>${body}</p><hr/>`;
      container.prepend(card);
    });
  });
}
