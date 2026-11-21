class LiveSearch {
    constructor() {
        // this gets the live search inputs
        this.nameInput = document.getElementById('live-name');
        this.speciesInput = document.getElementById('live-species');
        this.statusInput = document.getElementById('live-status');
        this.resultsBox = document.getElementById('live-results');
        this.timer = null;
        this.setupListeners();
    }
    setupListeners() {
        var self = this;
        // this waits a bit after typing before search
        this.nameInput.addEventListener('keyup', function() { clearTimeout(self.timer); self.timer = setTimeout(function() { self.doSearch(); }, 400); });
        this.speciesInput.addEventListener('keyup', function() { clearTimeout(self.timer); self.timer = setTimeout(function() { self.doSearch(); }, 400); });
        this.statusInput.addEventListener('change', function() { self.doSearch(); });
    }
    doSearch() {
        var self = this, name = this.nameInput.value.trim(), species = this.speciesInput.value.trim(), status = this.statusInput.value;
        if (name === '' && species === '' && status === '') { this.resultsBox.innerHTML = ''; this.resultsBox.style.display = 'none'; return; }
        this.resultsBox.style.display = 'block';
        this.resultsBox.innerHTML = '<p>Searching...</p>';
        // this sends ajax request to search php
        var url = 'search.php?name=' + encodeURIComponent(name) + '&species=' + encodeURIComponent(species) + '&status=' + encodeURIComponent(status) + '&token=' + encodeURIComponent(AJAX_TOKEN || '');
        fetch(url).then(function(r) { return r.json(); }).then(function(data) { self.showResults(data.pets, data.total); });
    }
    showResults(pets, total) {
        if (pets.length === 0) { this.resultsBox.innerHTML = '<p>No pets found.</p>'; return; }
        // this builds simple cards result
        var html = '<p>' + total + ' pets found (showing ' + pets.length + ')</p>';
        pets.forEach(function(pet) {
            html += '<div class="live-pet-card">';
            if (pet.photo_url) html += '<img src="Images/' + pet.photo_url + '" alt="' + pet.name + '">';
            html += '<div class="live-pet-info"><strong>' + pet.name + '</strong><span>' + pet.species + '</span>';
            html += pet.status === 'lost' ? '<span class="status-lost">Lost</span>' : '<span class="status-found">Found</span>';
            html += '</div></div>';
        });
        this.resultsBox.innerHTML = html;
    }
}
// this starts live search
var liveSearch = new LiveSearch();
