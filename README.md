# mediawiki-vimeo

## what it does

mediawiki-vimeo is a mediawiki extension to include thumbnails of your vimeo-videos on a mediawiki page. There is an existing template for the Widget-Extension, but it includes the whole video as iframe. That iframe contains a flash object and a google analytics tag. If you use multiple videos on one page this can seriously slow down the users computer.

An example of the mediawiki-vimeo extension can be found here, at the bottom of the page: http://www.hackerspace-bamberg.de/

## Usage

To use the Vimeo extension in your mediawiki, all you have to do is:

&lt;vimeo width="200" height="110"&gt;<br>
123123123<br>
12312345<br>
3454853<br>
4589399<br>
&lt;/vimeo&gt;

Width and height attribute is optional. Each Video-ID is seperated by a newline

## Installation

Switch to your mediawiki-extension directory:

    cd /var/www/mediawik/extensions

Clone VimeoList from my repostory (or a fork on your repository):

    git clone https://github.com/Schinken/mediawiki-vimeo.git

instead you can also install it from tarball:

    wget 'https://github.com/Schinken/mediawiki-vimeo/tarball/master' -O VimeoList.tar.gz
    tar xvfz VimeoList.tar.gz

... and as the last step, you need to add the extension to your LocalSettings.php (in your mediawiki-root directory):

    include_once("$IP/extensions/VimeoList/VimeoList.php");
