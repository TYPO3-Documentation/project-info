..  include:: /Includes.rst.txt

==============================
Sonstige Anpassungen am System
==============================

Alle weiteren Anpassungen am System, z.B. Modifikationen in Core-Dateien müssen ausführlich dokumentiert werden.
Hier ist es nötig, den genauen Grund der Anpassung im Core zu nennen. Sollte es zukünftig bessere
Lösungsmöglichkeiten geben, so müssen auch diese dokumentiert werden.

Beispiel
========

Sicherheitslücke im Caching-Mechanismus
---------------------------------------

Da in der aktuellen TYPO3 Version eine noch nicht geschlossene Sicherheitslücke im Caching-Mechanismus bekannt wurde,
ist dieser vorerst deaktiviert und zum zurücksetzen des Caches wurde ein Cronjob eingerichtet (:ref:`Cache Flush <cron>`)
Sobald die Sicherheitslücke seitens TYPO3 geschlossen wurde kann diese Anpassung entfernt und der Cronjob deaktiviert werden.

Ein diff mit den genauen Anpassungen am Core befindet sich in :file:`patches/patch123.diff`.
