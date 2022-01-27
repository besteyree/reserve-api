
importScripts('https://www.gstatic.com/firebasejs/8.4.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.4.1/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyD_g86Nr3uqZp_2mjAAzsYUXQCyGg1U1xQ",
    authDomain: "wegsoft-43793.firebaseapp.com",
    projectId: "wegsoft-43793",
    storageBucket: "wegsoft-43793.appspot.com",
    messagingSenderId: "792708413718",
    appId: "1:792708413718:web:c0d1bed80903db9d537837",
    measurementId: "G-ZMKZMHPW7Y"
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
    console.log(
        "[firebase-messaging-sw.js] Received background message ",
        payload,
    );
    // Customize notification here
    const notificationTitle = "Background Message Title";
    const notificationOptions = {
        body: "Background Message body.",
        icon: "/itwonders-web-logo.png",
    };

    return self.registration.showNotification(
        notificationTitle,
        notificationOptions,
    );
});