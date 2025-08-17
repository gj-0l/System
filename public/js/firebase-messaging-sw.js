importScripts(
  "https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"
);
importScripts(
  "https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js"
);

firebase.initializeApp({
  apiKey: "AIzaSyBwxIvQua1PMFur2bonw3ZSkRd2IL36e_A",
  authDomain: "mobile-equipment-3ac58.firebaseapp.com",
  projectId: "mobile-equipment-3ac58",
  storageBucket: "mobile-equipment-3ac58.firebasestorage.app",
  messagingSenderId: "736129810254",
  appId: "1:736129810254:web:1f70eaa87ec803279fa81f",
  measurementId: "G-DYC99K0M32",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});
