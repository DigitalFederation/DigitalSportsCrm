window.initMap = function(lat, lng) {
  let map = L.map('map').setView([lat, lng], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
      maxZoom: 12,
  }).addTo(map);
  L.marker([lat, lng]).addTo(map);
}