# Debug-Ausgaben

## Aufbau und Umfang

IPSViewAssistant verwendet das mit OpenHomeAlarm abgenommene Schema: `Modul | Ereignis | Funktion:Zeile` und die Felder `Instanz`, `Vorgang`, `Aufruf`, `Meldung`, `Bezug`. Deutsche Meldungen erklären öffentliche Modul- und SharedStyle-Einstiege, Lebenszyklus, Import/Export, Erstellung, Gestaltung, Vorschau und Designer-Übergabe. Auch die internen Grenzen für Medienexport, Profilimport und Startcheck besitzen Start und Ende.

Verschachtelte Aufrufe teilen die Vorgangskennung; ein neuer äußerer Aufruf beginnt eine neue Kennung. Ergebnisse enthalten echte Rückgabewerte bei Zahlen/Boolean beziehungsweise Typ und Umfang bei Text/Strukturen. Ein Text-Ergebnis wird nicht automatisch als Erfolg gewertet. Behandelte Fehler erhalten `FEHLER` und einen Ausnahmeabbruch im Ende. Ursprüngliche Rückgaben und bestehende Fehlerbehandlung bleiben erhalten.

## Fachlicher Bezug und Schutz

Die Eingaben nennen freigegebene Medien-/Kategorie-IDs und Betriebsoptionen. Erfolgreiche Speicherung und die Designer-Übergabe werden getrennt erklärt. Importmeldungen nennen das Quellmedium, ohne Dokumente offenzulegen. Medienereignisse zeigen das Zielmedium. Die Quell-View wird gemäß bestehendem Vertrag ausschließlich gelesen.

View-Namen, Seiten, Dokumente, JSON, Hintergrundbilder, Style-Profile, Freitext und Exceptiontexte gelangen nicht in den Debug-Kanal. Der modulbezogene Adapter schützt auch ältere Meldungen aus unveränderten zentralen Helpern. Deren private Ursachentexte werden durch eine sichere deutsche Fehlerhülle ersetzt. Die gemeinsame Nutzlastgrenze beträgt 4096 Bytes. Es gibt keinen zusätzlichen Diagnosespeicher und keine zusätzlichen Symcon-Abfragen für Debug.

## Lokale Nachweise und praktische Abnahme

`php tests/debug_output.php` prüft echte öffentliche Modulaufrufe mit einem minimalen Offline-Kern: ausgeglichene Starts/Enden, neue Vorgänge, abgelehnte Importe, Schutz eines synthetischen Dokuments und unveränderte Rückgaben bei ausgefallenem Debug-Kanal. Der Test gehört zu `php tests/run.php`.

Die praktische Abnahme in Symcon/IPSView bleibt offen: neue Test-View erstellen, separate Kopie gestalten, Import/Export erfolgreich und ungültig ausführen, Vorschau ändern und Designer-Übergabe prüfen. Lesbarkeit, Meldungsmenge und Laufzeit sind dort zu beurteilen. Lokale Tests bestätigen keine IPSView-Laufzeitwirkung.
