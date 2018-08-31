Each of your posts should be a markdown file with his assets in its own directory.  
All the post directories should go in a posts/ directory at the root of the repo.
You should create a homepage in the same way you create a post : by writing a markdown file. However, the homepage mardown file must live at the root of the posts/ directory.

The are some options that can be specified by placing html comments in your markdown file like this:
```
<!-- key: value -->
```
The options are:
- cover-image : specify a relative path to an image that should be used as cover.
- title : specify a title for your post
- description : specify a description (useful for social media sharing)
- title-font : specify a relative path to a font that will be used on your titles
- text-font : specify a relative path to a font that will be used on your text

To install the project:
```
php install.php
```
To compile the markdown files:
```
php bin/compile.php
```
