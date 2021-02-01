# Richdex

A lightweight content-focused directory index

## Setup

Just download the `index.php` file to a directory on your server! By default, it will provide a recursive directory listing with inline views of text, image, and video content.

To customize, include a `.config.php` file that returns an array with the configuration options you want to customize:

- recursive (`bool`): Determines if child directories are accessible
- hidden (`bool`): Whether hidden (dot prefix) items are listed
- dark (`bool` or `"auto"`): Determines if the dark styles should be used. Auto matches the user's device settings.
- autoplay (`bool`): Whether videos should auto-play
- loop (`bool`): Whether video playback should loop

## Alternatives

If you're just looking for a nice image gallery index, my old [Gallery](https://github.com/Alanaktion/Gallery) project may be closer to what you want. This one is intended to support a variety of file types with rich inline display, rather than a focus on images alone.
