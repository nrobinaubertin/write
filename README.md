Write
=====
*Static blog generator in less than 200 LOC*

Each article of your blog is a markdown file.  
The are some options that can be specified by placing html comments in your markdown file like this:
```
<!-- key: value -->
```
The options are:
- title : specify a title for your post
- description : specify a description (useful for social media sharing)
- title-font : path or name of a font file that will be used on your titles
- text-font : path or name of a font file that will be used on your text
- cover-image : specify a relative path to an image that should be used as cover.
- cover-credit-title: give credit for the cover-image
- cover-credit-url: credit url for the cover-image

To install the project:
```
./install.php
```
To compile the markdown files:
```
./compile.php <target> <dist>
```
