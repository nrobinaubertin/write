// this function loads all images, one by one, starting by the content images from top to bottom.
function loadNext() {
    for(var img of document.getElementsByTagName("img")) {
        if(img.src != "") {
            continue;
        }
        img.onload = loadNext;
        img.onerror = loadNext;
        img.src = img.dataset.src;
        console.log(img.dataset.src);
        return;
    }
}

window.onload = loadNext;
