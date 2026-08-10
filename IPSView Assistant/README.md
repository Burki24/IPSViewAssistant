# IPSView Assistant

Der **IPSView Assistant** erleichtert das Erstellen und Gestalten von IPSView-Projekten direkt in Symcon. Neue Views werden mit einer Hauptseite und einem frei wählbaren Design angelegt. Das Design einer bestehenden View kann auf eine separate Kopie übertragen werden, ohne das Original zu verändern.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Installation](#3-installation)
4. [Neue View erstellen](#4-neue-view-erstellen)
5. [Bestehende View gestalten](#5-bestehende-view-gestalten)
6. [Design einstellen](#6-design-einstellen)
7. [Gestaltungsumfang bei bestehenden Views](#7-gestaltungsumfang-bei-bestehenden-views)
8. [Ergebnis im IPSView Designer öffnen](#8-ergebnis-im-ipsview-designer-öffnen)
9. [Wichtige Hinweise](#9-wichtige-hinweise)

### 1. Funktionsumfang

- Neue IPSView als Medienobjekt mit Hauptseite erstellen
- Zwischen einem übersichtlichen Schnellstart und dem vollständigen Expertenmodus wechseln
- Einsatzprofile für Wandtablet, Tablet, Smartphone und Browser verwenden
- Seitenverhältnis und Ausrichtung festlegen
- Optional ein zwei- oder dreispaltiges Start-Raster für die Hauptseite vorbereiten
- Konfiguration vor der Erstellung mit einem verständlichen Startcheck prüfen
- Vorgefertigte Farbdesigns auswählen und individuell anpassen
- Farben, Schatten, Transparenz und Farbverläufe einstellen
- Schriftgröße, Schriftart, Ecken und Rahmen festlegen
- Lokale PNG- und JPEG-Hintergrundbilder in die Hauptseite oder alle Seiten einbetten
- Änderungen unmittelbar in einer Vorschau beurteilen
- Nach der Erstellung eine geführte Übergabe zum ersten Bedienelement im IPSView Designer erhalten
- Bestehende IPSView laden und als separate Designkopie neu gestalten
- Bereits angelegte Designkopie bei späteren Speichervorgängen aktualisieren

### 2. Voraussetzungen

- Symcon ab Version 9.1
- Installiertes und lizenziertes IPSView (Standard oder Professional)

Der IPSView Designer wird zum Öffnen, Bearbeiten und Speichern der erzeugten View benötigt. Ist er nicht installiert oder kann der Assistant ihn nicht automatisch erkennen, bleibt die Erstellung möglich; der Startcheck zeigt dann einen gelben Hinweis.

### 3. Installation

1. Im Symcon **Modul-Control** die folgende URL hinzufügen:

   ```text
   https://github.com/Burki24/IPSViewAssistant
   ```

2. Anschließend eine Instanz **IPSView Assistant** erstellen.
3. Die Instanzkonfiguration öffnen.

#### 3.1 Assistentenmodus

Beim ersten Öffnen ist der **Schnellstart** aktiv. Die Schaltfläche **Schnellstart öffnen** startet einen nativen Symcon-Wizard mit vier aufeinanderfolgenden Seiten und den automatisch eingeblendeten Aktionen **Zurück**, **Weiter** und **OK**:

1. Einsatzprofil, Name und Zielkategorie festlegen.
2. Format, Ausrichtung, Vollbild und optionales Start-Raster auswählen.
3. Eine fertige Designvorlage und bei Bedarf ein Hintergrundbild auswählen und in der Vorschau prüfen.
4. Den verbindlichen Startcheck ausführen und die View erstellen oder eine eindeutig erkannte gleichnamige IPSView bewusst überschreiben.

Detailfarben, Effekte, Typografie, Formensprache und die Bearbeitung bestehender Views bleiben im Schnellstart ausgeblendet.

Der **Expertenmodus** stellt sämtliche Funktionen des Assistants bereit. Der zuletzt gewählte Modus wird in der Instanz gespeichert und beim nächsten Öffnen wiederhergestellt. Kurze, gleichrangige Entscheidungen wie Assistentenmodus, Ausrichtung, Start-Raster und Bildanordnung werden als direkt sichtbare Radioauswahl dargestellt; umfangreichere Auswahllisten bleiben kompakte Dropdowns.

### 4. Neue View erstellen

Im Schnellstart werden die grundlegenden Eigenschaften der neuen View nacheinander abgefragt. Im Expertenmodus stehen dieselben Angaben weiterhin direkt im Bereich **View-Einstellungen** zur Verfügung.

Das **Einsatzprofil** belegt Seitenverhältnis, Ausrichtung und Vollbildmodus gemeinsam vor. Sobald eine dieser Einstellungen manuell geändert wird, wechselt die Auswahl auf **Benutzerdefiniert**.

| Einsatzprofil | Voreinstellung |
|---|---|
| **Wandtablet** | 16:9, Querformat, 1360 x 765 logische Pixel, Vollbild |
| **Tablet** | 4:3, Querformat, 1024 x 768 logische Pixel, Vollbild |
| **Smartphone** | 16:9, Hochformat, 765 x 1360 logische Pixel, Vollbild |
| **Browser** | 16:9, Querformat, 1360 x 765 logische Pixel, Fenstermodus |
| **Benutzerdefiniert** | Seitenverhältnis, Ausrichtung und Vollbild frei wählen |

| Einstellung | Bedeutung |
|---|---|
| **Einsatzprofil** | Gemeinsame Voreinstellung für das vorgesehene Anzeigegerät. |
| **View-Name** | Name des neu angelegten IPSView-Medienobjekts im Symcon-Objektbaum. |
| **Zielkategorie** | Kategorie, in der das Medienobjekt angelegt wird. |
| **Name der Hauptseite** | Bezeichnung der ersten Seite innerhalb der neuen View. |
| **Seitenverhältnis** | Auswahl zwischen 1:1, 4:3, 8:5, 9:5, 13:6, 16:9 und 2:1. |
| **Ausrichtung** | Querformat oder Hochformat. |
| **Vollbild** | Nutzt am Client die gesamte Anzeige ohne Fensterrahmen. |
| **Start-Raster** | Erstellt optional eine leere Raster-Inhaltsseite mit zwei oder drei Spalten und bindet sie über einen Seiten-Container in die Hauptseite ein. |
| **Vorlage** | Grundlage der neuen View. Derzeit steht eine leere View zur Verfügung. Diese technische Auswahl wird nur im Expertenmodus angezeigt. |

Der Assistant verwendet für jedes Verhältnis eine exakt proportionale logische Startgröße. Im Hochformat werden Breite und Höhe vertauscht.

Das optionale **Start-Raster** bereitet eine leere Inhaltsseite für einen geordneten Einstieg im IPSView Designer vor. Da IPSView den nativen Rastermodus nur auf Standard- und Popup-Seiten unterstützt, legt der Assistant zusätzlich zur Hauptseite eine Standard-Inhaltsseite an und bindet sie über einen vollflächigen Seiten-Container ein. Die Auswahl **2 Spalten** bzw. **3 Spalten** verwendet dort eine relative Zellbreite von 50 % bzw. 33,333333 %. Die Designvorschau ordnet ihre Beispielkarten unmittelbar passend zur ausgewählten Spaltenzahl an und blendet dezente Rasterhilfen ein. Außer dem für die Anzeige notwendigen Seiten-Container werden keine Platzhalter oder Bedienelemente erzeugt. Später auf der Inhaltsseite hinzugefügte Bedienelemente werden durch den Rastermodus automatisch angeordnet; Reihenfolge und weitere Rasterwerte lassen sich dort über die Seiteneigenschaften ändern. Die IPSView-6.5-Dokumentation beschreibt den [Rastermodus in den Seiteneigenschaften](https://docu.brownson.at/viewdesigner/WebHelp/DesignerPropertiesPage.html) und die Einbindung von Standardseiten über einen [Seiten-Container](https://docu.brownson.at/viewdesigner/WebHelp/DesignerRepositoryTypePageContainer.html).

Direkt vor den Aktionsschaltflächen fasst der **Startcheck** die wichtigsten Voraussetzungen zusammen. Er prüft Namen und Zielkategorie, erkennt eine bereits vorhandene gleichnamige View, validiert Format, Start-Raster und Hintergrundbild und sucht nach dem installierten IPSView Designer. Grün bedeutet, dass die View erstellt werden kann. Gelb kennzeichnet einen hilfreichen, aber nicht blockierenden Hinweis – beispielsweise ein nur auf die Hauptseite angewendetes Hintergrundbild bei aktiviertem Start-Raster. Rot nennt die zu korrigierenden Angaben und deaktiviert **View erstellen**. Änderungen an den grundlegenden View-Einstellungen aktualisieren den Bericht direkt; mit **Erneut prüfen** lässt er sich jederzeit vollständig aktualisieren. Beim Erstellen führt der Assistant dieselbe Prüfung unabhängig von der Anzeige nochmals verbindlich aus.

Ist genau eine gleichnamige IPSView in der Zielkategorie vorhanden, bietet der Startcheck die ausdrückliche Auswahl **Vorhandene View überschreiben** an. Ohne diese Bestätigung bleibt die Erstellung gesperrt. Beim bestätigten Überschreiben bleibt die Objekt-ID erhalten, aber alle Seiten, Bedienelemente und Einstellungen der bisherigen View werden durch die neu erzeugte View ersetzt. Gleichnamige Nicht-IPSView-Objekte und mehrere gleichnamige Treffer können nicht überschrieben werden.

| Seitenverhältnis | Logische Größe im Querformat |
|---|---:|
| **1:1** | 1000 x 1000 |
| **4:3** | 1024 x 768 |
| **8:5 (16:10)** | 1280 x 800 |
| **9:5 (18:10)** | 1440 x 800 |
| **13:6** | 1300 x 600 |
| **16:9** | 1360 x 765 |
| **2:1** | 1360 x 680 |

Danach im Bereich **Design** die gewünschte Gestaltung auswählen. Mit **View erstellen** wird das neue IPSView-Medienobjekt angelegt.

### 5. Bestehende View gestalten

Die nur im Expertenmodus sichtbare Schaltfläche **Bestehende View** öffnet die Einstellungen, mit denen das Design eines vorhandenen IPSView-Projekts übernommen und auf einer Kopie verändert werden kann.

1. Unter **Quell-IPSView** das vorhandene IPSView-Medienobjekt auswählen.
2. Der Assistant liest das bestehende Design ein und überträgt die erkannten Farben in die Designfelder.
3. Unter **Name der Kopie** den gewünschten Namen festlegen.
4. Unter **Zielkategorie der Kopie** den Speicherort auswählen.
5. Den gewünschten [Gestaltungsumfang](#7-gestaltungsumfang-bei-bestehenden-views) festlegen.
6. Farben, Effekte, Typografie und Formensprache nach Wunsch anpassen.
7. Mit **Designkopie speichern** die Kopie erstellen oder aktualisieren.

Nach dem Laden zeigt der Assistant unter anderem an, wie viele Seiten und Bedienelemente erkannt wurden. Außerdem werden die aktuelle Schriftart, Grundschriftgröße, Eckenrundung und Rahmenbreite angezeigt.

Die Quell-IPSView bleibt bei diesem Vorgang immer unverändert. Wird später erneut dieselbe Quell-View geladen und die Kopie unter demselben Namen in derselben Zielkategorie gespeichert, aktualisiert der Assistant die bereits angelegte Designkopie.

### 6. Design einstellen

Die Designvorlage und die Live-Vorschau bleiben direkt im Formular sichtbar. Die umfangreicheren Einstellungen für Farben, Effekte, Typografie und Formensprache befinden sich im Dialog **Designdetails**; das Hintergrundbild wird über einen eigenen Dialog bearbeitet. Änderungen aus beiden Dialogen aktualisieren die sichtbare Vorschau weiterhin unmittelbar.

#### 6.1 Designvorlagen

Unter **Designvorlage** stehen folgende Ausgangsdesigns zur Verfügung:

- IPSView-Standard
- Hell
- Dunkel
- Warm
- Kühl
- Erdig
- Wasser
- Sonnig
- Benutzerdefiniert

Eine Designvorlage belegt alle Farbrollen mit aufeinander abgestimmten Werten. Sobald eine Farbe manuell verändert wird, wechselt die Auswahl automatisch auf **Benutzerdefiniert**.

#### 6.2 Designfarben

Die Farben sind nach ihrer Aufgabe innerhalb der View gegliedert:

| Farbe | Verwendung |
|---|---|
| **View-Hintergrund** | Äußerster Hintergrund der gesamten View. |
| **Seitenhintergrund** | Hintergrund der einzelnen Seiten. |
| **Flächen und Bedienelemente** | Grundfarbe für Karten, Schaltflächen und andere Bedienflächen. |
| **Haupttext** | Primäre Beschriftungen und wichtige Texte. |
| **Nebentext** | Ergänzende und weniger hervorgehobene Texte. |
| **Rahmen und Trennlinien** | Rahmen, Konturen und optische Abgrenzungen. |
| **Akzent** | Hervorgehobene Elemente und zentrale Farbakzente. |
| **Aktiv** | Aktive oder eingeschaltete Zustände. |
| **Inaktiv** | Inaktive oder ausgeschaltete Zustände. |
| **Erfolg** | Positive Zustände und erfolgreiche Rückmeldungen. |
| **Warnung** | Hinweise, die besondere Aufmerksamkeit benötigen. |
| **Fehler** | Fehlerzustände und kritische Meldungen. |

Bei bestehenden Views werden individuelle Sonderfarben, Assoziationsfarben und besondere Statusfarben möglichst erhalten. Wie weit Farben von Bedienelementen verändert werden, hängt vom gewählten Gestaltungsumfang ab.

#### 6.3 Allgemeine Effekte

##### Schatten

| Auswahl | Wirkung |
|---|---|
| **Bestehende Einstellung beibehalten** | Vorhandene Schattenwerte werden nicht verändert. |
| **Keine Schatten** | Entfernt die globalen Schatten. |
| **Dezent** | Leichter Schatten mit geringer Hervorhebung. |
| **Mittel** | Deutlich sichtbarer Standardschatten. |
| **Stark** | Kräftiger Schatten für eine ausgeprägte Tiefenwirkung. |

##### Transparenz

| Auswahl | Wirkung |
|---|---|
| **Bestehende Einstellung beibehalten** | Vorhandene Transparenzwerte werden nicht verändert. |
| **Deckend** | Flächen werden vollständig deckend dargestellt. |
| **Benutzerdefiniert** | Die Transparenz wird über **Transparenzstärke** festgelegt. |

Bei der benutzerdefinierten Einstellung bedeutet ein niedriger Wert geringe Transparenz. Ein höherer Wert macht die betroffenen Flächen zunehmend durchscheinend.

##### Farbverlauf

| Auswahl | Wirkung |
|---|---|
| **Bestehende Einstellung beibehalten** | Vorhandene Verläufe werden nicht verändert. |
| **Kein Farbverlauf** | Verwendet eine einheitliche Flächenfarbe. |
| **Dezent** | Geringer Helligkeitsunterschied innerhalb der Fläche. |
| **Mittel** | Klar sichtbarer Farbverlauf. |
| **Stark** | Deutlich ausgeprägter Farbverlauf. |

Mit **Verlaufsrichtung** wird festgelegt, ob die Grundfarbe in eine dunklere oder hellere Variante übergeht.

Die Effekte wirken auf globale Flächen und – abhängig vom Gestaltungsumfang – auf passende Hintergründe von Bedienelementen. Texte, Rahmen und besondere Assoziationsfarben werden dadurch nicht verändert.

#### 6.4 Typografie

##### Schriftgrößen

| Auswahl | Grundschriftgröße |
|---|---:|
| **Bestehende Einstellung beibehalten** | Keine Änderung |
| **Kompakt** | 11 px |
| **Standard** | 14 px |
| **Groß** | 18 px |
| **Benutzerdefiniert** | Frei wählbar von 8 bis 32 px |

Weitere globale Schriftgrößen der View werden passend zur gewählten Grundschriftgröße mit angepasst.

##### Schriftarten

Zur Auswahl stehen:

- Roboto
- RobotoMono
- DancingScript
- IndieFlower
- OpenSans
- PTSans
- BebasNeue
- Segment7

Die Vorschau dieser Schriftarten funktioniert offline. Für **Segment7** wird die originale Schrift **G7 Segment 7 S5** verwendet. Bei einer aus einer bestehenden View erkannten zusätzlichen Systemschrift kann die Vorschau ersatzweise eine im Browser vorhandene Schrift anzeigen.

Bei einer bestehenden View ist **Bestehende Einstellung beibehalten** die sicherste Auswahl, wenn deren bisherige Schriftart nicht verändert werden soll.

##### Schriftformatierung

Für vorhandene Bedienelemente können zusätzlich **Schriftstärke**, **Schriftstil** und **Unterstreichung** festgelegt werden:

| Einstellung | Auswahl |
|---|---|
| **Schriftstärke** | Bestehende Einstellung beibehalten, Normal oder Fett |
| **Schriftstil** | Bestehende Einstellung beibehalten, Normal oder Kursiv |
| **Unterstreichung** | Bestehende Einstellung beibehalten, Nicht unterstrichen oder Unterstrichen |

Fett und Kursiv werden nur angeboten, wenn IPSView den passenden echten Schriftschnitt mitliefert:

| Schriftart | Fett | Kursiv | Fett und kursiv | Unterstrichen |
|---|:---:|:---:|:---:|:---:|
| Roboto | ✓ | ✓ | ✓ | ✓ |
| RobotoMono | ✓ | ✓ | ✓ | ✓ |
| OpenSans | ✓ | ✓ | ✓ | ✓ |
| PTSans | ✓ | ✓ | ✓ | ✓ |
| DancingScript | ✓ | – | – | ✓ |
| IndieFlower | – | – | – | ✓ |
| BebasNeue | – | – | – | ✓ |
| Segment7 | – | – | – | ✓ |

Wird eine Schriftart ohne fetten oder kursiven Schnitt gewählt, stellt der Assistant die betreffende Auswahl automatisch auf **Normal**. Die nicht verfügbare Option **Fett** beziehungsweise **Kursiv** bleibt zur Orientierung sichtbar, ist aber nicht auswählbar; **Normal** und die übrigen Optionen bleiben bedienbar. Unterstreichen ist unabhängig von der Schriftart möglich.

Die Schriftformatierung wirkt nur auf bereits vorhandene Bedienelemente einer Designkopie. Welche Bedienelemente angepasst werden, richtet sich nach dem gewählten **Gestaltungsumfang**. Eine neu angelegte leere View enthält noch keine Bedienelemente, auf die Fett, Kursiv oder Unterstrichen angewendet werden könnten.

#### 6.5 Ecken

| Auswahl | Eckenradius |
|---|---:|
| **Bestehende Einstellung beibehalten** | Keine Änderung |
| **Eckig** | 0 px |
| **Leicht gerundet** | 4 px |
| **Gerundet** | 10 px |
| **Stark gerundet** | 18 px |
| **Benutzerdefiniert** | Frei wählbar von 0 bis 40 px |

Die Einstellung wirkt auf die globalen Eckenvorgaben der View. Bei gerundeten Ecken werden auch geeignete Kreis- und Fortschrittsanzeigen entsprechend abgerundet.

#### 6.6 Rahmen

| Auswahl | Rahmenbreite |
|---|---:|
| **Bestehende Einstellung beibehalten** | Keine Änderung |
| **Kein Rahmen** | 0 px |
| **Dünn** | 1 px |
| **Standard** | 1,5 px |
| **Stark** | 3 px |
| **Benutzerdefiniert** | Frei wählbar von 0 bis 8 px |

Die ausgewählte Rahmenfarbe wird im Bereich **Designfarben** über **Rahmen und Trennlinien** festgelegt.

#### 6.7 Vorschau

Die Vorschau zeigt das Zusammenspiel von Farben, Schatten, Transparenz, Farbverlauf, Schrift, Ecken und Rahmen. Sie dient als Orientierung für das Gesamtbild. Einzelne Bedienelemente können im späteren IPSView Designer aufgrund ihrer eigenen Einstellungen abweichend aussehen.

#### 6.8 Hintergrundbild

Für die Hauptseite oder alle Seiten kann ein PNG- oder JPEG-Bild vom aktuell verwendeten Computer ausgewählt werden. Der Assistant bettet den Dateiinhalt direkt in die `.ipsView` ein; ein lokaler Dateipfad wird nicht gespeichert. Unterstützt werden Dateien bis 10 MB.

| Auswahl | Wirkung |
|---|---|
| **Bestehende Einstellung beibehalten** | Verändert das vorhandene Hintergrundbild einer Designkopie nicht. |
| **Hintergrundbild entfernen** | Entfernt Bild und Anordnung von den ausgewählten Seiten. |
| **Bilddatei von diesem Computer** | Bettet die ausgewählte Datei ein und weist sie den ausgewählten Seiten zu. |

Unter **Anwenden auf** lässt sich auswählen, ob die Änderung nur die **Hauptseite** oder **alle Seiten** betrifft. Das gilt sowohl für das Zuweisen eines Bildes und seiner Anordnung als auch für das Entfernen vorhandener Hintergrundbilder. Bei **Nur Hauptseite** bleiben alle anderen Seiten unverändert.

Die Bildanordnung kann auf **Kacheln**, **Zentriert** oder **Strecken** gesetzt werden. Identische bereits eingebettete Bilder werden wiederverwendet und auch bei mehreren Seiten nur einmal gespeichert.

### 7. Gestaltungsumfang bei bestehenden Views

Der Gestaltungsumfang bestimmt, wie weit das neue Design in die vorhandenen Bedienelemente der Kopie eingreift.

| Auswahl | Wirkung |
|---|---|
| **Nur globale Vorgaben (sehr sicher)** | Ändert ausschließlich die zentralen Designvorgaben der View. Individuell eingestellte Bedienelemente bleiben unverändert. |
| **Passende Bedienelementfarben mitgestalten (empfohlen)** | Ändert zusätzlich Farben von Bedienelementen, wenn diese einer bisherigen globalen Designfarbe entsprechen. Individuelle Sonderfarben bleiben erhalten. |
| **Alle Grundfarben der Bedienelemente vereinheitlichen (stark)** | Vereinheitlicht zusätzlich grundlegende Hintergründe, Texte und Rahmen vieler Bedienelemente. Besondere Assoziations- und Statusfarben bleiben geschützt, sofern sie keiner globalen Farbrolle entsprechen. |

Für eine bestehende, bereits sorgfältig gestaltete View empfiehlt sich zunächst die mittlere, empfohlene Stufe. Die starke Stufe eignet sich, wenn ein möglichst einheitliches Erscheinungsbild wichtiger ist als die Beibehaltung individueller Grundfarben.

### 8. Ergebnis im IPSView Designer öffnen

Nach dem Erstellen einer neuen View erscheint im Assistant automatisch der Bereich **Nächste Schritte im IPSView Designer**. Er bleibt auch nach dem erneuten Öffnen der Instanz verfügbar, solange das zuletzt erstellte Medienobjekt noch existiert.

1. Das im Assistant mit Name, Objekt-ID und vollständigem Pfad angezeigte Medienobjekt im Symcon-Objektbaum suchen und doppelt anklicken. Nur der Doppelklick führt die spezielle Medienaktion aus, die den IPSView Designer direkt mit dieser View öffnet.
2. Optional unter **Erstes Symcon-Objekt** eine Variable, ein Skript oder ein Medienobjekt auswählen. Der Assistant zeigt dessen Objekt-ID und schlägt einen geeigneten Typ für das erste Bedienelement vor.
3. Im Repository des IPSView Designers die Objekt-ID in den Filter eingeben oder die ID-Suche verwenden. IPSView zeigt daraufhin die geeigneten normalen und kombinierten Bedienelemente an. Mit Start-Raster eines davon per Drag-and-drop auf die erzeugte Inhaltsseite ziehen; ohne Raster wird die Hauptseite verwendet.
4. Die View im IPSView Designer einmal speichern.

Die Objektauswahl im Assistant dient nur als verständliche Hilfestellung. Sie erzeugt weder ein Bedienelement noch eine automatische Objektverknüpfung. Die offizielle IPSView-Dokumentation beschreibt die [ID-Suche und kombinierten Bedienelemente im Repository](https://docu.brownson.at/viewdesigner/WebHelpMobile/ViewDesignerRepository.html).

Nach dem erstmaligen Speichern einer Designkopie kann deren Medienobjekt wie gewohnt über den Symcon-Objektbaum im IPSView Designer geöffnet werden.

Der IPSView Assistant übernimmt die grundlegende Erstellung und Gestaltung. Seitenaufbau, Positionen, Navigation, Aktionen und Objektzuordnungen werden weiterhin im IPSView Designer bearbeitet.

### 9. Wichtige Hinweise

- Eine bestehende Quell-IPSView wird niemals direkt verändert.
- Die Quell-IPSView darf nicht gleichzeitig als Ziel der Designkopie verwendet werden.
- Bei bestehenden Views sind die Optionen **Bestehende Einstellung beibehalten** für Schatten, Transparenz, Verläufe, Schriftgrößen, Schriftart, Ecken und Rahmen die sicherste Ausgangsbasis.
- Der Assistant verändert keine Seitenstruktur, Positionen, Navigation, Aktionen oder Symcon-Objektzuordnungen.
- Spätere Änderungen, die im IPSView Designer an einer verwalteten Designkopie vorgenommen wurden, bleiben bei erneuter Designaktualisierung erhalten, soweit sie nicht zu den gewählten Designwerten gehören.
