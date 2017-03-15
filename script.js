function addCover(url) {
    var cover = document.createElement("div");
    cover.classList.add("cover");
    cover.style.backgroundImage = 'url('+url+')';
    document.body.insertBefore(cover, document.body.firstChild);
}

function addTitleFont(url) {
    var fontStyle = document.createElement("style");
    fontStyle.innerHTML = '@font-face{font-family:"TitleFont";src:url("'+url+'");} h1,h2,h3,h4,h5,h6{font-family: "TitleFont", serif;}';
    document.head.appendChild(fontStyle);
}

function addTextFont(url) {
    var fontStyle = document.createElement("style");
    fontStyle.innerHTML = '@font-face{font-family:"TextFont";src:url("'+url+'");} p{font-family: "TextFont", serif;}';
    document.head.appendChild(fontStyle);
}

function getMetadata() {
    var metadata = [];
    document.body.childNodes[0].childNodes.forEach(function(e) {
        var key, value;
        if(e.nodeType == 8) {
            [key, ...value] = e.nodeValue.split(":");
            value = value.reduce(function(acc, str) {
                return acc + ":" + str;
            });
            key = key.trim();
            value = value.trim();
            metadata.push({key: key, value: value});
        }
    });
    return metadata;
}

function applyMetadata(metadata) {
    metadata.forEach(function(e) {
        switch (e.key) {
            case "title":
                document.title = e.value;
                break;
            case "cover-image":
                addCover(e.value);
                break;
            case "title-font":
                addTitleFont(e.value);
                break;
            case "text-font":
                addTextFont(e.value);
                break;
        }
    });
}

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
    applyMetadata(getMetadata());
}

window.onload = loadNext;
