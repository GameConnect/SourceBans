const serverList = document.getElementById('server-list');
const infoUrl = serverList.dataset.infoUrl;

Array.from(serverList.querySelectorAll('tr[data-id]')).forEach(row => {
    fetch(infoUrl.replace('__ID__', row.dataset.id))
        .then(response => response.json())
        .then(data => {
            const hostname = row.querySelector('[data-column="hostname"]');
            const map = row.querySelector('[data-column="map"]');
            const players = row.querySelector('[data-column="players"]');

            hostname.textContent = (data.error ? data.error.message : data.hostname);

            if (map) {
                map.textContent = (data.error ? 'N/A' : data.map);
            }
            if (players) {
                players.textContent = (data.error ? 'N/A' : data.numplayers + '/' + data.maxplayers);
            }
        });
});
