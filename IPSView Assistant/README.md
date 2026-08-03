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
- Seitenverhältnis und Ausrichtung festlegen
- Vorgefertigte Farbdesigns auswählen und individuell anpassen
- Farben, Schatten, Transparenz und Farbverläufe einstellen
- Schriftgröße, Schriftart, Ecken und Rahmen festlegen
- Änderungen unmittelbar in einer Vorschau beurteilen
- Bestehende IPSView laden und als separate Designkopie neu gestalten
- Bereits angelegte Designkopie bei späteren Speichervorgängen aktualisieren

### 2. Voraussetzungen

- Symcon ab Version 9.0
- Installierter und lizenzierter IPSView Designer

### 3. Installation

1. Im Symcon **Modul-Control** die folgende URL hinzufügen:

   ```text
   https://github.com/Burki24/IPSViewAssistant
   ```

2. Anschließend eine Instanz **IPSView Assistant** erstellen.
3. Die Instanzkonfiguration öffnen.

### 4. Neue View erstellen

Im Bereich **View-Einstellungen** werden die grundlegenden Eigenschaften der neuen View festgelegt.

| Einstellung | Bedeutung |
|---|---|
| **View-Name** | Name des neu angelegten IPSView-Medienobjekts im Symcon-Objektbaum. |
| **Zielkategorie** | Kategorie, in der das Medienobjekt angelegt wird. |
| **Name der Hauptseite** | Bezeichnung der ersten Seite innerhalb der neuen View. |
| **Seitenverhältnis** | Auswahl zwischen quadratisch (1:1), klassisch (4:3) und Breitbild (16:9). |
| **Ausrichtung** | Querformat oder Hochformat. |
| **Vorlage** | Grundlage der neuen View. Derzeit steht eine leere View zur Verfügung. |

Danach im Bereich **Design** die gewünschte Gestaltung auswählen. Mit **View erstellen** wird das neue IPSView-Medienobjekt angelegt.

### 5. Bestehende View gestalten

Der Bereich **Bestehende View** dient dazu, das Design eines vorhandenen IPSView-Projekts zu übernehmen und auf einer Kopie zu verändern.

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

Die Vorschau dieser Schriftarten funktioniert offline. Für **Segment7** wird in der Vorschau eine kompatible Sieben-Segment-Schrift verwendet. Bei einer aus einer bestehenden View erkannten zusätzlichen Systemschrift kann die Vorschau ersatzweise eine im Browser vorhandene Schrift anzeigen.

Bei einer bestehenden View ist **Bestehende Einstellung beibehalten** die sicherste Auswahl, wenn deren bisherige Schriftart nicht verändert werden soll.

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

### 7. Gestaltungsumfang bei bestehenden Views

Der Gestaltungsumfang bestimmt, wie weit das neue Design in die vorhandenen Bedienelemente der Kopie eingreift.

| Auswahl | Wirkung |
|---|---|
| **Nur globale Vorgaben (sehr sicher)** | Ändert ausschließlich die zentralen Designvorgaben der View. Individuell eingestellte Bedienelemente bleiben unverändert. |
| **Passende Bedienelementfarben mitgestalten (empfohlen)** | Ändert zusätzlich Farben von Bedienelementen, wenn diese einer bisherigen globalen Designfarbe entsprechen. Individuelle Sonderfarben bleiben erhalten. |
| **Alle Grundfarben der Bedienelemente vereinheitlichen (stark)** | Vereinheitlicht zusätzlich grundlegende Hintergründe, Texte und Rahmen vieler Bedienelemente. Besondere Assoziations- und Statusfarben bleiben geschützt, sofern sie keiner globalen Farbrolle entsprechen. |

Für eine bestehende, bereits sorgfältig gestaltete View empfiehlt sich zunächst die mittlere, empfohlene Stufe. Die starke Stufe eignet sich, wenn ein möglichst einheitliches Erscheinungsbild wichtiger ist als die Beibehaltung individueller Grundfarben.

### 8. Ergebnis im IPSView Designer öffnen

Nach dem Erstellen oder erstmaligen Speichern einer Designkopie:

1. Das neu angelegte Medienobjekt im Symcon-Objektbaum öffnen.
2. Die View im IPSView Designer laden.
3. Die Darstellung kontrollieren und bei Bedarf einzelne Seiten oder Bedienelemente nachbearbeiten.
4. Die View im IPSView Designer einmal speichern.

Der IPSView Assistant übernimmt die grundlegende Erstellung und Gestaltung. Seitenaufbau, Positionen, Navigation, Aktionen und Objektzuordnungen werden weiterhin im IPSView Designer bearbeitet.

### 9. Wichtige Hinweise

- Eine bestehende Quell-IPSView wird niemals direkt verändert.
- Die Quell-IPSView darf nicht gleichzeitig als Ziel der Designkopie verwendet werden.
- Bei bestehenden Views sind die Optionen **Bestehende Einstellung beibehalten** für Schatten, Transparenz, Verläufe, Schriftgrößen, Schriftart, Ecken und Rahmen die sicherste Ausgangsbasis.
- Der Assistant verändert keine Seitenstruktur, Positionen, Navigation, Aktionen oder Symcon-Objektzuordnungen.
- Spätere Änderungen, die im IPSView Designer an einer verwalteten Designkopie vorgenommen wurden, bleiben bei erneuter Designaktualisierung erhalten, soweit sie nicht zu den gewählten Designwerten gehören.
