class PetMap {
    constructor() {
        // this makes the map object
        this.map = L.map('map').setView([26.12, 50.56], 10);
        // this stores markers by pet id so we can open popup from the list click
        this.markersByPetId = {};
        this.markerLayer = L.layerGroup().addTo(this.map);
        this.noLocationTimer = null;
        // this adds map tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(this.map);
        this.getUserLocation(); this.loadPets(SELECTED_PET_ID || null); this.setupForm();
    }
    getUserLocation() {
        var self = this;
        if ("geolocation" in navigator) navigator.geolocation.getCurrentPosition(function(p) { self.map.setView([p.coords.latitude, p.coords.longitude], 11); });
    }
    loadPets(selectedPetId) {
        var self = this;
        // this gets pets from php then refreshes list + markers
        var url = 'pets_map.php' + (selectedPetId ? ('?pet_id=' + encodeURIComponent(selectedPetId)) : '');
        fetch(url).then(function(r) { return r.json(); }).then(function(data) {
            var list = document.getElementById('pet-list');
            list.innerHTML = '<h2>Pets</h2>';
            self.markerLayer.clearLayers(); self.markersByPetId = {};
            self.showList(data.pets); self.showMarkers(data.pets);
            if (selectedPetId) data.pets.forEach(function(pet) { if (String(pet.id) === String(selectedPetId)) self.selectPet(pet); });
        });
    }
    showList(pets) {
        var self = this, list = document.getElementById('pet-list');
        // this builds pet cards in the side list
        pets.forEach(function(pet) {
            var c = pet.status === 'lost' ? 'map-status-lost' : 'map-status-found';
            var photo = pet.photo_url ? '<img src="Images/' + pet.photo_url + '" alt="' + pet.name + '">' : '<div class="map-no-photo">No Photo</div>';
            var seen = pet.last_seen ? pet.last_seen : 'No sighting details yet';
            list.innerHTML += '<div class="pet-item" data-pet-id="' + pet.id + '">' + photo + '<div class="pet-item-text"><strong>' + pet.name + '</strong><small>' + pet.species + ' - Owner: ' + pet.owner + '</small><small>' + seen + '</small></div><span class="' + c + '">' + pet.status + '</span></div>';
        });
        list.querySelectorAll('.pet-item').forEach(function(item, i) { item.addEventListener('click', function() { list.querySelectorAll('.pet-item').forEach(function(x) { x.classList.remove('selected'); }); item.classList.add('selected'); self.selectPet(pets[i]); }); });
    }
    showMarkers(pets) {
        var self = this;
        // this adds map markers and popup for pets with coordinates
        pets.forEach(function(pet) {
            if (!pet.latitude || !pet.longitude) return;
            var marker = L.marker([pet.latitude, pet.longitude]).addTo(self.markerLayer);
            self.markersByPetId[pet.id] = marker;
            var c = pet.status === 'lost' ? 'map-status-lost' : 'map-status-found';
            var photo = pet.photo_url ? '<img class="popup-pet-photo" src="Images/' + pet.photo_url + '" alt="' + pet.name + '">' : '';
            var seen = pet.last_seen ? pet.last_seen : 'No sighting details yet';
            marker.bindPopup('<div class="popup-card">' + photo + '<strong>' + pet.name + '</strong><div>Species: ' + pet.species + '</div><div>Owner: ' + pet.owner + '</div><div>Last sighting: ' + seen + '</div><span class="' + c + '">' + pet.status + '</span></div>');
            marker.on('click', function() { self.selectPet(pet); });
        });
    }
    selectPet(pet) {
        var intro = document.getElementById('sighting-intro'), msg = document.getElementById('sighting-message');
        var list = document.getElementById('pet-list'), selectedItem = list.querySelector('.pet-item[data-pet-id="' + pet.id + '"]');
        list.querySelectorAll('.pet-item').forEach(function(x) { x.classList.remove('selected'); });
        if (selectedItem) { selectedItem.classList.add('selected'); selectedItem.scrollIntoView({ block: 'nearest' }); }
        if (pet.latitude && pet.longitude) {
            // this moves map and opens popup for selected pet
            this.map.setView([pet.latitude, pet.longitude], 12);
            if (this.markersByPetId[pet.id]) this.markersByPetId[pet.id].openPopup();
            intro.textContent = 'Now report where you saw this pet.'; msg.textContent = '';
        } else {
            // this shows red warning for pets without location
            msg.textContent = 'No map location yet. If you have info, please report a sighting below.';
            msg.style.color = 'red';
            if (this.noLocationTimer) clearTimeout(this.noLocationTimer);
            this.noLocationTimer = setTimeout(function() { msg.textContent = ''; }, 4000);
        }
        document.getElementById('sighting-pet-id').value = pet.id;
        document.getElementById('sighting-pet-name').textContent = 'Sighting for: ' + pet.name;
        document.getElementById('sighting-form').style.display = 'block';
        intro.style.display = 'block';
    }
    setupForm() {
        var self = this;
        // this button fills latitude and longitude from the location of the user
        document.getElementById('use-my-location').addEventListener('click', function() { navigator.geolocation.getCurrentPosition(function(p) { document.getElementById('sighting-lat').value = p.coords.latitude.toFixed(5); document.getElementById('sighting-lng').value = p.coords.longitude.toFixed(5); }); });
        // this handles submit without the page fullz reloading
        document.getElementById('sighting-form').addEventListener('submit', function(e) { e.preventDefault(); self.submitSighting(); });
    }
    submitSighting() {
        var self = this, petId = document.getElementById('sighting-pet-id').value, comment = document.getElementById('sighting-comment').value.trim(), msg = document.getElementById('sighting-message');
        var lat = parseFloat(document.getElementById('sighting-lat').value), lng = parseFloat(document.getElementById('sighting-lng').value);
        if (comment === '') { msg.textContent = 'Please add a comment first.'; msg.style.color = 'red'; return; }
        // this sends sighting data to php
        var fd = new FormData();
        fd.append('pet_id', petId); fd.append('comment', comment);
        fd.append('latitude', document.getElementById('sighting-lat').value); fd.append('longitude', document.getElementById('sighting-lng').value);
        fd.append('token', AJAX_TOKEN || '');
        fetch('add_sighting.php', { method: 'POST', body: fd }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) { msg.textContent = 'Sighting reported!'; msg.style.color = 'green'; document.getElementById('sighting-comment').value = ''; if (!isNaN(lat) && !isNaN(lng)) self.map.setView([lat, lng], 12); self.loadPets(petId); }
            else { msg.textContent = 'Something went wrong.'; msg.style.color = 'red'; }
        });
    }
}
// this starts the map
var petMap = new PetMap();
