self.addEventListener('install', function(event){
    self.skipWaiting();
});

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var pushData = event.data.json();
    const options = {
        body: pushData.body,
        data: pushData.title
    };

    event.waitUntil(self.registration.showNotification(pushData.title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    event.waitUntil(
        clients.openWindow(event.notification.data)
    );
});