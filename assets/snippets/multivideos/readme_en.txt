Installation

Create package using link https://github.com/Pathologic/MultiVideos/archive/1.0.zip in your private repository at http://extras.evolution-cms.com
Install package from repository and activate plugin after installation.

MultiVideos Plugin

This plugin is upgraded MultiPhotos. User can insert link to video (youtube, rutube, vimeo), thumbnail image and video title; besides, if thumbnail field is empty, then thumbnail image will be downloaded from video hosting to special folder after saving resource. All data is stored as json in single TV.
Class by Bezumkin is used to parse links and download thumbnails http://bezumkin.ru/sections/php/441/.

Plugin options

TV Ids � id of tv parameter, which contains videogallery. It's possible to submit several ids separated with comma.
Template, Role � ids of templates and roles allowed for plugin, optional
Preview: width, Preview: height � thumbnail size in manager
Thumbs folder � absolute path of folder for saving thumbnails downloaded from video hosting
Force download � choose �No� to allow thumbnail download only when there's no thumbnail image in folder

MultiVideos Snippet

As well as the plugin, it's descended from MultiPhotos snippet. Use it to render tv containing gallery.

Snippet Options

&tvname � name of tv-parameter
&id � id of resource with gallery attached, 
&fid � element number (0 is the first), if you need only one element for output
&random � 1 to shuffle output 
&limit � amount of elements to process
&outerTpl � name of the chunk for templating whole gallery, it requires to contain [+videos+] placeholder
&rowTpl � name of the chunk for templating one element, available placeholders are [+video+] (video link),[+thumb+] (relative thumbnail URL), [+title+] (video title), [+embed+] (link for embedding), [+num+] (element number, 0 is the first)
&pagination � 1 to paginate ouput
&display � amount of elements per page

getVideo Snippet

Useful, if resource contains single video, stored in TV-parameter of text type.
It has two main options: &link � video link, &action � determines snippet output. If &action=`embed` (default), then snippet returns link for embedding; if &action=`thumb`, snippet returns relative thumbnail URL or �not found� image.
Additional options: &thumbsUrl � relative URL of folder to store thumbnails (default is assets/images/video/), &emptyImage � relative URL of �not found� image (default is assets/snippets/phpthumb/noimage.png), &forceDownload � see plugin options (default is false).
