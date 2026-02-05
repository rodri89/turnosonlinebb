// Import and configure the Firebase SDK
// These scripts are made available when the app is served or deployed on Firebase Hosting
importScripts('https://www.gstatic.com/firebasejs/12.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.8.0/firebase-messaging-compat.js');

// Initialize the Firebase app in the service worker by passing in
// your app's Firebase config object.
// https://firebase.google.com/docs/web/setup#config-object
firebase.initializeApp({
  apiKey: "AIzaSyBLLw7_0CJZ4mepUZjWbdHN9SAwFbZTtz0",
  authDomain: "turnosonlinebb-8406b.firebaseapp.com",
  projectId: "turnosonlinebb-8406b",
  storageBucket: "turnosonlinebb-8406b.firebasestorage.app",
  messagingSenderId: "991206372015",
  appId: "1:991206372015:web:0ef83da53a43d5a3f11a5a",
  measurementId: "G-KBD5NC6E1V"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

// Optional: Handle background messages
messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  
  // Customize notification here
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
    body: payload.notification.body,
    icon: payload.notification.icon || '/images/iconos/turnosonlinebb_icon.png',
    badge: '/images/iconos/turnosonlinebb_icon.png',
    tag: 'turno-notification',
    requireInteraction: true
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});

