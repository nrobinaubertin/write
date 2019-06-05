Write
=====
*Static blog generator in less than 100 LOC*

![Write](write.jpg)

### What is Write ?
Write is a static blog generator in python. Like a pen, it's a simple but powerful tool that does what you want, nothing more.  

### Requirements
- Python 3+
- [Misletoe](https://github.com/miyuchina/mistletoe)

### How does it work ?

Each article of your blog is a [commonmark](https://commonmark.org/) markdown file.  
The are some options that can be specified by placing html comments in your markdown file like this:
```
<!-- key: value -->
```
The options are:
- title: specify a title for your post
- lang: specify a lang for your post
- title-font: path or name of a font file that will be used on your titles
- text-font: path or name of a font file that will be used on your text
- style-file: path or name of a css file that will be used on the html
- script-file: path or name of a js file that will be used on the html

In order to process your files, just point the script towards your blog directory and specify an output directory :
```
write.py blog/ dist/
```
That's it !
