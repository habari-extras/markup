Plugin: markUp
URL: http://habariproject.org
Version: 0.7.3
Author: Habari Project

Purpose

markUp provides a simple text editor for those of us who want the productivity improvements of not having to type in all our own markup tags, but don't want to use a wysiwyg editor. markUp gives you the choice of using html, textile, bbcode or markdown as your markup language.

Out of the box markUp provides easy insertion of all header tags, paragraphs, unordered and ordered lists, bold, emphasis, images, and links.

Requirements

Just activate it.

Installation

1. Copy the plugin directory into your user/plugins directory or the site's plugins directory.
2. Go to the plugins page of your Habari admin panel.
3. Click on the Activate button for markItUp.
4. Configure the plugin. It defaults to having a simple skin for the editor, using html as the markup language and not to have any effect on comments.

Uninstallation

1. Got to the plugins page of your Habari admin panel.
2. Click on the Deactivate button.
3. Delete the markup directory from your user/plugins directory.
4. Delete the plugins configuration entries from the Options table.

Cleanup

markUp adds entries to the Options table of your database. These are all prefixed with the work 'Markup'. If you are uninstalling the plugin completely, these entries can be safely removed.

Changelog
Version 0.7.3
Fix: Internal changes to be compatible with Habari 0.9
Change: Updated to markItUp 1.1.11

Version 0.6
Change: Added option to allow commenters to use markup supported tags within comments
Change: Added option to include markup toolbar above comment form.

Version 0.5
Change: Updated to markItup 1.1.7
Change: Added a parser for the bbcode markup language.

Version 0.4
Change: Updated to markItUp 1.1.5.
Change: Added a parser for the textile markup language.

Version 0.3.2
Change: Added full screen mode to editor.

Version 0.3.1
Fix: Allow coexistence with other plugins that use John Gruber's markdown on the same Habari install.

Version 0.3
Change: Made the editor skin configurable.
Change: Added a parser for the markdown markup language.
Version 0.21
Fix: Move content label into the edit area of the content textarea.

Version 0.2
Fix: Changes necessitated by admin api changes

Version 0.1
Initial release

Credits

Little software is the product of one individual's efforts. markUp utilizes components developed by other developers. We are grateful for their efforts.

1. markItUp! - a jQuery based markup editor by Jay Salvat (http://markitup.jaysalvat.com/) forms the heart of markup.

2. PHP Markdown - a markdown parser by Michel Fortin (http://www.michelf.com/) is used for markdown parsing.

3. Textile -  a textile parser by Dean Allen <dean@textism.com>, Carlo Zottmann <carlo@g-blog.net>, and Alex Shiels (http://thresholdstate.com/) is used for textile parsing.

4. BBCode - a BBCode parser by Jay Salvat (http://markitup.jaysalvat.com/) is used for BBCode parsing. The class wrapper around the parser is by the Habari Community.
