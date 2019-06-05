#!/usr/bin/env python

import os, sys, mistletoe, re, shutil


def compilePosts(target_dir, rootDir, dist_dir):
    rootDir = os.path.abspath(rootDir)
    if not os.path.isdir(rootDir):
        sys.exit(rootDir + " is not an existing directory.")
    if not os.path.isdir(dist_dir):
        os.mkdir(dist_dir)
    for filename in os.listdir(rootDir + os.sep + target_dir):
        if os.path.isdir(rootDir + os.sep + target_dir + os.sep + filename):
            compilePosts(
                target_dir + os.sep + filename, rootDir, dist_dir + os.sep + filename
            )
            continue
        basename, ext = os.path.splitext(filename)
        if ext == ".md":
            with open(dist_dir + os.sep + basename + ".html", "w") as dist_file:
                dist_file.write(
                    genPostHTML(rootDir + os.sep + target_dir + os.sep + filename)
                )
        else:
            shutil.copyfile(
                rootDir + os.sep + target_dir + os.sep + filename,
                dist_dir + os.sep + filename,
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
    ll = [".."] * (len(path1.split(os.sep)) - c)
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


def genPostHTML(target):
    path = os.path.abspath(target)
    if not os.path.exists(path):
        return ""
    target_dir = os.path.dirname(path)
    metadata = getMetadata(path)

    lang = "en"
    if "lang" in metadata:
        lang = metadata["lang"]

    html = """
        <!DOCTYPE html><html lang="{}"><head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
    """.format(lang)

    if "title" in metadata:
        html += '<title>{}</title>'.format(metadata["title"])

    css_file = locateFile("default.css", target_dir)
    if "style-file" in metadata:
        css_file = locateFile(metadata["style-file"], target_dir)

    html += """
        <link rel='stylesheet' href='{}'/></head><body><main><article>{}</article></main>
    """.format(getRelativePath(target_dir, css_file), mistletoe.markdown(open(path, "r")))

    script_file = locateFile("default.js", target_dir)
    if "script-file" in metadata:
        script_file = locateFile(metadata["script-file"], target_dir)

    html += "<script src='{}'></script></body></html>".format(getRelativePath(target_dir, script_file));
    return html


compilePosts("", sys.argv[1], sys.argv[2])
