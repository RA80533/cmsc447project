// OFFLINE STORAGE SCRIPT

var offlineStorageSupported = (typeof(Storage) !== "undefined");

// console.log(offlineStorageSupported)

var store;
var favZips = Array()

var loadFavZips = function () {
	var favContainer = document.getElementById("favorites_container");

	for (var i = 0; i < favZips.length; i++) {
		favContainer.innerHTML = favContainer.innerHTML + "<button>" + favZips[i] + "</button>";
	}
}

var updateZipStore = function () {
	store.favZips = JSON.stringify(favZips)
}


if (offlineStorageSupported) {
	store = window.localStorage;
}

if (store.favZips == null && offlineStorageSupported) {
	updateZipStore();
}


if (offlineStorageSupported) {
	favZips = JSON.parse(store.favZips);
	loadFavZips();
}


var addToFavorites = function (zipCode) {
	favZips.push(zipCode);
	updateZipStore();
	// console.log(zipCode + " added to favs");
}
