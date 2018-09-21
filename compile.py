#!/usr/bin/env python

import os, sys, mistletoe, re, shutil


def compilePosts(targetDir, rootDir, distDir):
    rootDir = os.path.abspath(rootDir)
    if not os.path.isdir(rootDir):
        sys.exit(rootDir + " is not an existing directory.")
    if not os.path.isdir(distDir):
        os.mkdir(distDir)
    for filename in os.listdir(rootDir + os.sep + targetDir):
        if os.path.isdir(rootDir + os.sep + targetDir + os.sep + filename):
            compilePosts(
                targetDir + os.sep + filename, rootDir, distDir + os.sep + filename
            )
            continue
        basename, ext = os.path.splitext(filename)
        if ext == ".md":
            with open(distDir + os.sep + basename + ".html", "w") as distFile:
                distFile.write(
                    genPostHTML(rootDir + os.sep + targetDir + os.sep + filename)
                )
        else:
            shutil.copyfile(
                rootDir + os.sep + targetDir + os.sep + filename,
                distDir + os.sep + filename,
            )


def locateFile(filename, target_dir):
    while os.path.isdir(target_dir) and not os.path.isfile(
        os.path.join(target_dir, filename)
    ):
        target_dir = os.path.dirname(target_dir)
        if target_dir == "/":
            return ""
    return os.path.abspath(os.path.join(target_dir, filename))


def getRelativePath(path1, path2):
    path1 = os.path.abspath(path1)
    path2 = os.path.abspath(path2)
    c = len(os.path.commonpath([path1, path2]).split(os.sep))
    ll = [".." * (len(path1.split(os.sep)) - c)]
    return "/".join(ll + list(path2.split(os.sep)[c:])).strip("/")


def getMetadata(file):
    metadata = {}
    with open(file, "r") as f:
        for line in f.readlines():
            m = re.match(r"<!--([^>]*)-->", line)
            if m:
                key = m.group(1).split(":", 1)[0].strip()
                value = m.group(1).split(":", 1)[1].strip()
                metadata[key] = value
            else:
                break
    return metadata


def genCoverImageHTML(metadata):
    coverPicture = (
        '<div class="cover"><div class="cover-img" style="background-image:url('
        + metadata["cover-image"]
        + ')"></div>'
    )
    if "cover-credit-url" in metadata and "cover-credit-title" in metadata:
        coverPicture += (
            '<a class="credit-badge" href="'
            + metadata["cover-credit-url"]
            + '" target="_blank" rel="noopener noreferrer">'
        )
        coverPicture += '<span><svg viewBox="0 0 32 32">'
        coverPicture += '<path d="M20.8 18.1c0 2.7-2.2 4.8-4.8 4.8s-4.8-2.1-4.8-4.8c0-2.7 2.2-4.8 4.8-4.8 2.7.1 4.8 2.2 4.8 4.8zm11.2-7.4v14.9c0 2.3-1.9 4.3-4.3 4.3h-23.4c-2.4 0-4.3-1.9-4.3-4.3v-15c0-2.3 1.9-4.3 4.3-4.3h3.7l.8-2.3c.4-1.1 1.7-2 2.9-2h8.6c1.2 0 2.5.9 2.9 2l.8 2.4h3.7c2.4 0 4.3 1.9 4.3 4.3zm-8.6 7.5c0-4.1-3.3-7.5-7.5-7.5-4.1 0-7.5 3.4-7.5 7.5s3.3 7.5 7.5 7.5c4.2-.1 7.5-3.4 7.5-7.5z"></path>'
        coverPicture += (
            "</svg></span><span>" + metadata["cover-credit-title"] + "</span></a>"
        )
    coverPicture += "</div>"
    return coverPicture


def genPostHTML(target):
    path = os.path.abspath(target)
    if not os.path.exists(path):
        return ""
    target_dir = os.path.dirname(path)
    metadata = getMetadata(path)

    html = "<!DOCTYPE html><html><head>"
    html = '<meta charset="utf8">'
    html += '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">'
    html += '<meta property="og:type" content="article">'
    if "title" in metadata:
        html += "<title>" + metadata["title"] + "</title>"
        html += '<meta property="og:title" content="' + metadata["title"] + '">'
    if "description" in metadata:
        html += (
            '<meta property="og:description" content="' + metadata["description"] + '">'
        )
    if "cover-image" in metadata:
        html += '<meta property="og:image" content="' + metadata["cover-image"] + '">'

    if "title-font" in metadata:
        font = locateFile(metadata["title-font"], target_dir)
        html += (
            '<style>@font-face{font-family:"TitleFont";src:url("'
            + getRelativePath(target_dir, font)
            + '");} h1,h2,h3,h4,h5,h6{font-family: "TitleFont", serif;}</style>'
        )

    if "text-font" in metadata:
        font = locateFile(metadata["text-font"], target_dir)
        html += (
            '<style>@font-face{font-family:"TextFont";src:url("'
            + getRelativePath(target_dir, font)
            + '");} body{font-family: "TextFont", sans-serif;}</style>'
        )

    if "style-file" in metadata:
        cssFile = locateFile(metadata["style-file"], target_dir)
    else:
        cssFile = locateFile("default.css", target_dir)
    html += (
        '<link rel="stylesheet" href="' + getRelativePath(target_dir, cssFile) + '"/>'
    )

    html += "</head><body>"

    if "cover-image" in metadata:
        html += genCoverImageHTML(metadata)
    html += "<main><article>"
    html += mistletoe.markdown(open(path, "r"))
    html += "</article></main>"

    if "script-file" in metadata:
        scriptFile = locateFile(metadata["script-file"], target_dir)
    else:
        scriptFile = locateFile("default.js", target_dir)
    html += '<script src="' + getRelativePath(target_dir, scriptFile) + '"></script>'

    html += "</body></html>"
    return html


compilePosts("", sys.argv[1], sys.argv[2])
