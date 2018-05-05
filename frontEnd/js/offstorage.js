// OFFLINE STORAGE SCRIPT

var offlineStorageSupported = (typeof(Storage) !== "undefined");

// console.log(offlineStorageSupported)

var store;
var favZips = Array()

var clearFavZips = function () {
	var favContainer = document.getElementById("favorites_container");
	favContainer.innerHTML = ""
}

var loadFavZips = function () {
	var favContainer = document.getElementById("favorites_container");

	for (var i = 0; i < favZips.length; i++) {
		favContainer.innerHTML = favContainer.innerHTML + "<button onclick='removeFromFavorites(" + favZips[i] + ")'>" + favZips[i] + "</button>";
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
	clearFavZips();
	loadFavZips();
	// console.log(zipCode + " added to favs");
}

var removeFromFavorites = function (zipCode) {
	var index = favZips.indexOf(zipCode);
	if (index > -1) favZips.splice(index, 1);
	updateZipStore();
	clearFavZips();
	loadFavZips();
} 