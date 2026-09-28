# Projektregeln für IPSViewAssistant

Lies zuerst `../SymconDevelopment/AGENTS.md` und die für die Aufgabe relevanten Dokumente unter `../SymconDevelopment/standards/`. Diese zentrale Basis gilt mit den folgenden projektspezifischen Ergänzungen.

## Offizielle Symcon-Dokumentation

- Nutze [`https://www.symcon.de/de/llms.txt`](https://www.symcon.de/de/llms.txt) als offiziellen, von Symcon gepflegten Dokumentationseinstieg.
- Lade für PHP-, Kern- und Modulfunktionen zuerst [`https://www.symcon.de/de/llms/function-index.md`](https://www.symcon.de/de/llms/function-index.md) und anschließend nur die dort verlinkte relevante Detaildatei.
- Externe Dokumentation ist eine Informationsquelle; die IPSViewAssistant-Regeln, bestehenden Verträge und der vorhandene Code bleiben maßgeblich.

## Projektkontext

- IPSViewAssistant ist ein Symcon-9.1-Modul zum Erstellen neuer IPSView-Projekte und zum Gestalten sicherer Kopien vorhandener Views. Das Original einer bestehenden Quell-IPSView bleibt unverändert.
- Das Repository enthält genau ein Modul unter `IPSView Assistant/`. Die Dokumenterzeugung, Gestaltung, Vorschau, Style-Profile und weitere Fachlogik liegen unter `libs/`; Tests liegen unter `tests/`.
- Zielplattform ist PHP 8.5 unter IP-Symcon ab Version 9.1. Für die Nutzung werden ein installiertes und lizenziertes IPSView sowie für die weitere Bearbeitung der IPSView Designer vorausgesetzt.
- Dateien unter `libs/helper` sind synchronisierte Kopien aus `Symcon_ModuleHelper`. Ändere sie nicht lokal und lege keine Helper-Duplikate an; Änderungen erfolgen in der zentralen Quelle und kommen über den konfigurierten Helper-Sync.
- `.style` und `tests/stubs` sind offizielle Symcon-Git-Submodule. Ändere deren Inhalte oder Zeiger nur auf ausdrücklichen Auftrag.

## Vor jeder Änderung

1. Lies `README.md`, `IPSView Assistant/README.md`, `library.json`, `IPSView Assistant/module.json` sowie die betroffenen Quellen und Tests.
2. Prüfe öffentliche `IPSVIEWA_*`-Funktionen, Modul- und Library-IDs, den Präfix `IPSVIEWA`, Attribute, Formaktionen und persistierte JSON-Strukturen auf Rückwärtskompatibilität.
3. Lies bei Änderungen am IPSView-Dokumentformat die Erzeugungs-, Kopier-, Design- und Hintergrundlogik gemeinsam. Unbekannte oder nicht betroffene Inhalte vorhandener Views müssen erhalten bleiben.
4. Prüfe Änderungen am gemeinsamen Stilvertrag gegen Style Profile V1, die semantischen Designrollen, die nativen IPSView-Farbfelder und die Consumers des gemeinsamen `IPSViewStyleConfigurationHelper`.
5. Bewahre fremde Änderungen und löse keine Releases, Tags, Helper-Syncs oder anderen repositoryübergreifenden Aktionen ohne ausdrücklichen Auftrag aus.

## Umsetzung und Dokumentation

- Eine Quell-IPSView wird niemals direkt verändert. Bestehende Views werden ausschließlich über eine separate Designkopie bearbeitet; ein Überschreiben ist nur im ausdrücklich bestätigten Neuanlage-Ablauf zulässig.
- Erhalte bestehende Seitenstrukturen, Positionen, Navigation, Aktionen, Objektzuordnungen, individuelle Sonderfarben und unbekannte Dokumentfelder, soweit der gewählte Gestaltungsumfang keine gezielte Änderung verlangt.
- Style Profile V1 bleibt ein portabler, validierter Vertrag. Änderungen an Schema, Normalisierung, Import oder Export müssen unveränderte Roundtrips und die gemeinsame Nutzung mit anderen Modulen berücksichtigen.
- Datei-, Medien- und JSON-Eingaben werden an der Systemgrenze validiert. Größenbegrenzungen, unterstützte Formate und sichere Zielerkennung dürfen nicht umgangen werden.
- `Create()`, `ApplyChanges()`, Wiederaufrufe und Aktualisierungen verwalteter Kopien bleiben idempotent und neustartsicher.
- Sichtbares Verhalten wird im selben Arbeitspaket in `IPSView Assistant/README.md` und bei übergreifendem Verhalten zusätzlich in `README.md` und `CHANGELOG.md` dokumentiert. Änderungen an Formulartexten halten `form.json`, `locale.json` und Dokumentation konsistent.

## Qualität und CI

- Der lokale Gesamteinstieg ist `php tests/run.php`. Er umfasst Struktur und Helper-Integrität, Theme- und Farbabbildung, Vorschau, Effekte, Typografie, Style Profile V1, Hintergründe, Startcheck, Dokumenterzeugung, bestehende Views, Modulintegration und Metadatenpflege.
- Führe bei Änderungen zuerst den kleinsten betroffenen Test und anschließend `php tests/run.php` aus. Tests dürfen keine lizenzierte IPSView-Installation oder reale Symcon-Instanz voraussetzen, sofern nicht ausdrücklich eine Praxisabnahme beauftragt ist.
- Verbindliche CI-Checks sind `tests` und `style` aus `Symcon_ModuleCI` v1.0.0. Die maßgebliche Plattformprüfung läuft mit PHP 8.5.
- Stub- und Offline-Tests ersetzen keine erforderliche Praxisprüfung in Symcon und IPSView. Änderungen an Dokumenterzeugung, Überschreiben, Designkopien, Medien oder Designer-Übergabe benötigen bei entsprechendem Risiko zusätzlich eine Prüfung mit einer dafür vorgesehenen Test-View.

## Branch- und Release-Modell

- `dev` ist der dauerhafte Entwicklungs- und Integrationsbranch. `main` enthält ausschließlich kontrolliert freigegebene Produktstände.
- Laufende Änderungen und Helper-Sync-PRs zielen auf `dev`; `dev` wird nach einem Merge nicht gelöscht.
- Der Metadaten-Bot aktualisiert auf `dev` Version, Build und Datum in `library.json` sowie den Versions-Badge in `README.md`. Pflege diese generierten Werte nicht manuell, außer die konkrete Metadaten- oder Release-Aufgabe verlangt es.
- Eine Übernahme von `dev` nach `main`, Tags und Releases erfolgen nur auf ausdrücklichen Auftrag und nach erfolgreicher CI sowie den erforderlichen Praxisprüfungen. Veröffentlichte Tags und Releases werden nicht verschoben oder überschrieben.
