# NotAwesome

A Joomla! 5 Cassiopeia child template which can automatically unload FontAwesome, but **ONLY** when it's not needed.

This is my reply to the bad advice given in https://web-eau.net/en/blog/remove-font-awesome-from-joomla-template.

In that article, Daniel says that you can unload FontAwesome by pointing its URI in the WebAssetManager to a blank
string. However, this is wrong! It assumes you have no third party extensions which depend on FontAwesome. If you do,
they ask Joomla! to load FontAwesome, nothing is loaded, and now we have all sorts of display and possibly JavaScript
issues.

What I am demonstrating in this repository is that you can do that **conditionally**. You can tell Joomla! to remove the
FontAwesome dependency as long as nothing other than the Cassiopeia template depends on it. It's not easy and requires
registering event handlers from within the template, but it's nothing that you cannot abstract away to a helper class
that you can reuse across all your templates, if that's something you want to do.

Note that there is exactly one case where this will fall flat on its face: if an extension developer includes
FontAwesome directly, either by doing something like `$wa->useStyle('fontawesome')` or otherwise registering and loading
the CSS file to FontAwesome directly. But, hey, this is a fringe enough use case that you will absolutely know if you
run into it.

Joomla! is an extremely powerful CMS, as long as you use it right.