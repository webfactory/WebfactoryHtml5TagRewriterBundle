Changelog for WebfactoryTagRewriterBundle
--------------

## Version 2.0.0

- Der default-Modus ist jetzt nicht mehr `xhtml1`, sondern `html5`. Konfiguriere `xhtml1` ausdrücklich, wenn Du es benötigst. Wenn Du möchtest, kannst Du `html5` aus der Konfiguration entfernen.

## Version 1.6.1

- Es wird nicht mehr der erste definierte TagRewriter als default angenommen, sondern 
  nur noch eine Definition mit einer expliziten `default="true"`-Angabe.

## Version 1.4.0

- Loads the webfactory.tag_rewriter service lazily
